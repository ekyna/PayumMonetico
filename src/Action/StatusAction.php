<?php

namespace Ekyna\Component\Payum\Monetico\Action;

use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

/**
 * Class StatusAction
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatusAction implements ActionInterface
{
    /**
     * {@inheritdoc}
     *
     * @param GetStatusInterface $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if (false == $code = $model['code-retour']) {
            if (false != $code = $model['state_override']) {
                if ($code === 'canceled') {
                    $request->markCanceled();

                    return;
                }
            }

            $request->markNew();

            return;
        }

        switch ($code) {
            case "payetest" : // paiement accepté (en TEST uniquement)
            case "paiement" : // paiement accepté (en Production uniquement)
                $request->markCaptured();
                break;

            case "Annulation" : // contacter l’émetteur de carte
                $request->markFailed();
                break;

            default :
                $request->markUnknown();
        }

        if ($request->isCaptured() && false != $code = $model['state_override']) {
            if ($code === 'refunded') {
                $request->markRefunded();
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface
            && $request->getModel() instanceof \ArrayAccess;
    }
}
