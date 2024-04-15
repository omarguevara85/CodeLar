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
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\user\UserAuthInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\user\UserStorageInterface;

/**
 * Represents Login records as resources.
 *
 * @RestResource (
 *   id = "codelar_base_login",
 *   label = @Translation("Login"),
 *   uri_paths = {
 *     "create" = "/api-login/codelar-base-login"
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
final class LoginResource extends ResourceBase {

  protected $userAuth;
  protected $passwordChecker;

  public function __construct(
      array $configuration, 
      $plugin_id, 
      $plugin_definition, 
      array $serializer_formats, 
      $logger, 
      UserAuthInterface $user_auth, 
      UserStorageInterface $user_storage
  ) {
      parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
      $this->userAuth = $user_auth;
      $this->userStorage = $user_storage;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
      return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->getParameter('serializer.formats'),
          $container->get('logger.factory')->get('rest'),
          $container->get('user.auth'),
          $container->get('entity_type.manager')->getStorage('user')
      );
  }

  public function post(Request $request) {
    $credentials = json_decode($request->getContent(), TRUE);

    if (!empty($credentials['name']) && !empty($credentials['pass'])) {
        $uid = $this->userAuth->authenticate($credentials['name'], $credentials['pass']);
        if ($uid) {
            $user = $this->userStorage->load($uid);
            if ($user) {
                $roles = $user->getRoles();
                $jwt_service = \Drupal::service('jwt.authentication.jwt');
                $payload = [
                    'uid' => $uid,
                    'uuid' => $user->uuid(),
                    'name' => $user->getDisplayName(),
                    'mail' => $user->getEmail(),
                ];
                $token = $jwt_service->generateTokenPayLoad($payload, $uid);
                $user_id = $user->id();
                $user_uuid = $user->uuid();
                $logout_token = $jwt_service->generateTokenPayLoad(['uid' => [$uid]], ['exp' => time() + 300]);

                $response_data = [
                    'token' => $token,
                    'user_id' => $user_id,
                    'user_uuid' => $user_uuid,
                    'name' => $user->getDisplayName(),
                    'created' => $user->getCreatedTime(),
                    'updated' => $user->getChangedTime(),
                    'roles' => $roles,
                    'logout_token' => $logout_token
                ];
                return new ResourceResponse($response_data);
            } else {
                return new JsonResponse(['message' => 'User not found'], 404);
            }
        } else {
            return new JsonResponse(['message' => 'Invalid credentials'], 401);
        }
    } else {
        return new JsonResponse(['message' => 'Missing credentials'], 400);
    }
  }

}
