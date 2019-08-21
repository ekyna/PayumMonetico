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
        throw new RequestNotSupportedException('Do not use, this is a sample.');

        RequestNotSupportedException::assertSupports($this, $request);

        /** @var PaymentInterface $payment */
        $payment = $request->getSource();

        $model = ArrayObject::ensureArrayObject($payment->getDetails());

        if (false == $model['reference']) {
            $model['reference'] = $payment->getNumber();
        }
        if (false == $model['amount']) {
            $this->gateway->execute($currency = new GetCurrency($payment->getCurrencyCode()));
            $amount = (string)$payment->getTotalAmount();

            if (0 < $currency->exp) {
                $divisor = pow(10, $currency->exp);

                $amount = (string)round($amount / $divisor, $currency->exp);
                if (false !== $pos = strpos($amount, '.')) {
                    $amount = str_pad($amount, $pos + 1 + $currency->exp, '0', STR_PAD_RIGHT);
                }
            }

            $model['amount'] = $amount;
            $model['currency'] = (string)strtoupper($currency->code);
        }
        if (false == $model['email']) {
            $model['email'] = $payment->getClientEmail();
        }
        if (false == $model['comment']) {
            $model['comment'] = 'Customer: ' . $payment->getClientId();
        }

        // The 3DSecure v2 require that you provide the order context.
        // @see https://www.monetico-paiement.fr/fr/info/documentations/Monetico_Paiement_documentation_technique_v2.1.pdf (page 73)
        /* TODO $address = ...;
        $model['context'] = [
            'billing' => [
                'addressLine1' => $address->getStreet(),
                'city'         => $address->getCity(),
                'postalCode'   => $address->getPostalCode(),
                'country'      => $address->getCountry(),
            ]
        ];*/

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
