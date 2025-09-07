<?php

namespace Drupal\drift_eleven\Core\File;

use Exception;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

class FileAttributeRetriever implements RetrieverInterface {
  use FileTrait;

  /**
   * The allowed file attributes for retrievalforce-commit
   * @var array
   */
  protected const array ALLOWED_FILE_ATTRIBUTES = [
    'namespace',      // namespace without the class name
    'namespaceName',  // full namespace including the class name
    'docComment',     // doc comment of the class
    'name',           // full class name including the namespace
    'shortName',      // short class name without the namespace (so basically: class name)
    'interfaces',     // interface name that the class implements
    'publicMethods',  // methods of the class
  ];

  /**
   * @throws Exception
   */
  public static function retrieve(string $filePath, string $attribute): string|array|null {
    if (!self::fileExists($filePath) || !self::isReadable($filePath) || !self::isValidPHPFile($filePath)) {
      throw new InvalidArgumentException("File $filePath does not exist or is not readable or is not a valid PHP file.");
    }

    if (!in_array($attribute, self::ALLOWED_FILE_ATTRIBUTES)) {
      throw new InvalidArgumentException("Retrieval attribute $attribute is not allowed.");
    }

    return match ($attribute) {
      'namespace' => self::retrieveClassNamespace($filePath),
      'namespaceName' => self::retrieveClassNamespaceName($filePath),
      'docComment' => self::retrieveClassDocComment($filePath),
      'name' => self::retrieveClassName($filePath),
      'shortName' => self::retrieveClassShortName($filePath),
      'interfaces' => self::retrieveInterfaceName($filePath),
      'publicMethods' => self::retrieveDefinedPublicMethods($filePath),
      default => throw new InvalidArgumentException("Retrieval attribute '$attribute' is not implemented."),
    };
  }

  protected static function retrieveClassNamespace(string $filePath): ?string {
    $content = file_get_contents($filePath);
    if ($content === false) {
      throw new RuntimeException("Could not read file at $filePath.");
    }

    if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
      return trim($matches[1]);
    }

    throw new RuntimeException("Namespace not found in file at $filePath.");
  }

  /**
   * @throws ReflectionException
   * @throws Exception
   */
  protected static function createReflection(string $filePath): ReflectionClass {
    $namespace = self::retrieveClassNamespace($filePath);

    // we assume that the filename matches the class name
    $fileName = pathinfo($filePath, PATHINFO_FILENAME);
    $qualifiedClassName = $namespace . '\\' . $fileName;

    // we include the file, try to create a ReflectionClass instance
    // of the class and check if the class exists, just to be sure
    self::includeFile($filePath);

    if (!class_exists($qualifiedClassName, false)) {
      throw new Exception("Class $qualifiedClassName does not exist.");
    }

    return new ReflectionClass($qualifiedClassName);
  }

  /**
   * @throws Exception
   */
  protected static function retrieveClassNamespaceName(string $filePath): ?string {
    $reflection = self::createReflection($filePath);
    return $reflection->getNamespaceName() ?: null;
  }

  /**
   * @throws Exception
   */
  protected static function retrieveClassDocComment(string $filePath): ?string {
    $reflection = self::createReflection($filePath);
    return $reflection->getDocComment() ?: null;
  }

  /**
   * @throws Exception
   */
  protected static function retrieveClassName(string $filePath): ?string {
    $reflection = self::createReflection($filePath);
    return $reflection->getName() ?: null;
  }

  /**
   * @throws Exception
   */
  protected static function retrieveClassShortName(string $filePath): ?string {
    $reflection = self::createReflection($filePath);
    return $reflection->getShortName() ?: null;
  }

  /**
   * @throws Exception
   */
  protected static function retrieveInterfaceName(string $filePath): ?array {
    $reflection = self::createReflection($filePath);
    return $reflection->getInterfaceNames() ?: null;
  }

  /**
   * @throws Exception
   */
  protected static function retrieveDefinedPublicMethods(string $filePath): ?array {
    $reflection = self::createReflection($filePath);
    $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC) ?: null;

    if ($methods === null) return null;

    return array_map(function ($method) {
      return $method->getName();
    }, $methods);
  }
}
