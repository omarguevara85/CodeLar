<?php

declare(strict_types=1);

namespace Drupal\codelar_base;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * @todo Add class description.
 */
final class UserCustom {

  /**
   * Constructs an UserCustom object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * @todo Add method description.
   */
  public function loadUserByUsername($username): ?UserInterface {
    $users = $this->entityTypeManager->getStorage('user')->loadByProperties(['name' => $username]);
    $user = reset($users); // Return the first user found, or NULL if none.
    if (!$user) {
        return null; // Si no se encuentra ningún usuario, retorna NULL.
    }

    return $user; // Devuelve el objeto de usuario.
  }

}
