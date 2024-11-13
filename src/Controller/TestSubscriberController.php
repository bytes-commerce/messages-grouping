<?php

namespace App\Controller;

use App\Event\EventMessage;
use App\Event\MessageGroupSubscriber;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class TestSubscriberController extends AbstractController
{
    private readonly MessageBusInterface $messageBus;

    private readonly MessageGroupSubscriber $subscriber;

    public function __construct(MessageBusInterface $messageBus, MessageGroupSubscriber $subscriber)
    {
        $this->messageBus = $messageBus;
        $this->subscriber = $subscriber;
    }

    #[Route(path: '/test-dispatch', name: 'test_dispatch')]
    public function testDispatch(): Response
    {
        try {
            $this->messageBus->dispatch(new EventMessage(1, 'First task update'));
            $this->messageBus->dispatch(new EventMessage(1, 'Second task update'));

            $this->subscriber->processGroupedMessages();

            return new Response('Messages dispatched and grouped.');
        } catch(HandlerFailedException $e){
            $errorMessage = 'Message dispatch failed: ' . $e->getMessage();
            return new Response($errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\RuntimeException $e){
            $errorMessage = 'Failed to process grouped messages: ' . $e->getMessage();
            return new Response($errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Exception $e){
            $errorMessage = 'An unexpected error occured: ' . $e->getMessage();
            return new Response($errorMessage, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}