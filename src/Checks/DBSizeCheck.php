<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use bitbirddev\OhDearBundle\Support\DbConnectionInfo;
use Doctrine\DBAL\Connection;
use Throwable;

final class DBSizeCheck implements CheckInterface
{
    protected ?float $failWhenDBSizeGreater = null;

    public function __construct(
        private readonly Connection $connection,
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
        $dbInfo = new DbConnectionInfo();

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Database Size',
        );

        try {
            $size = $dbInfo->databaseSizeInMb($this->connection);

            $result->shortSummary($dbInfo->databaseSizeInMb($this->connection).' MB')->meta(['size_in_mb' => $size])->ok();

            if ($this->failWhenDBSizeGreater) {
                if ($size > $this->failWhenDBSizeGreater) {
                    $result->shortSummary($dbInfo->databaseSizeInMb($this->connection).' MB')->failed('DB size is greater than '.$this->failWhenDBSizeGreater.' MB');
                }
            }
        } catch (Throwable) {
            $result->shortSummary('not ok')->failed('could not determine database size');
        }

        return $result;
    }

    public function failWhenDBSizeGreater(float $size): self
    {
        $this->failWhenDBSizeGreater = $size;

        return $this;
    }
}
