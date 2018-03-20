<?php

namespace Ekyna\Component\Payum\Cybermut\Action;

use Ekyna\Component\Payum\Cybermut\Request\PaymentResponse;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Request\Sync;

/**
 * Class SyncActionTest
 * @package Ekyna\Component\Payum\Cybermut
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SyncActionTest extends AbstractActionTest
{
    protected $requestClass = Sync::class;

    protected $actionClass  = SyncAction::class;


    /**
     * @test
     */
    public function should_execute_api_response_with_same_model()
    {
        $expectedModel = new ArrayObject(['foo' => 'fooVal']);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf(PaymentResponse::class))
            ->will($this->returnCallback(function (PaymentResponse $request) use ($expectedModel) {
                $model = $request->getModel();

                $this->assertEquals($expectedModel, $model);
            }));

        $action = new SyncAction();
        $action->setGateway($gatewayMock);

        $action->execute(new Sync($expectedModel));
    }
}
