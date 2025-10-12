<?php

namespace Drupal\drift_eleven\Core\Utility\Trait;

trait SanitizerTrait {
  protected function sanitizeURL(string $input): ?string {
    return filter_var($input, FILTER_SANITIZE_URL);
  }
}
