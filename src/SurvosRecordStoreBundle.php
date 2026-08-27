<?php

declare(strict_types=1);

namespace Survos\RecordStoreBundle;

use Survos\Kit\AbstractSurvosBundle;
use Survos\Kit\SurvosKitBundle;
use Survos\RecordStore\Contract\AdapterFactoryInterface;
use Survos\RecordStore\Registry\RecordStoreRegistry;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Kernel\RequiredBundle;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

#[RequiredBundle(SurvosKitBundle::class)]
// Symfony\Component\HttpKernel\Bundle\Bundle <-- Flex auto-registration marker (see Survos\Kit\AbstractSurvosBundle)
final class SurvosRecordStoreBundle extends AbstractSurvosBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()->children()
            ->arrayNode('connections')
                ->info('Named provider connections keyed by local alias.')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('driver')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('options')
                            ->normalizeKeys(false)
                            ->useAttributeAsKey('name')
                            ->variablePrototype()->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
                ->defaultValue([])
            ->end()
            ->arrayNode('applications')
                ->info('Logical applications mapped to a configured connection and provider resource ID.')
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->children()
                        ->scalarNode('connection')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('id')->isRequired()->cannotBeEmpty()->end()
                        ->arrayNode('tables')
                            ->useAttributeAsKey('name')
                            ->arrayPrototype()
                                ->children()
                                    ->scalarNode('id')->isRequired()->cannotBeEmpty()->end()
                                    ->arrayNode('fields')
                                        ->useAttributeAsKey('name')
                                        ->variablePrototype()->end()
                                        ->defaultValue([])
                                    ->end()
                                ->end()
                            ->end()
                            ->defaultValue([])
                        ->end()
                    ->end()
                ->end()
                ->defaultValue([])
            ->end()
        ->end();
    }

    /** @param array<string, mixed> $config */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        parent::loadExtension($config, $container, $builder);

        $builder->registerForAutoconfiguration(AdapterFactoryInterface::class)
            ->addTag('survos_record_store.adapter_factory');

        $services = $container->services()->defaults()->autowire()->autoconfigure();
        $services->set(RecordStoreRegistry::class)
            ->arg('$connections', $config['connections'])
            ->arg('$applications', $config['applications'])
            ->arg('$factories', tagged_iterator('survos_record_store.adapter_factory'))
            ->public();
    }
}
