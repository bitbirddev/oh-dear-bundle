<?php

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\Checks\CheckInterface;
use OhDear\HealthCheckResults\CheckResult;
use Spatie\CpuLoadHealthCheck\CpuLoad;

class CpuLoadCheck implements CheckInterface
{

  public function __construct(
    protected ?float $failWhenLoadIsHigherInTheLastMinute = null,
    protected ?float $failWhenLoadIsHigherInTheLast5Minutes = null,
    protected ?float $failWhenLoadIsHigherInTheLast15Minutes = null
) {}

    public function identify(): string
    {
        return 'cpuload-checker';
    }

    public function frequency(): int
    {
        return 0;
    }


    public function runCheck(): CheckResult
    {
        $cpuLoad = $this->measureCpuLoad();

        $result = CheckResult::make(name:'CPU-Load', label: 'CPU-Load')
            ->shortSummary(
                "{$cpuLoad->lastMinute} {$cpuLoad->last5Minutes} {$cpuLoad->last15Minutes}"
            )
            ->meta([
                'last_minute' => $cpuLoad->lastMinute,
                'last_5_minutes' => $cpuLoad->last5Minutes,
                'last_15_minutes' => $cpuLoad->last15Minutes,
            ]);

        if ($this->failWhenLoadIsHigherInTheLastMinute) {
            if ($cpuLoad->lastMinute > ($this->failWhenLoadIsHigherInTheLastMinute)) {
                return $result->status(CheckResult::STATUS_FAILED)->notificationMessage("The CPU load of the last minute is {$cpuLoad->lastMinute} which is higher than the allowed {$this->failWhenLoadIsHigherInTheLastMinute}");
            }
        }

        if ($this->failWhenLoadIsHigherInTheLast5Minutes) {
            if ($cpuLoad->last5Minutes > ($this->failWhenLoadIsHigherInTheLast5Minutes)) {
                return $result->status(CheckResult::STATUS_FAILED)->notificationMessage("The CPU load of the last 5 minutes is {$cpuLoad->last5Minutes} which is higher than the allowed {$this->failWhenLoadIsHigherInTheLast5Minutes}");
            }
        }

        if ($this->failWhenLoadIsHigherInTheLast15Minutes) {
            if ($cpuLoad->last15Minutes > ($this->failWhenLoadIsHigherInTheLast15Minutes)) {
                return $result->status(CheckResult::STATUS_FAILED)->notificationMessage("The CPU load of the last 15 minutes is {$cpuLoad->last15Minutes} which is higher than the allowed {$this->failWhenLoadIsHigherInTheLast15Minutes}");
            }
        }

        return $result->status(CheckResult::STATUS_OK);
    }

    protected function measureCpuLoad(): CpuLoad
    {
        return CpuLoad::measure();
    }
}
