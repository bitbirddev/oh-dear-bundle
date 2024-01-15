<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\DependencyInjection;

use bitbirddev\OhDearBundle\Contracts\HealthCheckProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader;

final class OhDearExtension extends Extension
{
    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(HealthCheckProviderInterface::class)
            ->addTag('oh_dear.health_check_provider');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'oh_dear.secret',
            $config['secret']
        );

        $container->setParameter(
            'oh_dear.expiration_threshold',
            $config['expiration_threshold']
        );

        $yamlLoader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../../config'));
        $yamlLoader->load('services.yaml');
    }
}
