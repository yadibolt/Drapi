<?php

namespace Drupal\pingvin\Middleware\Request;

use Drupal\cordr\Route\Content\Validation;
use Drupal\pingvin\Http\ServerJsonResponse;
use Drupal\pingvin\Sanitizer\InputSanitizer;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class JsonBodyMiddleware {
  /**
   * Maximum allowed size for the request body in bytes.
   *
   * @var int
   */
  protected const int MAX_BODY_SIZE = 1048576;
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
    $this->data = $this->request->getContent();

    if ($this->request->server->get('REQUEST_METHOD') == 'GET' && !empty($this->data)) {
      return new ServerJsonResponse([
        'message' => 'GET requests cannot have a body.',
        'actionId' => 'request:body_not_empty',
      ], 400);
    }

    if (in_array($this->request->server->get('REQUEST_METHOD'), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
      if (empty($this->data)) {
        return new ServerJsonResponse([
          'message' => 'Request body cannot be empty.',
          'actionId' => 'request:body_empty',
        ], 400);
      }

      // if the request contains JSON type
      // we try to parse it
      if ($this->request->server->get('CONTENT_TYPE') !== null && str_contains($this->request->server->get('CONTENT_TYPE'), 'application/json')) {
        $contents = json_decode($this->data, true) ?: [];

        if (json_last_error() !== JSON_ERROR_NONE) {
          return new ServerJsonResponse([
            'message' => 'Invalid JSON.',
            'actionId' => 'body:invalid',
          ], 400);
        } else {
          $this->data = $contents;
        }

        if (strlen(json_encode($this->data)) > self::MAX_BODY_SIZE) {
          return new ServerJsonResponse([
            'message' => 'Request body too large.',
            'actionId' => 'body:too_large',
          ], 400);
        }

        $inputSanitizer = new InputSanitizer($this->data);
        $this->data = $inputSanitizer->sanitize('xss');
        $this->data = $inputSanitizer->sanitize('sql');
      }
    }

    return [
      'data' => $this->data ?: [],
    ];
  }
}
