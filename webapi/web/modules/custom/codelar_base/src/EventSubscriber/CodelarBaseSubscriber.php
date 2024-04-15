<?php

declare(strict_types=1);

namespace Drupal\codelar_base\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @todo Add description for this subscriber.
 */
final class CodelarBaseSubscriber implements EventSubscriberInterface {

  public function checkForToken(RequestEvent $event) {
    $request = $event->getRequest();
    if (strpos($request->getPathInfo(), '/api/') === 0) {
      // Se valida la ruta incial del api

      $auth_header = $request->headers->get('Authorization');
      if ($auth_header && preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];

        $jwt_service = \Drupal::service('jwt.authentication.jwt');
        try {
          $payload = $jwt_service->decodeToken($token);
          // Añadir lógica para manejar el payload y determinar si el acceso es permitido.
          // Logica...
          \Drupal::logger('CodeLar')->debug('<pre><code>' . print_r($payload, TRUE) . '</code></pre>');
        } catch (\Exception $e) {
          // Token inválido, denegar acceso.
          $event->setResponse(new JsonResponse(['message' => 'Access denied: ' . $e->getMessage()], 403));
        }
      } else {
        // No hay token, denegar acceso.
        $event->setResponse(new JsonResponse(['message' => 'No token provided'], 401));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      KernelEvents::REQUEST => [['checkForToken', 20]],
    ];
  }

}
