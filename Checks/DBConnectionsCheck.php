<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Support\DbConnectionInfo;
use Doctrine\ORM\EntityManagerInterface;
use OhDear\HealthCheckResults\CheckResult;
use Throwable;

final class DBConnectionsCheck implements CheckInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        protected DbConnectionInfo $dbinfo,
        protected ?float $failWhenConnectionCountGreater = null
    ) {
    }

    public function identify(): string
    {
        return 'Database Connections';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = new CheckResult(
            name: $this->identify(),
            label: 'Database Connections',
        );

        try {
            $connection = $this->entityManager->getConnection();
            $connections = $this->dbinfo->connectionCount($connection);
            $result->shortSummary($this->dbinfo->connectionCount($connection).' connections');
            $result->status(CheckResult::STATUS_OK);
            $result->meta(['connections' => $this->dbinfo->connectionCount($connection)]);

            if ($this->failWhenConnectionCountGreater) {
                if ($connections > $this->failWhenConnectionCountGreater) {
                    $result->shortSummary("{$this->dbinfo->connectionCount($connection)} connections but only max. {$this->failWhenConnectionCountGreater} connections allowed");
                    $result->status(CheckResult::STATUS_FAILED);
                    $result->meta(['connections' => $this->dbinfo->connectionCount($connection)]);
                }
            }
        } catch (Throwable) {
            $result->status = CheckResult::STATUS_FAILED;
            $result->shortSummary = 'not connected';
            $result->notificationMessage = 'Database connection is not working';
        }

      return $result;
    }
}
