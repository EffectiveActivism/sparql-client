<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Bundle;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\DependencyInjection\Configuration;
use EffectiveActivism\SparQlClient\DependencyInjection\SparQlClientExtension;
use EffectiveActivism\SparQlClient\EffectiveActivismSparQlClientBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class BundleTest extends KernelTestCase
{
    const TEST_CONFIG = [
        'sparql_endpoint' => 'http://test-sparql-endpoint:9999/blazegraph/sparql',
        'shacl_endpoint' => '',
        'namespaces' => [
            'schema' => 'http://schema.org/',
        ]
    ];

    public function testBundle()
    {
        $bundle = new EffectiveActivismSparQlClientBundle();
        $this->assertInstanceOf(SparQlClientExtension::class, $bundle->getContainerExtension());
    }

    public function testConfiguration()
    {
        $configuration = new Configuration();
        $tree = $configuration->getConfigTreeBuilder();
        $nodes = $tree->buildTree();
        $this->assertEquals('sparql_client', $nodes->getName());
    }

    public function testExtension()
    {
        $containerBuilderStub = $this->createMock(ContainerBuilder::class);
        $definition = new Definition(SparQlClientInterface::class, []);
        $containerBuilderStub->method('getDefinition')->willReturn($definition);
        $extension = new SparQlClientExtension();
        $extension->load([
            'sparql_client' => self::TEST_CONFIG
        ], $containerBuilderStub);
        $this->assertEquals([0 => self::TEST_CONFIG, 1 => self::TEST_CONFIG], $definition->getArguments());
        $this->assertEquals('sparql_client', $extension->getAlias());
    }
}
