<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Support\DbConnectionInfo;
use Doctrine\ORM\EntityManagerInterface;
use OhDear\HealthCheckResults\CheckResult;
use Throwable;

final class DBSizeCheck implements CheckInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        protected DbConnectionInfo $dbinfo,
        protected ?float $failWhenDBSizeGreater = null
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
        $result = new CheckResult(
            name: $this->identify(),
            label: 'Database Size',
        );

        try {
            $connection = $this->entityManager->getConnection();
            $size = $this->dbinfo->databaseSizeInMb($connection);
            $result->shortSummary($this->dbinfo->databaseSizeInMb($connection).' MB');
            $result->status(CheckResult::STATUS_OK);
            $result->meta(['size_in_mb' => $size]);

            if ($this->failWhenDBSizeGreater) {
                if ($size > $this->failWhenDBSizeGreater) {
                    $result->status(CheckResult::STATUS_FAILED);
                    $result->shortSummary($this->dbinfo->databaseSizeInMb($connection).' MB');
                    $result->notificationMessage('DB size is greater than '.$this->failWhenDBSizeGreater.' MB');
                }
            }
        } catch (Throwable) {
            $result->shortSummary('not ok');
            $result->notificationMessage('could not determine database size');
            $result->status(CheckResult::STATUS_FAILED);
        }

        return $result;
    }
}
