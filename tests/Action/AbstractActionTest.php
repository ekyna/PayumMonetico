<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Ekyna\Component\Payum\Monetico\Api\Api;
use Payum\Core\GatewayInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;

/**
 * Class AbstractActionTest
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractActionTest extends GenericActionTest
{
    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        parent::couldBeConstructedWithoutAnyArguments();

        $this->assertTrue(true);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMockBuilder(GatewayInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->getMockBuilder(TokenInterface::class)->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Api
     */
    protected function createApiMock()
    {
        return $this->getMockBuilder(Api::class)->getMock();
    }
}