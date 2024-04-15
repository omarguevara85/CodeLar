<?php

declare(strict_types=1);

namespace Drupal\codelar_base\Plugin\rest\resource;

use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\rest\ModifiedResourceResponse;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Route;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\codelar_base\UserCustom;

/**
 * Represents Register records as resources.
 *
 * @RestResource (
 *   id = "codelar_base_register",
 *   label = @Translation("Register"),
 *   uri_paths = {
 *     "create" = "/api/codelar-base-register"
 *   }
 * )
 *
 * @DCG
 * The plugin exposes key-value records as REST resources. In order to enable it
 * import the resource configuration into active configuration storage. An
 * example of such configuration can be located in the following file:
 * core/modules/rest/config/optional/rest.resource.entity.node.yml.
 * Alternatively, you can enable it through admin interface provider by REST UI
 * module.
 * @see https://www.drupal.org/project/restui
 *
 * @DCG
 * Notice that this plugin does not provide any validation for the data.
 * Consider creating custom normalizer to validate and normalize the incoming
 * data. It can be enabled in the plugin definition as follows.
 * @code
 *   serialization_class = "Drupal\foo\MyDataStructure",
 * @endcode
 *
 * @DCG
 * For entities, it is recommended to use REST resource plugin provided by
 * Drupal core.
 * @see \Drupal\rest\Plugin\rest\resource\EntityResource
 */
final class RegisterResource extends ResourceBase {

  /**
   * The key-value storage.
   */
  private readonly KeyValueStoreInterface $storage;

  /**
   * {@inheritdoc}
   */
  protected $currentUser;
  protected $passwordService;
  protected $userManager;

  public function __construct(
      array $configuration,
      $plugin_id,
      $plugin_definition,
      array $serializer_formats,
      $logger,
      AccountProxyInterface $current_user,
      PasswordInterface $password_service,
      UserCustom $user_custom
  ) {
      parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
      $this->currentUser = $current_user;
      $this->passwordService = $password_service;
      $this->user_custom = $user_custom;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->getParameter('serializer.formats'),
          $container->get('logger.factory')->get('rest'),
          $container->get('current_user'),
          $container->get('password'),
          $container->get('codelar_base.user_custom')
      );
  }

  public function post(Request $request) {
    // Autenticación JWT y comprobación de permisos
    if (!$this->currentUser->isAuthenticated() || !$this->currentUser->hasPermission('administer users')) {
      return new JsonResponse(['message' => 'Access denied'], 403);
    }

    $data = json_decode($request->getContent(), TRUE);

    // Pendiente por validar la existencia del usuario

    // Crear el usuario
    if (!empty($data['username']) && !empty($data['password']) && !empty($data['email'])) {
      $user = User::create([
        'name' => $data['username'],
        'mail' => $data['email'],
        'pass' => $this->passwordService->hash($data['password']),
        'status' => 1,
        'roles' => ['authenticated'],  // Ajustar según necesidad
      ]);

      $user->save();
      return new ResourceResponse(['user_id' => $user->id(), 'message' => 'User created successfully']);
    } else {
      return new JsonResponse(['message' => 'Missing required fields'], 400);
    }
  }

}
