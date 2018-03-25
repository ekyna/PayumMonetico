<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Ekyna\Component\Payum\Monetico\Request\PaymentResponse;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Sync;

/**
 * Class SyncAction
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SyncAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @param Sync $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        //$model = ArrayObject::ensureArrayObject($request->getModel());

        $this->gateway->execute(new PaymentResponse($request->getModel()));
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof Sync
            && $request->getModel() instanceof \ArrayAccess;
    }
}
