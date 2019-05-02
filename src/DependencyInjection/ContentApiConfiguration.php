<?php

declare(strict_types=1);

namespace Libero\ContentApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use function count;

final class ContentApiConfiguration implements ConfigurationInterface
{
    private $rootName;

    public function __construct(string $rootName)
    {
        $this->rootName = $rootName;
    }

    public function getConfigTreeBuilder() : TreeBuilder
    {
        $treeBuilder = new TreeBuilder();

        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->root($this->rootName);
        $rootNode
            ->fixXmlConfig('service')
            ->children()
                ->append($this->getServicesDefinition())
            ->end()
        ;

        return $treeBuilder;
    }

    private function getServicesDefinition() : ArrayNodeDefinition
    {
        $builder = new TreeBuilder();

        /** @var ArrayNodeDefinition $servicesNode */
        $servicesNode = $builder->root('services');
        $servicesNode
            ->info('Content APIs to create')
            ->normalizeKeys(false)
            ->useAttributeAsKey('prefix')
            ->arrayPrototype()
                ->children()
                    ->scalarNode('items')
                        ->isRequired()
                        ->info('The service name to use.')
                    ->end()
                    ->booleanNode('include_prefix')
                        ->defaultTrue()
                        ->info('Whether to include the service prefix in the routes.')
                    ->end()
                    ->scalarNode('put_workflow')
                        ->info('Workflow name for PUT requests.')
                    ->end()
                ->end()
            ->end()
            ->validate()
                ->ifTrue(function (array $services) : bool {
                    if (count($services) > 1) {
                        foreach ($services as $service) {
                            if (false === $service['include_prefix']) {
                                return true;
                            }
                        }
                    }

                    return false;
                })
                ->thenInvalid("Services prefixes must be included if there's more than one service.")
            ->end()
        ;

        return $servicesNode;
    }
}
