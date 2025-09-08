<?php

namespace Drupal\drift_eleven\Core\Route;

use Drupal\drift_eleven\Core\File\FileAttributeRetriever;
use Exception;
use InvalidArgumentException;
use ParseError;

class Route implements RouteInterface {
  protected string $id;
  protected string $name;
  protected string $method;
  protected string $description;
  protected string $path;
  protected array $permissions;
  protected array $roles;
  protected array $useMiddleware;
  protected bool $useCache;
  protected bool $enabled;
  protected string $filePath;

  public function __construct(string $id, string $name, string $method, string $description, string $path, array $permissions, array $roles, array $useMiddleware, bool $useCache, string $filePath = '') {
    $this->id = $id;
    $this->name = $name;
    $this->method = $method;
    $this->description = $description;
    $this->path = $path;
    $this->permissions = $permissions;
    $this->roles = $roles;
    $this->useMiddleware = $useMiddleware;
    $this->useCache = $useCache;
    $this->enabled = true;
    $this->filePath = $filePath;
  }

  public function applyAssertions(): bool {
      $asserters = [
          // todo: add asserters here
      ];

      foreach ($asserters as $asserter) {
          try {
              call_user_func([$asserter, 'assert'], $this);
          } catch (Exception $e) {
              throw new ParseError("{$asserter} requirements not met. {$e->getMessage()}");
          }
      }

      return true;
  }

  public function toArray(): array {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'method' => $this->method,
      'description' => $this->description,
      'path' => $this->path,
      'permissions' => $this->permissions,
      'roles' => $this->roles,
      'useMiddleware' => $this->useMiddleware,
      'useCache' => $this->useCache,
      'enabled' => $this->enabled,
      'attributes' => $this->getFileAttributes(),
    ];
  }

  public function toSymfony(): \Symfony\Component\Routing\Route {
    $fileAttributes = $this->getFileAttributes();

    if (empty($fileAttributes['attributes']['name'])) {
      throw new InvalidArgumentException('Route name is missing in file attributes.');
    }

    return new \Symfony\Component\Routing\Route(
      path: $this->path,
      defaults: [
        '_title' => $this->name,
        '_controller' => $fileAttributes['attributes']['name'] . '::' . strtolower($this->method),
        '_format' => 'json',
      ],
      requirements: [
        '_permission' => implode(', ', $this->permissions) ?: '',
      ],
      options: [
        'drift_eleven.route:id' => $this->id,
        'no_cache' => TRUE,
      ],
      schemes: self::ALLOWED_SCHEMES,
      methods: [$this->method],
    );
  }

  public function getFileAttributes(): ?array {
    if (empty($this->filePath)) return null;

    return [
      'namespace' => FileAttributeRetriever::retrieve($this->filePath, 'namespace'),
      'namespaceName' => FileAttributeRetriever::retrieve($this->filePath, 'namespaceName'),
      'docComment' => FileAttributeRetriever::retrieve($this->filePath, 'docComment'),
      'name' => FileAttributeRetriever::retrieve($this->filePath, 'name'),
      'shortName' => FileAttributeRetriever::retrieve($this->filePath, 'shortName'),
      'interfaces' => FileAttributeRetriever::retrieve($this->filePath, 'interfaces'),
      'publicMethods' => FileAttributeRetriever::retrieve($this->filePath, 'publicMethods'),
    ];
  }

  public function getId(): string {
    return $this->id;
  }

  public function setEnabled(bool $enabled): void {
    $this->enabled = $enabled;
  }

  public function setFilePath(string $filePath): void {
    $this->filePath = $filePath;
  }
}
