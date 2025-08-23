<?php

namespace Drupal\pingvin\Parser;

use Drupal\pingvin\Route\Route;
use Exception;

class RouteDocCommentParser {
  /**
   * The tags that are expected in the route doc comment.
   *
   * @var array
   */
  protected const array ROUTE_TAGS = [
    'id',
    'name',
    'method',
    'description',
    'path',
    'permissions',
    'roles',
    'restrict_host',
    'enabled',
  ];

  /**
   * The full content of the doc comment.
   *
   * @var string
   */
  protected string $content;

  /**
   * Creates a new instance from parsable string route content.
   *
   * @param string $content
   *    The content of the doc comment to parse.
   */
  public function __construct(string $content) {
    $this->content = trim($content);
  }

  /**
   * Parses the doc comment content to extract route information.
   *
   * @return array
   *    Returns true if the parsing is successful.
   *
   * @throws Exception
   *    if the Doc Comment is not parsable and contains errors.
   */
  public function parse(bool $fill = false): array {
    if (!str_starts_with($this->content, '/**') || !str_ends_with($this->content, '*/')) {
      throw new Exception('Doc comment must start with /** and end with */');
    }

    if (!preg_match('/@route\s+/', $this->content)) {
      throw new Exception('Doc comment must contain a @route tag.');
    }

    if (!preg_match('/@route-end\s+/', $this->content)) {
      throw new Exception('Doc comment must contain a @route-end tag.');
    }

    $routeContentStart = strpos($this->content, '@route');
    $routeContentEnd = strpos($this->content, '@route-end', $routeContentStart);

    if ($routeContentStart > $routeContentEnd) {
      throw new Exception('Invalid route definition in doc comment.');
    }

    $routeContent = substr($this->content, $routeContentStart, $routeContentEnd - $routeContentStart);
    $routeContent = str_replace('=', ':', $routeContent);
    $routeContent = str_replace(['@route', '@route-end', '*'], '', $routeContent);
    $routeContent = str_replace("'", '"', $routeContent);
    $routeContent = trim($routeContent);

    $iteration = 0;
    foreach (self::ROUTE_TAGS as $tag) {
      if ($iteration === 0) {
        $routeContent = str_replace($tag, '"'.$tag.'"', $routeContent);
        $iteration++;
        continue;
      }

      $routeContent = str_replace($tag, ',"'.$tag.'"', $routeContent);
    }

    $routeContent = json_decode('{' . $routeContent . '}', true);
    if ($routeContent === null) {
      throw new Exception('The route definition is not valid. Please check the syntax.');
    }

    if ($fill) {
      $routeContent = $this->fillMissingKeys($routeContent);
    }

    return $routeContent;
  }

  /**
   * Fills missing keys in the route content with default values.
   *
   * @param array $routeContent
   *    The route content array to fill.
   * @return array
   *    The filled route content array.
   */
  protected function fillMissingKeys(array $routeContent): array {
    foreach (self::ROUTE_TAGS as $tag) {
      if (!array_key_exists($tag, $routeContent)) {
        if ($tag === 'enabled') {
          $routeContent[$tag] = true;
        } else {
          $routeContent[$tag] = null;
        }
      }
    }

    return $routeContent;
  }
}
