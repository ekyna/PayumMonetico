<?php

namespace Ekyna\Component\Payum\Monetico\Action\Api;

use Ekyna\Component\Payum\Monetico\Action\AbstractActionTest;
use Ekyna\Component\Payum\Monetico\Request\PaymentForm;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;

/**
 * Class PaymentFormActionTest
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentFormActionTest extends AbstractActionTest
{
    protected $requestClass = PaymentForm::class;

    protected $actionClass = PaymentFormAction::class;

    protected function setUp(): void
    {
        $this->action = new $this->actionClass('template');
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        // TODO Remove this test...
        //$this->markTestSkipped();
        new $this->actionClass('template');
    }

    /**
     * @test
     */
    public function should_call_api_create_payment_form_and_execute_render_template()
    {
        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->at(0))
            ->method('createPaymentForm')
            ->with($this->isType('array'))
            ->willReturn([]);

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(RenderTemplate::class));

        $action = new PaymentFormAction('template');
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new PaymentForm([]);
        $request->setModel(['some' => 'data']);

        $this->expectException(HttpResponse::class);

        $action->execute($request);
    }
}
