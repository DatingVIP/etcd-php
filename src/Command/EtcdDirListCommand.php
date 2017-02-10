<?php

namespace DatingVIP\Component\Etcd\Command;

use DatingVIP\Component\Etcd\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use DatingVIP\Component\Etcd\Http\Curl;

class EtcdDirListCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('etcd:dir:list')
            ->setDescription(
                'Retrieve a directory'
            )
            ->addArgument(
                'key',
                InputArgument::OPTIONAL,
                'Key to set',
                '/'
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
                'returns all values for key and child keys'
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $server = $input->getArgument('server');
        $key = $input->getArgument('key');
        $recursive = $input->getOption('recursive');

        $client = new Client($server);
        $http = new Curl();
        $client->setHttpClient($http);

        $data = $client->dirList($key, $recursive);
        $output->writeln($data);
    }
}
