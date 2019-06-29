<?php

namespace Ekyna\Component\Payum\Monetico\Action\Api;

use Ekyna\Component\Payum\Monetico\Action\AbstractActionTest;
use Ekyna\Component\Payum\Monetico\Request\PaymentResponse;
use Payum\Core\Request\GetHttpRequest;


/**
 * Class PaymentResponseActionTest
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentResponseActionTest extends AbstractActionTest
{
    protected $requestClass = PaymentResponse::class;

    protected $actionClass = PaymentResponseAction::class;

    /**
     * @test
     */
    public function should_not_execute_api_check_payment_if_code_retour_not_set_in_request()
    {
        $httpRequest = new GetHttpRequest();

        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($httpRequest);

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('checkPaymentResponse')
            ->with($this->isType('array'));

        $action = new PaymentResponseAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new PaymentResponse([]);
        $request->setModel([]);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function should_not_execute_api_check_payment_if_amount_do_not_equal()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->query['code-retour'] = 'payment';
                $request->query['montant'] = 1;
            }));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->never())
            ->method('checkPaymentResponse')
            ->with($this->isType('array'));

        $action = new PaymentResponseAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new PaymentResponse([]);
        $request->setModel(['amount' => 2]);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function should_execute_api_check_payment_if_code_retour_is_set_in_query_and_amount_equals()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->query['code-retour'] = 'payment';
                $request->query['montant'] = '12.34EUR';
            }));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->at(0))
            ->method('checkPaymentResponse')
            ->with($this->isType('array'));

        $action = new PaymentResponseAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new PaymentResponse([]);
        $request->setModel(['amount' => '12.34', 'currency' => 'EUR']);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function should_execute_api_check_payment_if_code_retour_is_set_in_request_and_amount_equals()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetHttpRequest::class))
            ->will($this->returnCallback(function (GetHttpRequest $request) {
                $request->request['code-retour'] = 'payment';
                $request->request['montant'] = 1;
            }));

        $apiMock = $this->createApiMock();
        $apiMock
            ->expects($this->at(0))
            ->method('checkPaymentResponse')
            ->with($this->isType('array'));

        $action = new PaymentResponseAction();
        $action->setGateway($gatewayMock);
        $action->setApi($apiMock);

        $request = new PaymentResponse([]);
        $request->setModel(['amount' => 1]);

        $action->execute($request);
    }
}
