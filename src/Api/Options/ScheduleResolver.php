<?php

namespace Ekyna\Component\Payum\Monetico\Api\Options;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ScheduleResolver
 * @package Ekyna\Component\Payum\Monetico\Api\Options
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ScheduleResolver extends OptionsResolver
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this
            ->setRequired(['date', 'amount'])
            ->setAllowedTypes('date', 'string')
            ->setAllowedTypes('amount', 'numeric')
            ->setAllowedValues('date', function ($value) {
                return preg_match('~^[0-9]{2}/[0-9]{2}/[0-9]{4}$~', $value);
            })
            ->setAllowedValues('amount', function ($value) {
                return preg_match('~^[0-9]+(\.[0-9]{2,3})?$~', $value);
            });
    }
}
