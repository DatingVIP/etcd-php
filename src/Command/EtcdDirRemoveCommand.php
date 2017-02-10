<?php

namespace DatingVIP\Component\Etcd\Command;

use DatingVIP\Component\Etcd\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EtcdDirRemoveCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:dir:remove')
            ->setDescription(
                'Removes the key if it is an empty directory or a key-value pair'
            )
            ->addArgument(
                'key',
                InputArgument::REQUIRED,
                'Key to remove'
            )
            ->addArgument(
                'server',
                InputArgument::OPTIONAL,
                'Base url of etcd server and the default is http://127.0.0.1:4001',
                'http://127.0.0.1:4001'
            )
            ->addOption(
                'recursive',
                null,
                InputOption::VALUE_NONE,
                'To delete a directory that holds keys'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        $recursive = $input->getOption('recursive');
        $output->writeln("<info>Removing key `$key`</info>");
        $client = new Client($server);
        $data = $client->dirRemove($key, (bool)$recursive);
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        echo $json;
    }
}
