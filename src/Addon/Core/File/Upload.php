<?php

namespace Drupal\pingvin\Addon\Core\File;

use Drupal;
use Drupal\Core\File\FileSystem;
use Drupal\Core\File\FileSystemInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\pingvin\Session\Session;
use Drupal\user\Entity\User;
use Exception;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Middleware\Middleware;
use Drupal\pingvin\Route\RouteInterface;
use Grpc\Server;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Drupal\pingvin\Asserter\DirectoryPathAsserter;

/**
 * @route
 * id = 'pingvin:api_file_upload'
 * name = 'Pingvin Auth - File Upload'
 * method = 'POST'
 * description = 'File Upload route for Pingvin Auth.'
 * path = 'api/file/upload'
 * permissions = [
 *  'access content'
 * ]
 * roles = []
 * @route-end
 */
class Upload implements RouteInterface {
  /**
   * Handles Logout requests.
   *
   * @param Request $request
   *    The HTTP request object from Symfony.
   * @return ServerJsonResponse
   *    The JSON response containing the logout data.
   * @throws Exception
   *    Only if the middleware specifications are incorrect.
   */
  public function post(Request $request): ServerJsonResponse {
    $request = Middleware::enable($request, ['auth:jwt', 'body:binary', 'client:cors', 'request']);
    if ($request instanceof ServerJsonResponse) return $request;

    /** @var array $context */
    $context = $request->attributes->get('context');
    /** @var User $user */
    $user = $context['user'];
    /** @var array $data */
    $data = $context['data'];
    /** @var array $files */
    $files = $context['files'];

    if (count($files) > 3) {
      return new ServerJsonResponse([
        'message' => 'You have exceeded the limit of maximum attachments.',
      ], 400);
    }

    if (empty($data['published'])) {
      return new ServerJsonResponse([
        'message' => 'Published is not specified.',
      ], 400);
    }

    if (!in_array((int)$data['published'], [0, 1], true)) {
      return new ServerJsonResponse([
        'message' => 'Published is not valid.',
      ], 400);
    }

    if (empty($data['visibility'])) {
      return new ServerJsonResponse([
        'message' => 'Visibility is not specified.'
      ], 400);
    }

    if (!in_array($data['visibility'], ['public', 'private'])) {
      return new ServerJsonResponse([
        'message' => 'Visibility is not specified.'
      ], 400);
    }

    if (empty($data['path'])) {
      return new ServerJsonResponse([
        'message' => 'Path is not specified.'
      ]);
    }

    if (!DirectoryPathAsserter::assert($data['path'])) {
      return new ServerJsonResponse([
        'message' => 'Path does not have the right format.'
      ], 400);
    }

    $directoryUri = $data['visibility'] . '://' . $data['path'];
    if (!is_dir($data['path'])) {
      Drupal::service('file_system')->prepareDirectory($directoryUri, FileSystemInterface::CREATE_DIRECTORY);
    }

    $uploadedFiles = [];
    $directoryPath = Drupal::service('file_system')->realpath($directoryUri);
    /** @var UploadedFile|null $file */
    foreach ($files as $file) {
      /** @var FileRepositoryInterface $fileRepositoryService */
      $fileRepositoryService = Drupal::service('file.repository');

      try {
        $uploadedFile = $fileRepositoryService->writeData($file['content'],
          $directoryUri . '/' . $file['filename'] . '.' . $file['fileExtension'], FileSystemInterface::EXISTS_REPLACE);
        $uploadedFile->setOwnerId($user->id());
        $uploadedFile->setChangedTime(time());
        $uploadedFile->setPermanent();
        $uploadedFile->save();

        $uploadedFiles[] = [
          'id' => $uploadedFile->id(),
          'url' => $uploadedFile->createFileUrl(true),
          'filename' => $file['filename'],
          'fileExtension' => $file['fileExtension'],
        ];
      } catch (Drupal\Core\File\Exception\FileException) {}
    }

    return new ServerJsonResponse([
      'message' => 'Uploading files was attempted.',
      'uploadedFiles' => $uploadedFiles,
    ], 200);
  }
}
