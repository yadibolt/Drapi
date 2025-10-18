<?php

namespace Drupal\drift_eleven\Routes;

use Drupal;
use Drupal\Core\File\FileSystemInterface;
use Drupal\drift_eleven\Core\Http\Mail\MailClient;use Drupal\drift_eleven\Core\Http\Reply;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandler;
use Drupal\drift_eleven\Core\Http\Route\Base\RouteHandlerBase;
use Drupal\file\FileRepositoryInterface;use Symfony\Component\HttpFoundation\File\UploadedFile;

#[RouteHandler(
  id: 'drift_eleven:example',
  name: 'Drift Eleven Example Route',
  method: 'POST',
  path: 'api/example',
  description: 'An Example Drift Eleven Route',
  permissions: ['access content'],
  roles: [],
  useMiddleware: ['request'],
  useCache: false
)]
class ExampleRoute extends RouteHandlerBase {
  public function handle(): Reply {

    $data = $this->getRequestData();
    $files = $this->getFiles();

    /** @var FileRepositoryInterface $fileRepositoryService */
    $fileRepositoryService = Drupal::service('file.repository');
    $targetDir = 'public://uploads';

    Drupal::service('file_system')
      ->prepareDirectory($targetDir, FileSystemInterface::CREATE_DIRECTORY);

    $mailClient = MailClient::make(
      moduleName: 'drift_eleven',
      from: 'admin@drifteleven.com',
      to: 'test@drifteleven.com',
      subject: 'Test Email with Attachments',
      themeKey: 'user_registration_mail',
      themeParams: ['message' => 'This is a test email with attachments.'],
    );

    /** @var UploadedFile $file */
    foreach ($files as $file) {
      if ($file->isValid()) {
        $filename = $file->getClientOriginalName();
        $destination = $targetDir . '/' . $filename;

        $uploadedFile = $fileRepositoryService->writeData(
          file_get_contents($file->getRealPath()),
          $destination, FileSystemInterface::EXISTS_REPLACE);
        $uploadedFile->setOwnerId(1);
        $uploadedFile->setChangedTime(time());
        $uploadedFile->setPermanent();
        $uploadedFile->save();

        $mailClient->addAttachment($filename, $uploadedFile->getFileUri(), $uploadedFile->getMimeType());

        if (file_exists($file->getRealPath())) {
          unlink($file->getRealPath());
        }
      }
    }

    $mailClient->sendMail();

    // send test mail with attachment

    return Reply::make([
      'message' => 'No language set',
    ], 200);
  }
}
