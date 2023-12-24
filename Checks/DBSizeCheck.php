<?php

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use bitbirddev\OhDearBundle\Support\DbConnectionInfo;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OhDear\HealthCheckResults\CheckResult;
use Throwable;

final class DBSizeCheck implements CheckInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        protected DbConnectionInfo $dbinfo
    ) {
    }

    public function identify(): string
    {
        return 'Database Size';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $connection = $this->entityManager->getConnection();

        $result = new CheckResult(
            name: $this->identify(),
            label: 'Database Size',
            shortSummary: $this->dbinfo->databaseSizeInMb($connection).' MB',
            status: CheckResult::STATUS_OK,
            meta: ['size_in_mb' => $this->dbinfo->databaseSizeInMb($connection)]
        );

        try {
            $this->entityManager->getConnection()->connect();
            if (false === $this->entityManager->getConnection()->isConnected()) {
                throw new Exception('Database connection is not working');
            }
        } catch (Throwable) {
            $result->status = CheckResult::STATUS_FAILED;
            $result->shortSummary = 'not connected';
            $result->notificationMessage = 'Database connection is not working';
        }

        return $result;
    }
}
