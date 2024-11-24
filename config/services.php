<?php

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return static function (ContainerConfigurator $configurator) {
    $services = $configurator->services();

    $services->defaults()
        ->autowire(true)
        ->autoconfigure(true);

    $services->load('MessagesGrouping\\', '../src/')
        ->exclude('../src/DependencyInjection/')
        ->exclude('../src/Entity/')
        ->exclude('../src/Kernel.php');

    $services->set(MessagesGrouping\Event\MessageGroupSubscriber::class)
        ->arg('$messageBus', new Reference('messenger.bus.default'))
        ->arg('$processor', new Reference(MessagesGrouping\Service\MessageProcessor::class))
        ->tag('kernel.event_subscriber');

    $services->set(MessagesGrouping\Service\MessageProcessor::class);

    $services->set(MessagesGrouping\Event\CustomSubscriber::class)
        ->parent(MessagesGrouping\Event\MessageGroupSubscriber::class)
        ->arg('$processor', new Reference(MessagesGrouping\Service\MessageProcessor::class))  // Optional, can override parent arguments
        ->tag('kernel.event_subscriber');

};
