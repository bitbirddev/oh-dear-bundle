<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use bitbirddev\OhDearBundle\Support\DbConnectionInfo;
use Doctrine\DBAL\Connection;
use Throwable;

final class DBConnectionCountCheck implements CheckInterface
{
    protected ?int $failWhenConnectionCountGreater = null;

    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function identify(): string
    {
        return 'DB Connection Count';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $dbInfo = new DbConnectionInfo();

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'DB Connection Count',
        );

        try {
            $connections = $dbInfo->connectionCount($this->connection);

            $result
                ->meta(['connections' => $dbInfo->connectionCount($this->connection)])
                ->shortSummary($dbInfo->connectionCount($this->connection).' connections')->ok();

            if ($this->failWhenConnectionCountGreater) {
                if ($connections > $this->failWhenConnectionCountGreater) {
                    $result
                        ->meta(['connections' => $dbInfo->connectionCount($this->connection)])
                        ->shortSummary("{$dbInfo->connectionCount($this->connection)} connections but only max. {$this->failWhenConnectionCountGreater} connections allowed")
                        ->failed();
                }
            }
        } catch (Throwable) {
            $result->shortSummary('not connected')->failed('Database connection is not working');
        }

      return $result;
    }

    public function failWhenConnectionCountGreater(int $count): self
    {
        $this->failWhenConnectionCountGreater = $count;

        return $this;
    }
}
