<?php

namespace Ekyna\Component\Payum\Cybermut\Action;

use Ekyna\Component\Payum\Cybermut\Request\PaymentForm;
use Payum\Core\Model\Token;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;

/**
 * Class CaptureActionTest
 * @package Ekyna\Component\Payum\Cybermut
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CaptureActionTest extends AbstractActionTest
{
    protected $requestClass = Capture::class;

    protected $actionClass  = CaptureAction::class;


    /**
     * @test
     */
    public function should_not_set_urls_if_capture_passed_without_token()
    {
        $gatewayMock = $this->createGatewayMock();

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([]);
        $request->setModel([]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertArrayNotHasKey('return_url', $model);
        $this->assertArrayNotHasKey('success_url', $model);
        $this->assertArrayNotHasKey('failure_url', $model);
    }

    /**
     * @test
     */
    public function should_set_urls_if_capture_passed_with_token()
    {
        $expectedTargetUrl = 'theTargetUrl';

        $token = new Token();
        $token->setTargetUrl($expectedTargetUrl);
        $token->setDetails([]);

        $gatewayMock = $this->createGatewayMock();

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture($token);
        $request->setModel([]);

        $action->execute($request);

        $model = $request->getModel();

        $this->assertEquals($model['return_url'], $expectedTargetUrl);
        $this->assertEquals($model['success_url'], $expectedTargetUrl);
        $this->assertEquals($model['failure_url'], $expectedTargetUrl);
    }

    /**
     * @test
     */
    public function should_not_execute_api_request_if_urls_are_not_set()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class));

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([]);
        $request->setModel([]);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function should_not_execute_api_request_if_date_is_set()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class));

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([]);
        $request->setModel(['date' => 'is_set']);

        $action->execute($request);
    }

    /**
     * @test
     */
    public function should_execute_api_request_if_date_is_not_set_and_urls_are_set()
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(PaymentForm::class));

        $gatewayMock
            ->expects($this->at(1))
            ->method('execute')
            ->with($this->isInstanceOf(Sync::class));

        $action = new CaptureAction();
        $action->setGateway($gatewayMock);

        $request = new Capture([]);
        $request->setModel([
            'return_url'  => 'set',
            'success_url' => 'set',
            'failure_url' => 'set',
        ]);

        $action->execute($request);
    }
}
