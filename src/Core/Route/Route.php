<?php

namespace Drupal\drift_eleven\Core\Route;

use Drupal\drift_eleven\Core\Asserters\DirectoryPathAsserter;
use Drupal\drift_eleven\Core\Asserters\RouteClassAsserter;
use Drupal\drift_eleven\Core\Asserters\RouteDocCommentAsserter;
use Drupal\drift_eleven\Core\Asserters\RouteExtendsAsserter;
use Drupal\drift_eleven\Core\Asserters\RouteImplementsAsserter;
use Drupal\drift_eleven\Core\Asserters\RouteMethodAsserter;
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
        RouteClassAsserter::class,        // checks class declaration
        RouteMethodAsserter::class,       // checks for required methods
        RouteDocCommentAsserter::class,   // checks for doc comments
        RouteExtendsAsserter::class,      // checks if class extends a base class
        RouteImplementsAsserter::class    // checks if class implements an interface
      ];

      foreach ($asserters as $asserter) {
        if (!call_user_func([$asserter, 'assert'], $this)) {
          throw new ParseError("$asserter requirements not met");
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
    $attributes = $this->getFileAttributes();

    if (empty($attributes['name'])) {
      throw new InvalidArgumentException('Route name is missing in file attributes.');
    }

    return new \Symfony\Component\Routing\Route(
      path: $this->path,
      defaults: [
        '_title' => $this->name,
        '_controller' => $attributes['name'] . '::' . 'handle',
        '_format' => 'json',
      ],
      requirements: [
        '_permission' => implode(', ', $this->permissions) ?: '',
      ],
      options: [
        'drift_eleven:route:id' => $this->id,
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
      'parentClass' => FileAttributeRetriever::retrieve($this->filePath, 'parentClass'),
    ];
  }

  public function getId(): string {
    return $this->id;
  }

  public function setEnabled(bool $enabled): void {
    $this->enabled = $enabled;
  }

  public function getFilePath(): string {
    return $this->filePath;
  }

  public function setFilePath(string $filePath): void {
    $this->filePath = $filePath;
  }
}
