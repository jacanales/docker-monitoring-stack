<?php

namespace Demo\StatsD\Command;

use Domnikl\Statsd\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Jacanales\StatsD\Client;
use Symfony\Component\Dotenv\Dotenv;

class RandomMetricsCommand extends Command
{
    private const COMMAND_NAME = 'run:metrics';
    private Client $client;

    public function __construct(string $name = null)
    {
        (new Dotenv())->load(__DIR__ . '/../.env');

        $connection = new Connection\UdpSocket(
            $_ENV['TELEGRAF_HOST'],
            $_ENV['TELEGRAF_PORT'],
            $_ENV['TELEGRAF_TIMEOUT'],
            $_ENV['TELEGRAF_PERSISTENT'],
        );

        $this->client = new Client($connection, $_ENV['DEFAULT_NAMESPACE']);

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName(self::COMMAND_NAME)
            ->setDescription('Send random metrics to Telegraf')
        ;
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln('Sending metrics to Telegraf');
        $output->writeln('Press CMD+C to stop');
        while (true) {
            $count = random_int(1000, 10000);

            $this->client->count('random_increment', $count, ['app' => 'demo']);
            sleep(1/100);
        }
    }
}