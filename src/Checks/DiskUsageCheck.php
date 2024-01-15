<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Spatie\Regex\Regex;
use Symfony\Component\Process\Process;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class DiskUsageCheck implements CheckInterface
{
    protected int $warningThreshold = 80;
    protected int $errorThreshold = 90;

    public function __construct(
        protected ?string $filesystemName = null
    ) {
    }

    public function identify(): string
    {
        return 'Disk Usage';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $diskSpaceUsedPercentage = $this->getDiskUsagePercentage();

        $result = CheckResult::make(
            name: 'Disk Usage',
            label: 'Disk Usage',
            meta : ['disk_space_used_percentage' => $diskSpaceUsedPercentage],
            shortSummary: $diskSpaceUsedPercentage.'%'
        );

        if ($diskSpaceUsedPercentage > $this->errorThreshold) {
            return $result->failed("The disk is almost full ({$diskSpaceUsedPercentage}% used).");
        }

        if ($diskSpaceUsedPercentage > $this->warningThreshold) {
            return $result->warning("The disk is almost full ({$diskSpaceUsedPercentage}% used).");
        }

        return $result->ok();
    }

    protected function getDiskUsagePercentage(): int
    {
        $process = Process::fromShellCommandline('df -P '.($this->filesystemName ?: '.'));

        $process->run();

        $output = $process->getOutput();

        return (int) Regex::match('/(\d*)%/', $output)->group(1);
    }

    public function warningThreshold(int $threshold): self
    {
        $this->warningThreshold = $threshold;

        return $this;
    }

    public function errorThreshold(int $threshold): self
    {
        $this->errorThreshold = $threshold;

        return $this;
    }
}
