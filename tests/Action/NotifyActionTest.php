<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Notify;
use Payum\Core\Request\Sync;

/**
 * Class NotifyActionTest
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class NotifyActionTest extends AbstractActionTest
{
    protected $requestClass = Notify::class;

    protected $actionClass = NotifyAction::class;


    /**
     * @test
     */
    public function should_execute_sync_with_same_model()
    {
        $expectedModel = new ArrayObject(['foo' => 'fooVal']);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class))
            ->will($this->returnCallback(function (Sync $request) use ($expectedModel) {
                $model = $request->getModel();

                $this->assertEquals($expectedModel, $model);
            }));

        $action = new NotifyAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Notify($expectedModel));
    }
}
