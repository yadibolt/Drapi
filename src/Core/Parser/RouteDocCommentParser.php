<?php

namespace Drupal\drift_eleven\Core\Parser;

use Drupal\drift_eleven\Core\Route\RouteInterface;
use ParseError;

class RouteDocCommentParser implements FileParserInterface {
  public static function parse(string $content, bool $fillMissing = false): ?array {
    if (!str_starts_with($content, '/**') || !str_ends_with($content, '*/')) {
      throw new ParseError('Doc comment must start with /** and end with */');
    }

    if (!preg_match('/@route\s+/', $content)) {
      throw new ParseError('Doc comment must contain a @route tag.');
    }

    if (!preg_match('/@route-end\s+/', $content)) {
      throw new ParseError('Doc comment must contain a @route-end tag.');
    }

    $docCommentStart = strpos($content, '@route');
    $docCommentEnd = strpos($content, '@route-end', $docCommentStart);
    if ($docCommentStart > $docCommentEnd) {
      throw new ParseError('The @route-end tag must come after the @route tag.');
    }

    $extractedDef = substr($content, $docCommentStart, $docCommentEnd - $docCommentStart);
    $extractedDef = str_replace('=', ':', $extractedDef);
    $extractedDef = str_replace(['@route', '@route-end', '*'], '', $extractedDef);
    $extractedDef = str_replace("'", '"', $extractedDef);
    $extractedDef = trim($extractedDef);

    $tag_count = 0;
    foreach (RouteInterface::ALLOWED_ROUTE_TAGS as $tag) {
      $pattern = '/\b' . preg_quote($tag, '/') . '\b/';
      if (!preg_match($pattern, $extractedDef)) continue;

      if ($tag_count === 0) {
        $extractedDef = preg_replace($pattern, '"' . $tag . '"', $extractedDef, 1);
      } else {
        $extractedDef = preg_replace($pattern, ',"' . $tag . '"', $extractedDef, 1);
      }

      $tag_count++;
    }

    $extractedDef = json_decode('{' . $extractedDef . '}', true);
    if (empty($extractedDef)) {
      throw new ParseError('Failed to parse route definition from doc comment.');
    }

    if ($fillMissing) $extractedDef = self::fillMissingTags($extractedDef);

    return $extractedDef ?: null;
  }

  protected static function fillMissingTags(array $definition): array {
    foreach (RouteInterface::ALLOWED_ROUTE_TAGS as $tag) {
      if (!array_key_exists($tag, $definition)) {
        if ($tag === 'enabled') {
          $definition[$tag] = true;
        } else {
          $definition[$tag] = null;
        }
      }
    }
    return $definition;
  }
}
