<?php

namespace DatingVIP\Component\Etcd\Command;

use DatingVIP\Component\Etcd\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EtcdKeySetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:key:set')
            ->setDescription(
                'Set a key'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to set'
            )
            ->addArgument(
                'value',
                InputArgument::REQUIRED,
                'Value to set'
            )
            ->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server',
                'http://127.0.0.1:4001'
            )
            ->addOption(
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
        $value = $input->getArgument('value');
        $ttl = $input->getOption('ttl');
        echo "Setting `$key` to `$value`\n";
        $client = new Client($server);
        $data = $client->keySet($key, $value, $ttl);

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo $json;
    }
}
