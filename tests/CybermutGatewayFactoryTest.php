<?php

namespace Ekyna\Component\Payum\Cybermut;

use Ekyna\Component\Payum\Cybermut\Api\Api;
use Payum\Core\CoreGatewayFactory;
use Payum\Core\Exception\LogicException;
use Payum\Core\GatewayFactory;

/**
 * Class CybermutGatewayFactoryTest
 * @package Ekyna\Component\Payum\Cybermut
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CybermutGatewayFactoryTest extends TestCase
{
    public function test_extends_GatewayFactory()
    {
        $rc = new \ReflectionClass(CybermutGatewayFactory::class);

        $this->assertTrue($rc->isSubclassOf(GatewayFactory::class));
    }

    public function test_construct_without_any_arguments()
    {
        new CybermutGatewayFactory();

        $this->assertTrue(true);
    }

    public function test_core_factory_created_if_not_passed()
    {
        $factory = new CybermutGatewayFactory();

        $this->assertAttributeInstanceOf(CoreGatewayFactory::class, 'coreGatewayFactory', $factory);
    }

    public function test_core_factory_used_if_passed()
    {
        $coreFactory = $this->createMock('Payum\Core\GatewayFactoryInterface');

        $cybermutFactory = new CybermutGatewayFactory([], $coreFactory);

        $this->assertAttributeSame($coreFactory, 'coreGatewayFactory', $cybermutFactory);
    }

    public function test_create_gateway()
    {
        $factory = new CybermutGatewayFactory();

        $gateway = $factory->create([
            'bank'      => Api::BANK_CM,
            'mode'      => Api::MODE_PRODUCTION,
            'tpe'       => '123456',
            'key'       => '123456',
            'company'   => 'foobar',
            'directory' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'EkynaPayumCybermut',
        ]);

        $this->assertInstanceOf('Payum\Core\Gateway', $gateway);
        $this->assertAttributeNotEmpty('apis', $gateway);
        $this->assertAttributeNotEmpty('actions', $gateway);

        $extensions = $this->readAttribute($gateway, 'extensions');

        $this->assertAttributeNotEmpty('extensions', $extensions);
    }

    public function test_create_config()
    {
        $factory = new CybermutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);
        $this->assertNotEmpty($config);
    }

    public function test_config_defaults_passed_in_constructor()
    {
        $factory = new CybermutGatewayFactory([
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
        $factory = new CybermutGatewayFactory();

        $config = $factory->createConfig();

        $this->assertInternalType('array', $config);

        $this->assertArrayHasKey('payum.factory_name', $config);
        $this->assertEquals('cybermut', $config['payum.factory_name']);

        $this->assertArrayHasKey('payum.factory_title', $config);
        $this->assertEquals('Cybermut', $config['payum.factory_title']);
    }

    public function test_throw_if_required_options_not_passed()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('The tpe, key, company, directory fields are required.');

        $factory = new CybermutGatewayFactory();

        $factory->create();
    }
}
