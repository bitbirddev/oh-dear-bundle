<?php

declare(strict_types=1);

namespace bitbirddev\OhDearBundle\DependencyInjection;

use bitbirddev\OhDearBundle\Contracts\CheckInterface;
use bitbirddev\OhDearBundle\Contracts\HealthCheckProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;

final class OhDearExtension extends Extension implements PrependExtensionInterface
{
    public function prepend(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->prependExtensionConfig('framework', [
            'cache' => [
                'pools' => [
                    'cache.ohdear' => [
                        'clearer' => 'cache.app_clearer',
                        'public' => true,
                        'tags' => true,
                        'adapter' => 'cache.adapter.redis_tag_aware',
                        'provider' => 'redis://%env(REDIS_CACHE_HOST)%/%env(REDIS_CACHE_DB)%',
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param array<mixed> $configs
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerForAutoconfiguration(HealthCheckProviderInterface::class)
            ->addTag('oh_dear.health_check_provider');

        $container->registerForAutoconfiguration(CheckInterface::class)
            ->addTag('oh_dear.health_check');

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
