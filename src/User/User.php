<?php

namespace Drupal\pingvin\User;

use Drupal;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Exception;

class User {
  protected const array ALLOWED_LOAD_METHODS = ['mail', 'name'];
  /**
   * Retrieve a user entity by their login, which can be either username or email.
   *
   * @param string $login
   *    The username or email of the user to retrieve.
   * @param string $loadPropertyMethod
   *    The method to use for loading the user. Allowed values are 'mail' or 'name'.
   * @throws InvalidPluginDefinitionException
   * @throws PluginNotFoundException
   * @throws Exception
   *    If an invalid load method is provided.
   * @return Drupal\user\Entity\User|null
   *   The user entity if found, or null if no user matches the provided login.
   */
  public static function retrieveByPropertyMethod(string $login, string $loadPropertyMethod = 'mail'): ?Drupal\user\Entity\User {
    if (!in_array($loadPropertyMethod, self::ALLOWED_LOAD_METHODS)) {
      throw new Exception('Invalid load method. Allowed methods are: ' . implode(', ', self::ALLOWED_LOAD_METHODS));
    }

    $users = Drupal::entityTypeManager()
      ->getStorage('user');

    if ($loadPropertyMethod == 'mail') {
      $users = $users->loadByProperties(['mail' => $login]);
    }
    if ($loadPropertyMethod == 'name') {
      $users = $users->loadByProperties(['name' => $login]);
    }

    if (!$users) return null;

    return reset($users);
  }
}
