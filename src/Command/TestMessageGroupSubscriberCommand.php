<?php

namespace MessagesGrouping\Command;

use MessagesGrouping\Event\EventMessage;
use MessagesGrouping\Event\MessageGroupSubscriber;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Messenger\MessageBusInterface;


#[AsCommand(name: 'app:test-message-group')]
class TestMessageGroupSubscriberCommand extends Command
{
    private readonly MessageBusInterface $messageBus;

    private readonly MessageGroupSubscriber $subscriber;

    public function __construct(MessageBusInterface $messageBus, MessageGroupSubscriber $subscriber)
    {
        parent::__construct();
        $this->messageBus = $messageBus;
        $this->subscriber = $subscriber;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Dispatching messages');

        $this->messageBus->dispatch(new EventMessage(1, 'Update 1'));
        $this->messageBus->dispatch(new EventMessage(1, 'Update 2'));

        $this->subscriber->processGroupedMessages();

        $output->writeln("Messages dispatched and processed");

        return Command::SUCCESS;
    }

}