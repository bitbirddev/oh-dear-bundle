<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\DependencyInjection\Compiler;

use bitbirddev\OhDearBundle\HealthChecker;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class CheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(HealthChecker::class)) {
            return;
        }

        $definition = $container->findDefinition(HealthChecker::class);
    }
}
