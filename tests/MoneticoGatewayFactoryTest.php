<?php

namespace Ekyna\Component\Payum\Monetico;

use Ekyna\Component\Payum\Monetico\Api\Api;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Class MoneticoGatewayFactoryTest
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MoneticoGatewayFactoryTest extends TestCase
{
    public function test_extends_GatewayFactory()
    {
        $rc = new ReflectionClass(MoneticoGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactory::class));
    }

    public function test_construct_without_any_arguments()
    {
        new MoneticoGatewayFactory();

        $this->assertTrue(true);
    }

    public function test_core_factory_created_if_not_passed()
    {
        $factory = new MoneticoGatewayFactory();

        $this->assertAttributeInstanceOf(CoreGatewayFactory::class, 'coreGatewayFactory', $factory);
    }

    public function test_core_factory_used_if_passed()
    {
        $coreFactory = $this->createMock('Payum\Core\GatewayFactoryInterface');

        $moneticoFactory = new MoneticoGatewayFactory([], $coreFactory);

        $this->assertAttributeSame($coreFactory, 'coreGatewayFactory', $moneticoFactory);
    }

    public function test_create_gateway()
    {
        $factory = new MoneticoGatewayFactory();

        $gateway = $factory->create([
            'mode'    => Api::MODE_PRODUCTION,
            'tpe'     => '123456',
            'key'     => '123456',
            'company' => 'foobar',
        ]);

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');

        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    public function test_create_config()
    {
        $factory = new MoneticoGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    public function test_config_defaults_passed_in_constructor()
    {
        $factory = new MoneticoGatewayFactory([
            'foo' => 'fooVal',
            'bar' => 'barVal',
        ]);

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('foo', $config);
        $this->assertEquals('fooVal', $config['foo']);

        $this->assertArrayHasKey('bar', $config);
        $this->assertEquals('barVal', $config['bar']);
    }

    public function test_config_contains_factory_name_and_title()
    {
        $factory = new MoneticoGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('monetico', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Monetico', $config['payum.factory_title']);
    }

    public function test_throw_if_required_options_not_passed()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The mode, tpe, key, company fields are required.');

        $factory = new MoneticoGatewayFactory();

        $factory->create();
    }

    public function test_configure_paths()
    {
        $factory = new MoneticoGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);

        $this->assertInternalType('array', $config['payum.paths']);
        $this->assertNotEmpty($config['payum.paths']);

        $this->assertArrayHasKey('PayumCore', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['PayumCore']);
        $this->assertTrue(file_exists($config['payum.paths']['PayumCore']));

        $this->assertArrayHasKey('EkynaPayumMonetico', $config['payum.paths']);
        $this->assertStringEndsWith('Resources/views', $config['payum.paths']['EkynaPayumMonetico']);
        $this->assertTrue(file_exists($config['payum.paths']['EkynaPayumMonetico']));
    }
}
