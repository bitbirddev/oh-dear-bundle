<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Doctrine\DBAL\Connection;
use Exception;
use Throwable;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class DBConnectionCheck implements CheckInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function identify(): string
    {
        return 'Database';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Database connection status',
        );

        try {
            $this->connection->connect();
            if (false === $this->connection->isConnected()) {
                throw new Exception('Database connection is not working');
            }
            $result->ok()->shortSummary('connected');
        } catch (Throwable) {
            $result->failed('Database connection is not working')->shortSummary('not connected');
        }

        return $result;
    }
}
