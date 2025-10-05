<?php

namespace Drupal\drift_eleven\Core\Field;

class FieldFoundation {
  protected function getBaseFieldNames(): array {
    return [
      'nid',
      'vid',
      'type',
      'langcode',
      'status',
      'promote',
      'sticky',
      'created',
      'changed',
      'path',
    ];
  }

  protected function getProtectedFieldNames(): array {
    return [
      'uid',
      'uuid',
      'comment',
      'revision_id',
      'revision_user',
      'content_translation_created',
      'reusable',
      'revision_default',
      'content_translation_source',
      'content_translation_outdated',
      'content_translation_uid',
      'revision_log',
      'revision_uid',
      'preffered_admin_langcode',
      'preferred_langcode',
      'revision_created',
      'default_langcode',
      'revision_translation_affected',
    ];
  }
}
