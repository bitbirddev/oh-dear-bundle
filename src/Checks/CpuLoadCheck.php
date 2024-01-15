<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Spatie\CpuLoadHealthCheck\CpuLoad;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class CpuLoadCheck implements CheckInterface
{
    protected ?float $failWhenLoadIsHigherInTheLastMinute = null;
    protected ?float $failWhenLoadIsHigherInTheLast5Minutes = null;
    protected ?float $failWhenLoadIsHigherInTheLast15Minutes = null;

    public function __construct(
    ) {
    }

    public function identify(): string
    {
        return 'CPU-Load';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $cpuLoad = $this->measureCpuLoad();

        $result = CheckResult::make(
            name: $this->identify(),
            label: 'CPU-Load',
            shortSummary: "{$cpuLoad->lastMinute} {$cpuLoad->last5Minutes} {$cpuLoad->last15Minutes}",
            meta: [
                'last_minute' => $cpuLoad->lastMinute,
                'last_5_minutes' => $cpuLoad->last5Minutes,
                'last_15_minutes' => $cpuLoad->last15Minutes,
            ]
        );

        if ($this->failWhenLoadIsHigherInTheLastMinute) {
            if ($cpuLoad->lastMinute > $this->failWhenLoadIsHigherInTheLastMinute) {
                return $result->failed("The CPU load of the last minute is {$cpuLoad->lastMinute} which is higher than the allowed {$this->failWhenLoadIsHigherInTheLastMinute}");
            }
        }

        if ($this->failWhenLoadIsHigherInTheLast5Minutes) {
            if ($cpuLoad->last5Minutes > $this->failWhenLoadIsHigherInTheLast5Minutes) {
                return $result->failed("The CPU load of the last 5 minutes is {$cpuLoad->last5Minutes} which is higher than the allowed {$this->failWhenLoadIsHigherInTheLast5Minutes}");
            }
        }

        if ($this->failWhenLoadIsHigherInTheLast15Minutes) {
            if ($cpuLoad->last15Minutes > $this->failWhenLoadIsHigherInTheLast15Minutes) {
                return $result->failed("The CPU load of the last 15 minutes is {$cpuLoad->last15Minutes} which is higher than the allowed {$this->failWhenLoadIsHigherInTheLast15Minutes}");
            }
        }

        return $result->ok();
    }

    protected function measureCpuLoad(): CpuLoad
    {
        return CpuLoad::measure();
    }

    public function failWhenLoadIsHigherInTheLast15Minutes(float $load): self
    {
        $this->failWhenLoadIsHigherInTheLast15Minutes = $load;

        return $this;
    }

    public function failWhenLoadIsHigherInTheLast5Minutes(float $load): self
    {
        $this->failWhenLoadIsHigherInTheLast5Minutes = $load;

        return $this;
    }

    public function failWhenLoadIsHigherInTheLastMinute(float $load): self
    {
        $this->failWhenLoadIsHigherInTheLastMinute = $load;

        return $this;
    }
}
