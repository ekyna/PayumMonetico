<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Ekyna\Component\Payum\Monetico\Request\PaymentForm;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Capture;
use Payum\Core\Request\Sync;

/**
 * Class CaptureAction
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Capture $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($request->getToken()) {
            // Done redirection urls
            $targetUrl = $request->getToken()->getTargetUrl();
            foreach (['success_url', 'failure_url'] as $field) {
                if (false == $model[$field]) {
                    $model[$field] = $targetUrl;
                }
            }

            // Notify url is unique for all payment.
            // Create a dedicated controller to handle notification.
        }

        if (false == $model['date'] && ($model['success_url'] && $model['failure_url'])) {
            $this->gateway->execute(new PaymentForm($model));
        }

        $this->gateway->execute(new Sync($model));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof Capture
            && $request->getModel() instanceof \ArrayAccess;
    }
}
