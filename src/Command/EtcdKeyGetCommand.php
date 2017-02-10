<?php

namespace DatingVIP\Component\Etcd\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use DatingVIP\Component\Etcd\Client;
use DatingVIP\Component\Etcd\Http\Curl;

class EtcdKeyGetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:key:get')
            ->setDescription(
                'Get a key'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to get'
            )->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server',
                'http://127.0.0.1:4001'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        echo "Getting `$key` on `$server`\n";

        $data = (new Client($server))->keyGet($key);

        $output->writeln($data);
    }
}
