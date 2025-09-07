<?php

namespace Drupal\pingvin\Middleware\Request;

use Drupal\pingvin\Generator\FileGenerator;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Normalizer\FileNormalizer;
use Drupal\pingvin\Sanitizer\InputSanitizer;
use Exception;
use Grpc\Server;
use Random\RandomException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class BinaryBodyMiddleware {
  /**
   * Maximum allowed size for the request body in bytes.
   *
   * @var int
   */
  protected const int MAX_BODY_SIZE = 1048576 * 100; // todo: config? 100 MB
  /**
   * All allowed file extensions that server can process.
   *
   * @var array
   */
  protected const array ALLOWED_FILE_EXTENSIONS = [
    'pdf'
  ];

  /**
   * The current request object.
   *
   * @var Request
   */
  protected Request $request;
  /**
   * The route definition array.
   *
   * This array contains the route information.
   * @see \Drupal\pingvin\Parser\RouteDocCommentParser
   * @var array
   */
  protected array $routeDefinition;
  /**
   * Request body data.
   *
   * @var string|resource|false|null
   */
  protected mixed $data;
  /**
   * Request body data.
   *
   * @var string|resource|false|null
   */
  protected mixed $files;

  /**
   * Constructs the AuthMiddleware.
   *
   * @param Request $request
   *    The current request object.
   * @param array $routeDefinition
   *    The route definition array.
   */
  public function __construct(Request $request, array $routeDefinition = []) {
    $this->request = $request;
    $this->routeDefinition = $routeDefinition;
  }

  /**
   * Applies the middleware to the request.
   * Middleware should be called at the very beginning of the request lifecycle.
   *
   * @return array|ServerJsonResponse
   *    Returns the attributes or a JSON response in case of an error.
   * @throws Exception
   */
  public function apply(): array|ServerJsonResponse {
    $validFiles = [];
    $this->data = $this->request->request->all() ?: [];
    $this->files = $this->request->files->all();

    /** @var UploadedFile|null $file */
    foreach ($this->files as $file) {
      $fileExtension = (string)$file->getClientOriginalExtension();

      if (!in_array($fileExtension, self::ALLOWED_FILE_EXTENSIONS)) {
        return new ServerJsonResponse([
          'message' => 'File type is not allowed.',
        ], 400);
      }

      if ($file->getSize() > self::MAX_BODY_SIZE) {
        return new ServerJsonResponse([
          'message' => 'File size exceeds the maximum allowed size of ' . self::MAX_BODY_SIZE . ' bytes.',
        ], 413);
      }

      $validFiles[] = match($fileExtension) {
        'pdf' => $this->handlePdf($file),
        default => null,
      };
    }

    if (!empty($this->data)) {
      $inputSanitizer = new InputSanitizer($this->data);
      $this->data = $inputSanitizer->sanitize('xss');
      $this->data = $inputSanitizer->sanitize('sql');
    }

    return [
      'files' => array_filter($validFiles, function ($file) { return $file !== null; }),
      'data' => $this->data,
    ];
  }

  /**
   * @throws RandomException
   */
  protected function handlePdf(UploadedFile $file): array {
    $originalFilename = $file->getClientOriginalName();
    $originalFileContent = $file->getContent();
    $fileSize = $file->getSize();

    $filename = FileGenerator::generateFilename((string)$file->getClientOriginalName(), (string)$file->getClientOriginalExtension());

    return [
      'filename' => $filename,
      'fileExtension' => FileNormalizer::normalizeFileExtension((string)$file->getClientOriginalExtension()),
      'content' => $originalFileContent,
      'fileSize' => $fileSize,
    ];
  }
}
