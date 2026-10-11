<?php

namespace App\EventSubscriber;

use App\Exception\ApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => 'onException'];
    }

    public function onException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();
        $code = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
        $message = $e instanceof ApiException ? $e->getMessage() : Response::$statusTexts[$code];

        $event->setResponse(new JsonResponse([
            'status' => 'error',
            'code' => $code,
            'message' => $message,
        ], $code));
    }
}
