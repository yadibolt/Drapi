<?php

namespace Drupal\pingvin\File;

use Exception;
use ReflectionClass;
use ReflectionMethod;

/**
 * Used mainly to determine the attributes of a PHP file.
 * Since we use registry to register the routes, we need to check required
 * attributes in order to securely create the routes.
 *
 * @see \Drupal\pingvin\Registry\RouteRegistry
 */
class Retriever {
  /**
   * The allowed file attributes for retrieval.
   * @var array
   */
  public const array ALLOWED_FILE_ATTRIBUTES = [
    'namespace', // namespace without the class name
    'namespaceName', // full namespace including the class name
    'docComment', // doc comment of the class
    'name', // full class name including the namespace
    'shortName', // short class name without the namespace (so basically: class name)
    'interfaces', // interface name that the class implements
    'publicMethods', // methods of the class
  ];

  /**
   * The file path to retrieve.
   * @var string
   */
  protected string $filePath;

  /**
   * Constructs a new Retriever instance from a file path.
   * The file has to be a PHP file, otherwise an exception will be thrown.
   *
   * @param string $filePath
   *    The path to the PHP file to retrieve.
   * @throws Exception
   *    If the file is not a PHP file or does not exist.
   */
  public function __construct(string $filePath) {
    $this->filePath = $filePath;

    $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);
    if ($extension !== 'php') {
      throw new Exception("File at {$this->filePath} is not a PHP file.");
    }
  }

  /**
   * Retrieves the specified attribute from the file.
   * This method can be used only for PHP files.
   *
   * @param string $retrievalAttribute
   *   The attribute to retrieve from the file.
   *
   * @return string
   *   The value of the retrieved attribute.
   *
   * @throws Exception
   *   If the file does not exist or the retrieval attribute is not allowed.
   */
  public function retrieve(string $retrievalAttribute): string|array|null {
    if (!in_array($retrievalAttribute, self::ALLOWED_FILE_ATTRIBUTES)) {
      throw new Exception("Retrieval attribute '{$retrievalAttribute}' is not allowed.");
    }

    return match ($retrievalAttribute) {
      'namespace' => $this->retrieveClassNamespace(),
      'namespaceName' => $this->retrieveClassNamespaceName(),
      'docComment' => $this->retrieveClassDocComment(),
      'name' => $this->retrieveClassName(),
      'shortName' => $this->retrieveClassShortName(),
      'interfaces' => $this->retrieveInterfaceName(),
      'publicMethods' => $this->retrieveDefinedPublicMethods(),
      default => throw new Exception("Retrieval attribute '{$retrievalAttribute}' is not implemented."),
    };
  }

  /**
   * Creates a ReflectionClass instance for the class defined in the PHP file.
   *
   * @return ReflectionClass
   *    Returns a ReflectionClass instance for the class defined in the PHP file.
   * @throws Exception
   *    If the class does not exist or cannot be reflected.
   */
  protected function createReflection(): ReflectionClass {
    $namespace = $this->retrieveClassNamespace();

    // we assume that the filename matches the class name
    $fileName = pathinfo($this->filePath, PATHINFO_FILENAME);
    $qualifiedClassName = $namespace . '\\' . $fileName;

    // we include the file, try to create a ReflectionClass instance
    // of the class and check if the class exists, just to be sure
    include_once $this->filePath;

    if (!class_exists($qualifiedClassName, false)) {
      throw new Exception("Class {$qualifiedClassName} does not exist.");
    }

    return new ReflectionClass($qualifiedClassName);
  }

  /**
   * Retrieves the namespace from the PHP file.
   *
   * @return string|null
   *    Returns the namespace if found, otherwise returns null.
   * @throws Exception
   *    If the file cannot be read or the namespace cannot be found.
   */
  protected function retrieveClassNamespace(): ?string {
    $content = file_get_contents($this->filePath);
    if ($content === false) {
      throw new Exception("Could not read file at {$this->filePath}.");
    }

    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
      return trim($matches[1]);
    }

    throw new Exception("Namespace not found in file at {$this->filePath}.");
  }

  /**
   * Retrieves the namespace name from the PHP file.
   *
   * @return string|null
   *    Returns the namespace if found, otherwise returns null.
   * @throws Exception
   *    If the file cannot be read or the namespace cannot be found.
   */
  protected function retrieveClassNamespaceName(): ?string {
    $reflection = $this->createReflection();
    return $reflection->getNamespaceName() ?: null;
  }

  /**
   * Retrieves the class doc comment from the PHP file.
   *
   * @return string|null
   *    Retrieves the class doc comment from the PHP file.
   *    Returns null if the namespace does not exist or the class does not have a doc comment.
   * @throws Exception
   *    If the class does not exist or the namespace cannot be retrieved.
   */
  protected function retrieveClassDocComment(): ?string {
    $reflection = $this->createReflection();
    return $reflection->getDocComment() ?: null;
  }

  /**
   * Retrieves the full class name including the namespace.
   *
   * @return string|null
   *    Returns the full class name including the namespace.
   *    If the class does not exist, returns null.
   * @throws Exception
   *    If the class does not exist or cannot be reflected.
   */
  protected function retrieveClassName(): ?string {
    $reflection = $this->createReflection();
    return $reflection->getName() ?: null;
  }

  /**
   * Retrieves the short class name without the namespace.
   *
   * @return string|null
   *    Retrieves the short class name without the namespace.
   *    If the class does not exist, returns null.
   * @throws Exception
   *    If the class does not exist or cannot be reflected.
   */
  protected function retrieveClassShortName(): ?string {
    $reflection = $this->createReflection();
    return $reflection->getShortName() ?: null;
  }

  /**
   * Retrieves the interface names that the class implements.
   *
   * @return array|null
   *    Returns an array of interface names that the class implements.
   *    If the class does not implement any interfaces, returns null.
   * @throws Exception
   *    If the class does not exist or cannot be reflected.
   */
  protected function retrieveInterfaceName(): ?array {
    $reflection = $this->createReflection();
    return $reflection->getInterfaceNames() ?: null;
  }

  /**
   * Retrieves the public methods defined in the class.
   *
   * @return array|null
   *    Returns an array of public method names defined in the class.
   *    If the class does not have any public methods, returns null.
   * @throws Exception
   *    If the class does not exist or cannot be reflected.
   */
  protected function retrieveDefinedPublicMethods(): ?array {
    $reflection = $this->createReflection();
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC) ?: null;

    if ($methods === null) return null;

    return array_map(function ($method) {
      return $method->getName();
    }, $methods);
  }
}
