<?php

namespace Drupal\pingvin\Route;

use Drupal;
use Drupal\pingvin\File\Retriever;
use Exception;

class Route {
  /**
   * The allowed route methods for the route.
   * These methods are used to define the HTTP methods that the routes supports.
   *
   * @var array
   */
  public const array ALLOWED_ROUTE_METHODS = [
    'GET',
    'POST',
    'PUT',
    'PATCH',
    'DELETE',
  ];

  /**
   * The path to the route file.
   *
   * @var string
   */
  protected string $routeFilePath;
  /**
   * The unique identifier for the route.
   *
   * @var string
   */
  private string $id;
  /**
   * The name of the route.
   *
   * @var string
   */
  protected string $name;
  /**
   * The HTTP method for the route.
   *
   * @var string
   */
  protected string $method;
  /**
   * The description of the route.
   *
   * @var string
   */
  protected ?string $description;
  /**
   * The path for the route.
   *
   * @var string
   */
  protected string $path;
  /**
   * The permission required to access the route.
   *
   * @var array
   */
  protected ?array $permission;
  /**
   * The host restrictions for the route.
   *
   * @var string
   */
  protected ?string $restrictHost;
  /**
   * Whether the route is enabled.
   *
   * @var bool
   */
  private ?bool $enabled;

  /**
   * Constructs a new Route object.
   *
   * @param string $routeFilePath
   *    The path to the route file.
   * @param array $routeContent
   *    The content of the route file as an associative array.
   */
  public function __construct(string $routeFilePath, array $routeContent) {
    $this->routeFilePath = $routeFilePath;
    $this->id = $routeContent['id'];
    $this->name = $routeContent['name'];
    $this->method = $routeContent['method'];
    $this->description = $routeContent['description'];
    $this->path = $routeContent['path'];
    $this->permission = $routeContent['permission'];
    $this->restrictHost = $routeContent['restrict_host'];
    $this->enabled = $routeContent['enabled'] ?: true;
  }

  /**
   * Returns the route information as an associative array.
   *
   * @return array
   *    An associative array containing the route information.
   */
  public function getAssociativeRoute(): array {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'method' => $this->method,
      'description' => $this->description,
      'path' => $this->path,
      'permission' => $this->permission,
      'restrict_host' => $this->restrictHost,
      'enabled' => $this->enabled,
    ];
  }

  /**
   * Returns a Symfony route object based on the route information.
   *
   * @return \Symfony\Component\Routing\Route
   *    The Symfony route object.
   * @throws Exception
   *    If the route class cannot be retrieved or if the method is not defined.
   */
  public function getSymfonyRoute(): \Symfony\Component\Routing\Route {
    $config = Drupal::config(pw8dr1_PROJECT_ID);
    $retriever = new Retriever($this->routeFilePath);

    $isProduction = $config->get('config.production_environment') ?: false;
    $schemes = [
      'https',
    ];
    if (!$isProduction) {
      $schemes[] = 'http';
    }

    $className = $retriever->retrieve('name');
    $methodName = strtolower($this->method);

    return new \Symfony\Component\Routing\Route(
      path: $this->path,
      defaults: [
        '_title' => $this->name,
        '_controller' => $className . '::' . $methodName,
        '_format' => 'json',
      ],
      requirements: [
        '_permission' => implode(', ', $this->permission) ?: '',
      ],
      options: [
        pw8dr1_PROJECT_ID.':routeId' => $this->id,
      ],
      host: $this->restrictHost ?: '',
      schemes: $schemes,
      methods: [$this->method],
    );
  }

  public function getId(): string {
    return $this->id;
  }

  public function setEnabled(bool $enabled): void {
    $this->enabled = $enabled;
  }
}
