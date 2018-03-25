<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Model\PaymentInterface;
use Payum\Core\Request\Convert;
use Payum\Core\Request\GetCurrency;

/**
 * Class ConvertPaymentAction
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConvertPaymentAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Convert $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false == $model['reference']) {
            $model['reference'] = $payment->getNumber();
        }
        if (false == $model['amount']) {
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));

            $model['amount'] = (string)round($payment->getTotalAmount(), $currency->exp);
            $model['currency'] = (string)strtoupper($currency->code);
        }
        if (false == $model['email']) {
            $model['email'] = $payment->getClientEmail();
        }
        if (false == $model['comment']) {
            $model['comment'] = 'Customer: ' . $payment->getClientId();
        }

        $request->setResult((array)$model);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Convert
            && $request->getSource() instanceof PaymentInterface
            && $request->getTo() == 'array';
    }
}
