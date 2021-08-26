<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Ekyna\Component\Payum\Monetico\Api\Api;
use Payum\Core\GatewayInterface;
use Payum\Core\Security\TokenInterface;
use Payum\Core\Tests\GenericActionTest;
use PHPUnit\Framework\MockObject\MockObject;

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
     * @return MockObject&GatewayInterface
     */
    protected function createGatewayMock()
    {
        return $this->getMockBuilder(GatewayInterface::class)->getMock();
    }

    /**
     * @return MockObject&TokenInterface
     */
    protected function createTokenMock()
    {
        return $this->getMockBuilder(TokenInterface::class)->getMock();
    }

    /**
     * @return MockObject&Api
     */
    protected function createApiMock()
    {
        return $this->getMockBuilder(Api::class)->getMock();
    }
}