<?php

namespace Ekyna\Component\Payum\Monetico\Api\Options;

use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RequestResolver
 * @package Ekyna\Component\Payum\Monetico\Api\Options
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class RequestResolver extends OptionsResolver
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this
            ->setRequired([
                'date',
                'amount',
                'currency',
                'reference',
                'context',
                'email',
                'success_url',
                'failure_url',
            ])
            ->setDefaults([
                'locale'                 => 'FR',
                'comment'                => null,
                'schedule'               => [],
                'aliascb'                => null,
                'forcesaisiecb'          => null,
                '3dsdebrayable'          => null,
                'ThreeDSecureChallenge'  => null,
                'libelleMonetique'       => null,
                'desactivemoyenpaiement' => null,
                'protocole'              => null,
            ]);

        $this
            ->setAllowedTypes('date', 'string')
            ->setAllowedValues('date', function ($value) {
                return preg_match('~^[0-9]{2}/[0-9]{2}/[0-9]{4}:[0-9]{2}:[0-9]{2}:[0-9]{2}$~', $value);
            })
            ->setAllowedTypes('amount', 'numeric')
            ->setAllowedValues('amount', function ($value) {
                return preg_match('~^[0-9]+(\.[0-9]{1,2})?$~', $value);
            })
            ->setAllowedValues('currency', Assert::currency())
            ->setAllowedTypes('reference', 'string')
            ->setAllowedValues('reference', function ($value) {
                return preg_match('~^[0-9A-Za-z]{1,12}$~', $value);
            });

        $this
            ->setAllowedTypes('context', 'array')
            ->setNormalizer('context', function (
                /** @noinspection PhpUnusedParameterInspection */
                Options $options,
                $value
            ) {
                return (new ContextResolver())->resolve($value);
            })
            ->setAllowedTypes('email', 'string')
            ->setAllowedValues('email', function ($value) {
                return !empty($value)
                    && preg_match('~^.+@.+\..+$~', $value)
                    && filter_var($value, FILTER_VALIDATE_EMAIL);
            })
            ->setAllowedTypes('success_url', 'string')
            ->setAllowedTypes('failure_url', 'string')
            ->setAllowedTypes('locale', 'string')
            ->setAllowedValues('locale', ['FR', 'EN', 'DE', 'IT', 'ES', 'NL', 'PT', 'JA', 'SV'])
            ->setAllowedValues('comment', Assert::string(3200));

        $this
            ->setAllowedTypes('schedule', 'array')
            ->setNormalizer('schedule', function (
                /** @noinspection PhpUnusedParameterInspection */
                Options $options,
                $value
            ) {
                $resolver = new ScheduleResolver();

                $schedule = [];
                foreach ($value as $data) {
                    $schedule[] = $resolver->resolve($data);
                }

                return $schedule;
            })
            ->setAllowedValues('schedule', function ($value) {
                return empty($value) || (1 < count($value));
            });

        $this
            ->setAllowedTypes('3dsdebrayable', ['null', 'int'])
            ->setAllowedValues('3dsdebrayable', [null, 0, 1])
            ->setAllowedTypes('ThreeDSecureChallenge', ['null', 'string'])
            ->setAllowedValues('ThreeDSecureChallenge', [
                null,
                'no_preference',
                'challenge_preferred',
                'challenge_mandated',
                'no_challenge_requested',
                'no_challenge_requested_strong_authentication',
                'no_challenge_requested_trusted_third_party',
                'no_challenge_requested_risk_analysis',
            ])
            ->setAllowedTypes('libelleMonetique', ['null', 'string'])
            ->setAllowedValues('libelleMonetique', function ($value) {
                return is_null($value) || preg_match('~^[A-Za-z0-9 ]{1,32}$~', $value);
            })
            ->setAllowedTypes('desactivemoyenpaiement', ['null', 'string'])
            ->setAllowedValues('desactivemoyenpaiement', [null, '1euro', '3xcb', '4xcb', 'paypal', 'lyfpay'])
            ->setAllowedTypes('aliascb', ['null', 'string'])
            ->setAllowedValues('aliascb', function ($value) {
                return is_null($value) || preg_match('~^[a-zA-Z0-9]{1,64}$~', $value);
            })
            ->setAllowedTypes('forcesaisiecb', ['null', 'int'])
            ->setAllowedValues('forcesaisiecb', [null, 0, 1])
            ->setAllowedTypes('protocole', ['null', 'string'])
            ->setAllowedValues('protocole', [null, '1euro', '3xcb', '4xcb', 'paypal', 'lyfpay']);
    }
}
