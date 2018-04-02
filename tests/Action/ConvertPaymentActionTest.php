<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Payum\Core\Model\Payment;
use Payum\Core\Request\Convert;
use Payum\Core\Request\Generic;
use Payum\Core\Request\GetCurrency;

/**
 * Class ConvertPaymentActionTest
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentActionTest extends AbstractActionTest
{
    protected $requestClass = Convert::class;

    protected $actionClass  = ConvertPaymentAction::class;


    /**
     * @test
     *
     * @dataProvider providePaymentsAndAmounts
     */
    public function should_execute_currency_request_and_set_amount_if_not_set($payment, $amount)
    {
        $gatewayMock = $this->createGatewayMock();
        $gatewayMock
            ->expects($this->at(0))
            ->method('execute')
            ->with($this->isInstanceOf(GetCurrency::class))
            ->will($this->returnCallback(function (GetCurrency $request) {
                $request->alpha3 = $request->code;

                switch ($request->code) {
                    case 'ISK':
                        $request->exp = 0;
                        break;
                    case 'EUR':
                        $request->exp = 2;
                        break;
                    case 'TND':
                        $request->exp = 3;
                        break;
                }
            }));

        $action = new ConvertPaymentAction();
        $action->setGateway($gatewayMock);

        $request = new Convert($payment, 'array');

        $action->execute($request);

        $result = $request->getResult();

        $this->assertSame($amount, $result['amount']);
    }

    public function provideSupportedRequests()
    {
        return [
            [new Convert(new Payment(), 'array')],
        ];
    }

    public function provideNotSupportedRequests()
    {
        return [
            ['foo'],
            [['foo']],
            [new \stdClass()],
            [new Convert('foo', 'bar')],
            [$this->getMockForAbstractClass(Generic::class, [[]])],
        ];
    }

    public function providePaymentsAndAmounts()
    {
        $p1 = new Payment();
        $p1->setNumber('TEST');
        $p1->setTotalAmount(123450);
        $p1->setCurrencyCode('EUR');
        $p1->setClientId(1);
        $p1->setClientEmail('test@example.org');

        $p2 = clone $p1;
        $p2->setTotalAmount(123450);
        $p2->setCurrencyCode('ISK');

        $p3 = clone $p1;
        $p3->setTotalAmount(123450);
        $p3->setCurrencyCode('TND');

        return [
            [$p1, '1234.50'],
            [$p2, '123450'],
            [$p3, '123.450'],
        ];
    }
}
