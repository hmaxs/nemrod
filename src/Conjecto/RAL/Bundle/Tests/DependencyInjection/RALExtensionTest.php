<?php
namespace Conjecto\RAL\Bundle\Tests\DependencyInjection;

use Conjecto\RAL\Bundle\DependencyInjection\RALExtension;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class RALExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var  ContainerBuilder */
    private $container;

    /** @var  Extension */
    private $extension;

    private $minConfig = array(/*'kernel.bundles'=>array()*/);

    protected function setUp()
    {
        //have to manually register annotation
        AnnotationRegistry::registerFile(__DIR__.'/../../../ResourceManager/Annotation/Rdf/Resource.php');
        $this->container = new ContainerBuilder();
        $this->container->setParameter('kernel.bundles', array('Conjecto\RAL\Bundle\Tests\Fixtures\TestBundle\FixtureTestBundle'));
        $this->container->setParameter('kernel.root_dir', __DIR__."../../../");
        $this->extension = new RALExtension();
        $this->container->registerExtension($this->extension);

        $this->load(array( array('endpoints' => array('foopoint' => 'http://bar.org/sparql'), "default_endpoint" => 'foopoint', 'namespaces' => array('foo' => 'http://www.example.org/foo#'),'elasticsearch' => array())));

    }

    protected function load($config)
    {
        $this->extension->load($config, $this->container);
    }

    public function testExtensionRegistersNamespaces()
    {
        $service = $this->getServiceDefinition('ral.namespace_registry');
        $calls = $service->getMethodCalls();

        $this->assertEquals(array("set", array('foo','http://www.example.org/foo#')), $calls[0]);
    }

    public function testExtensionRegisterClient()
    {
        $service = $this->getServiceDefinition('ral.namespace_registry');
        $calls = $service->getMethodCalls();

        $this->assertTrue($this->containerHasDefinition('ral.sparql.connection.foopoint'));
        $service = $this->getServiceDefinition('ral.sparql.connection.foopoint');
        $service->getMethodCalls();
    }

    public function testExtensionRegisterManager()
    {
        $this->assertTrue($this->containerHasAlias('rm'));
        $this->assertTrue($this->containerHasDefinition('ral.resource_manager.foopoint'));
    }

    public function testExtensionRegisterResourceMapping()
    {
        $service = $this->getServiceDefinition('ral.type_mapper');
        $calls = $service->getMethodCalls();

        $this->assertEquals(array("set",array('foo:Class','Conjecto\RAL\Bundle\Tests\Fixtures\TestBundle\RdfResource\TestResource')),$calls[0]);
    }


    /**
     * Load namespaces in namespace registry
     */
    public function testNamespaces()
    {
        $configs = array(
            array(
                'namespaces' => array(
                    'foo'    => 'http://purl.org/ontology/foo/',
                    'bar'    => 'http://www.w3.org/ns/bar#'
                ),
                'elasticsearch' => array()
            )
        );

        $extension = new RALExtension();
        $extension->load($configs, $this->container);
        $definition = $this->container->getDefinition('ral.namespace_registry');

        $this->assertEquals(array(
            array("set", array("foo", 'http://purl.org/ontology/foo/')),
            array("set", array("bar", 'http://www.w3.org/ns/bar#')),
        ), $definition->getMethodCalls());
    }

    public function testResourceMapping()
    {

    }

    protected function tearDown()
    {
        $this->container = null;
    }

    protected function getServiceDefinition($id)
    {
        return $this->container->getDefinition($id);
    }

    protected function containerHasDefinition($id)
    {
        return $this->container->hasDefinition($id);
    }

    protected function containerHasAlias($id)
    {
        return $this->container->hasAlias($id);
    }
}