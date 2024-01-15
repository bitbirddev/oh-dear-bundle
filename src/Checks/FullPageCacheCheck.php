<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use Pimcore\Config;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;

final class FullPageCacheCheck implements CheckInterface
{
    protected bool $expected = true;

    public function __construct(
        protected Config $config,
    ) {
    }

    public function identify(): string
    {
        return 'Full Page Cache';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = CheckResult::make(
            name: $this->identify(),
            label: 'Full Page Cache',
        );

        if ($conf = $this->config['full_page_cache']) {
            $actual = (bool) $conf['enabled'];
            $result->shortSummary($this->convertToWord($actual));
            $result->meta(
                [
                    'expected' => $this->expected,
                    'actual' => $this->convertToWord($actual),
                ]
            );
        } else {
            return $result->skipped('FullPageCache is not configured');
        }

        return $this->expected === $actual
            ? $result->ok()
            : $result->failed("The FullPageCache was expected to be `{$this->convertToWord($this->expected)}`, but actually was `{$this->convertToWord($actual)}`");

        return $result;
    }

    protected function convertToWord(bool $boolean): string
    {
        return $boolean ? 'enabled' : 'disabled';
    }

    public function expect(bool $value): self
    {
        $this->expected = $value;

        return $this;
    }
}
