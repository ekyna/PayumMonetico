<?php

namespace Ekyna\Component\Payum\Cybermut\Action;

use Payum\Core\Request\GetHumanStatus;

/**
 * Class StatusActionTest
 * @package Ekyna\Component\Payum\Cybermut
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class StatusActionTest extends AbstractActionTest
{
    protected $requestClass = GetHumanStatus::class;

    protected $actionClass  = StatusAction::class;


    /**
     * @test
     */
    public function should_not_support_anything_not_status_request()
    {
        $action = new StatusAction();
        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throw_if_not_supported_request()
    {
        $action = new StatusAction();
        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function should_mark_new_if_code_is_not_set()
    {
        $action = new StatusAction();
        $request = new GetHumanStatus([]);

        $action->execute($request);

        $this->assertTrue($request->isNew());
    }

    /**
     * @test
     */
    public function should_mark_captured_if_code_is_paiement()
    {
        $action = new StatusAction();
        $request = new GetHumanStatus([
            'code-retour' => 'paiement'
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function should_mark_captured_if_code_is_payetest()
    {
        $action = new StatusAction();
        $request = new GetHumanStatus([
            'code-retour' => 'payetest'
        ]);

        $action->execute($request);

        $this->assertTrue($request->isCaptured());
    }

    /**
     * @test
     */
    public function should_mark_failed_if_code_is_Annulation()
    {
        $action = new StatusAction();
        $request = new GetHumanStatus([
            'code-retour' => 'Annulation'
        ]);

        $action->execute($request);

        $this->assertTrue($request->isFailed());
    }

    /**
     * @test
     */
    public function should_mark_unknown_if_unexpected_code()
    {
        $action = new StatusAction();
        $request = new GetHumanStatus([
            'code-retour' => 'abracadabra'
        ]);

        $action->execute($request);

        $this->assertTrue($request->isUnknown());
    }
}
