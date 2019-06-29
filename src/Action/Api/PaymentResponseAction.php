<?php

namespace Ekyna\Component\Payum\Monetico\Action\Api;

use Ekyna\Component\Payum\Monetico\Request\PaymentResponse;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetHttpRequest;

/**
 * Class PaymentResponseAction
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentResponseAction extends AbstractApiAction
{
    /**
     * @inheritdoc
     */
    public function execute($request)
    {
        /** @var PaymentResponse $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute($httpRequest = new GetHttpRequest());

        if (isset($httpRequest->request['code-retour'])) {
            $data = $httpRequest->request;
        } elseif (isset($httpRequest->query['code-retour'])) {
            $data = $httpRequest->query;
        } else {
            return;
        }

        $this->logResponseData($data);

        // Check amount
        if ($model['amount'] . $model['currency'] != $data['montant']) {
            return;
        }

        // Check the response signature
        if ($this->api->checkPaymentResponse($data)) {
            // Update the payment details
            $model->replace($data); // TODO do not store all data
            $model['valid_mac'] = 1;
        } else {
            $model['valid_mac'] = 0;
        }

        $request->setModel($model);
    }

    /**
     * Logs the response data.
     *
     * @param array $data
     */
    private function logResponseData(array $data)
    {
        $this->logData("[Monetico] Response", $data, [
            'MAC',
            'tpe',
            'date',
            'montant',
            'reference',
            'texte-libre',
            'code-retour',
            'cvx',
            'vld',
            'brand',
            'status3ds',
            'numauto',
            'motifrefus',
            'originecb',
            'bincb',
            'hpancb',
            'ipclient',
            'originetr',
            'veres',
            'pares',
        ]);
    }

    /**
     * @inheritdec
     */
    public function supports($request)
    {
        return $request instanceof PaymentResponse
            && $request->getModel() instanceof \ArrayAccess;
    }
}
