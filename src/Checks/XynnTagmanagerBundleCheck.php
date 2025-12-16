<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\Checks;

use bitbirddev\OhDearBundle\CheckResult;
use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class XynnTagmanagerBundleCheck implements CheckInterface
{
    protected ?string $expectedGtmId = null;

    public function __construct(
        protected KernelInterface $kernel,
        protected ParameterBagInterface $parameterBag,
    ) {
    }

    public function identify(): string
    {
        return 'GoogleTagManager-xynnn-google-tag-manager-bundle';
    }

    public function frequency(): int
    {
        return 0;
    }

    public function runCheck(): CheckResult
    {
        $result = CheckResult::make(
            name: $this->identify(),
            label: 'GoogleTagManager (xynn/google-tag-manager-bundle)',
        );

        // Check 1: Bundle installed and active
        if (!$this->hasGtmBundleInstalled()) {
            return $result->skipped('The xynnn/google-tag-manager-bundle is not installed');
        }

        // Check 2: Configuration exists in container
        if (!$this->hasValidConfiguration()) {
            return $result->failed('GoogleTagManager configuration is missing or invalid in container');
        }

        // Check 3: GTM_ID environment variable is set
        $gtmId = $this->getGtmIdFromEnv();
        if (null === $gtmId) {
            return $result->failed('GTM_ID environment variable is not set');
        }

        // Check 4: Optional - verify GTM ID value
        if (null !== $this->expectedGtmId && $gtmId !== $this->expectedGtmId) {
            return $result->failed("GTM ID mismatch: expected '{$this->expectedGtmId}', got '{$gtmId}'");
        }

        return $result->ok("GTM ID: {$gtmId}")->shortSummary($gtmId);
    }

    protected function hasGtmBundleInstalled(): bool
    {
        // Check if the bundle is registered in the kernel
        foreach ($this->kernel->getBundles() as $bundle) {
            if ($bundle instanceof \Xynnn\GoogleTagManagerBundle\GoogleTagManagerBundle) {
                return true;
            }
        }

        // Fallback: check if the bundle class exists
        return class_exists(\Xynnn\GoogleTagManagerBundle\GoogleTagManagerBundle::class);
    }

    protected function hasValidConfiguration(): bool
    {
        try {
            // Check if google_tag_manager.enabled exists and is true
            if (!$this->parameterBag->has('google_tag_manager.enabled')) {
                return false;
            }

            $enabled = $this->parameterBag->get('google_tag_manager.enabled');
            if (true !== $enabled) {
                return false;
            }

            // Check if google_tag_manager.id exists
            if (!$this->parameterBag->has('google_tag_manager.id')) {
                return false;
            }

            // Check if google_tag_manager.autoAppend exists
            if (!$this->parameterBag->has('google_tag_manager.autoAppend')) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    protected function getGtmIdFromEnv(): ?string
    {
        // Try $_ENV first
        if (isset($_ENV['GTM_ID']) && !empty($_ENV['GTM_ID'])) {
            return $_ENV['GTM_ID'];
        }

        // Fallback to getenv()
        $gtmId = getenv('GTM_ID');
        if (false !== $gtmId && !empty($gtmId)) {
            return $gtmId;
        }

        // Try to get from container parameter (resolved value)
        try {
            if ($this->parameterBag->has('google_tag_manager.id')) {
                $id = $this->parameterBag->get('google_tag_manager.id');
                if (!empty($id) && !str_starts_with($id, '%env(')) {
                    return $id;
                }
            }
        } catch (Exception $e) {
            // Ignore
        }

        return null;
    }

    public function expectGtmId(string $expectedId): self
    {
        $this->expectedGtmId = $expectedId;

        return $this;
    }
}
