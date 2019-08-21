<?php

namespace Ekyna\Component\Payum\Monetico\Action\Api;

use Ekyna\Component\Payum\Monetico\Request\PaymentForm;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\RenderTemplate;
use ArrayAccess;

/**
 * Class PaymentFormAction
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class PaymentFormAction extends AbstractApiAction
{
    /**
     * @var string
     */
    protected $templateName;


    /**
     * @param string|null $templateName
     */
    public function __construct($templateName)
    {
        $this->templateName = $templateName;
    }

    /**
     * @inheritdoc
     *
     * @throws HttpResponse
     */
    public function execute($request)
    {
        /** @var PaymentForm $request */
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model['date'] = date('d/m/Y:H:i:s');

        $request->setModel($model);

        $data = $model->getArrayCopy();

        $this->logRequestData($data);

        $form = $this->api->createPaymentForm($data);

        $renderTemplate = new RenderTemplate($this->templateName, $form);

        $this->gateway->execute($renderTemplate);

        throw new HttpResponse($renderTemplate->getResult());
    }

    /**
     * Logs the request data.
     *
     * @param array $data
     */
    private function logRequestData(array $data)
    {
        $this->logData("[Monetico] Request", $data, [
            'trans_id',
            'reference',
            'amount',
            'currency',
            'date',
            'email',
            'comment',
        ]);
    }

    /**
     * @inheritdoc
     */
    public function supports($request)
    {
        return $request instanceof PaymentForm
            && $request->getModel() instanceof ArrayAccess;
    }
}
