<?php

namespace Ekyna\Component\Payum\Monetico\Api\Options;

use Ekyna\Component\Payum\Monetico\Api\Api;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigResolver
 * @package Ekyna\Component\Payum\Monetico\Api\Options
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ConfigResolver extends OptionsResolver
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this
            ->setDefaults([
                'mode'  => Api::MODE_TEST,
                'debug' => false,
            ])
            ->setRequired([
                'tpe',
                'key',
                'company',
            ])
            ->setAllowedTypes('mode', 'string')
            ->setAllowedTypes('tpe', 'string')
            ->setAllowedTypes('key', 'string')
            ->setAllowedTypes('company', 'string')
            ->setAllowedTypes('debug', 'bool')
            ->setAllowedValues('mode', [Api::MODE_TEST, Api::MODE_PRODUCTION]);
    }
}
