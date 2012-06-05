<?php

namespace Varspool\WebsocketBundle\Command;

use Varspool\WebsocketBundle\VarspoolWebsocketBundle;

use \InvalidArgumentException;

use WebSocket\Server;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class ListenCommand extends ContainerAwareCommand
{
    const DEFAULT_INTERFACE = 'localhost';
    const DEFAULT_PORT = 8000;

    /**
     * @see Symfony\Component\Console\Command.Command::configure()
     */
    protected function configure()
    {
        $this
            ->setName('websocket:listen')
            ->setDescription('Listen for websocket requests (blocks indefinitely)')
            ->addArgument(
                'server_name',
                InputArgument::OPTIONAL,
                'The server name (from your varspool_websocket configuration)',
                'default'
            );
    }

    /**
     * @see Symfony\Component\Console\Command.Command::execute()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('server_name');

        $manager = $this->getContainer()->get('varspool_websocket.server_manager');
        $manager->setLogger(function ($message, $level) use ($output) {
            $output->writeln($level . ': ' . $message);
        });

        $server = $manager->getServer($name);
    	$server->run();
    }
}
