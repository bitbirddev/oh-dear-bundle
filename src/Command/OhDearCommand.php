<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

abstract class OhDearCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addOption('ohdear-uuid', null, InputOption::VALUE_REQUIRED);
    }

    final protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $ohdearUuid = $input->getOption('ohdear-uuid');

        if ($ohdearUuid) {
            $start = microtime(true);

            try {
                return $this->doExecute($input, $output);
            } catch (Exception $e) {
                $exitCode = Command::FAILURE;
                $failureMessage = $e->getMessage();

                return $exitCode;
            } finally {
                $duration = microtime(true) - $start;
                $httpClient = HttpClient::createForBaseUri('https://ping.ohdear.app/');

                $body = [
                    'memory' => memory_get_peak_usage(true),
                    'runtime' => $duration,
                ];

                if (isset($exitCode)) {
                    $body['exit_code'] = $exitCode;
                }

                if (isset($failureMessage)) {
                    $body['failure_message'] = $failureMessage;
                }

                try {
                    $httpClient->request('POST', $ohdearUuid, [
                        'body' => $body,
                    ]);
                } catch (TransportExceptionInterface) {
                    // silently ignore
                }
            }
        } else {
            return $this->doExecute($input, $output);
        }
    }

    abstract protected function doExecute(InputInterface $input, OutputInterface $output): int;
}
