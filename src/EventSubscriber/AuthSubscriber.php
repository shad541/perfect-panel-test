<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AuthSubscriber implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire('%env(API_TOKEN)%')]
        private string $token,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 64]];
    }

    public function onRequest(RequestEvent $event): void
    {
        $header = $event->getRequest()->headers->get('Authorization', '');

        if ($header !== 'Bearer '.$this->token) {
            throw new ApiException('Invalid token', 403);
        }
    }
}
