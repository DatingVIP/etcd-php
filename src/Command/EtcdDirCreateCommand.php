<?php

namespace DatingVIP\Component\Etcd\Command;

use DatingVIP\Component\Etcd\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EtcdDirCreateCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:dir:create')
            ->setDescription(
                'Make a new directory'
            )->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to set'
            )->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server and the default is http://127.0.0.1:4001',
                'http://127.0.0.1:4001'
            )->addOption(
                'ttl',
                null,
                InputOption::VALUE_OPTIONAL,
                0
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        $ttl = $input->getOption('ttl');
        $output->writeln("<info>making directory `$key`</info>");
        $client = new Client($server);
        $data = $client->dirCreate($key, $ttl);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo $json;
    }
}
