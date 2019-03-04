<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\DependencyInjection;

use Libero\ContentApiBundle\Controller\GetItemController;
use Libero\ContentApiBundle\Controller\GetItemListController;
use Libero\ContentApiBundle\Routing\Loader;
use Libero\PingController\PingController;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use function array_keys;
use function count;
use function current;
use function str_replace;

final class ContentApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        foreach (array_keys($config['services']) as $prefix) {
            $config['services'][$prefix]['prefix'] = $prefix;
        }

        if (1 === count($config['services']) && false === current($config['services'])['include_prefix']) {
            $config['services'] = ['' => current($config['services'])];
        }

        foreach (array_keys($config['services']) as $name) {
            if ('' === $name) {
                $config['services'][$name]['internal_name'] = 'service';
            } else {
                $config['services'][$name]['internal_name'] = str_replace('-', '_', (string) $name);
            }

            $this->addContentService((string) $name, $config['services'][$name], $container);
        }

        $container->findDefinition(Loader::class)->setArgument(0, $config['services']);
    }

    private function addContentService(string $name, array $config, ContainerBuilder $container) : void
    {
        $ping = new Definition(PingController::class);
        $ping->addTag('controller.service_arguments');
        $container->setDefinition("libero.content_api.{$config['internal_name']}.ping", $ping);

        $getItem = new Definition(GetItemController::class);
        $getItem->addTag('controller.service_arguments');
        $getItem->addArgument(new Reference($config['items']));
        $container->setDefinition("libero.content_api.{$config['internal_name']}.item.get", $getItem);

        $getItemList = new Definition(GetItemListController::class);
        $getItemList->addTag('controller.service_arguments');
        $getItemList->addArgument(new Reference($config['items']));
        $getItemList->addArgument($config['prefix']);
        $container->setDefinition("libero.content_api.{$config['internal_name']}.item_list.get", $getItemList);
    }

    public function getConfiguration(array $config, ContainerBuilder $container) : ConfigurationInterface
    {
        return new ContentApiConfiguration($this->getAlias());
    }

    public function getNamespace() : string
    {
        return 'http://libero.pub/schema/content-api-bundle';
    }

    public function getXsdValidationBasePath() : string
    {
        return __DIR__.'/../Resources/config/schema/content-api-bundle';
    }

    public function getAlias() : string
    {
        return 'content_api';
    }
}
