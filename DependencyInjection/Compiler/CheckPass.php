<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\DependencyInjection\Compiler;

use bitbirddev\OhDearBundle\HealthChecker;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class CheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(HealthChecker::class)) {
            return;
        }

        $definition = $container->findDefinition(HealthChecker::class);

        $taggedServices = $container->findTaggedServiceIds('oh_dear.checker');

    ray($taggedServices);
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addHealthChecker', [new Reference($id)]);
        }
    }
}
