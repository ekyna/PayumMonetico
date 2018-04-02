<?php

namespace Ekyna\Component\Payum\Monetico\Api;

use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RuntimeException;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Api
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Api
{
    const BANK_CM  = 'CM';
    const BANK_CIC = 'CIC';
    const BANK_OBC = 'OBC';

    const TYPE_PAYMENT = 'payment';
    const TYPE_CANCEL  = 'cancel';
    const TYPE_CAPTURE = 'capture';
    const TYPE_REFUND  = 'refund';

    const MODE_TEST       = 'TEST';
    const MODE_PRODUCTION = 'PRODUCTION';

    const NOTIFY_SUCCESS = "version=2\ncdr=0\n";
    const NOTIFY_FAILURE = "version=2\ncdr=1\n";

    const VERSION = '3.0';

    /**
     * @var OptionsResolver
     */
    private $configResolver;

    /**
     * @var OptionsResolver
     */
    private $requestOptionsResolver;

    /**
     * @var array
     */
    private $config;


    /**
     * Configures the api.
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        try {
            $this->config = $this
                ->getConfigResolver()
                ->resolve($config);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Creates the request form.
     *
     * @param array $data
     *
     * @return array
     */
    public function createPaymentForm(array $data)
    {
        $this->ensureApiIsConfigured();

        try {
            $data = $this
                ->getRequestOptionsResolver()
                ->resolve($data);
        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException($e->getMessage(), $e->getCode(), $e);
        }

        $fields = [
            'TPE'         => $this->config['tpe'],
            'date'        => $data['date'],
            'montant'     => $data['amount'] . $data['currency'],
            'reference'   => $data['reference'],
            'texte-libre' => $data['comment'],
            'version'     => static::VERSION,
            'lgue'        => $data['locale'],
            'societe'     => $this->config['company'],
            'mail'        => $data['email'],
        ];

        $macData = array_values($fields);

        $fields['texte-libre'] = $this->htmlEncode($data['comment']);

        if (!empty($data['schedule'])) {
            $macData[] = $fields['nbrech'] = count($data['schedule']);

            $count = 0;
            foreach ($data['schedule'] as $datum) {
                $count++;
                $macData[] = $fields['dateech' . $count] = $datum['date'];
                $macData[] = $fields['montantech' . $count] = $datum['amount'] . $data['currency'];
            }

            // Fills empty schedule
            for ($i = 2 * $count + 10; $i < 18; $i++) {
                $macData[] = null;
            }

            $options = [];
            foreach ($data['options'] as $key => $value) {
                $options = "$key=$value";
            }
            $macData[] = $fields['options'] = implode('&', $options);
        }

        $fields['MAC'] = $this->computeMac($macData);

        $fields['url_retour'] = $data['return_url'];
        $fields['url_retour_ok'] = $data['success_url'];
        $fields['url_retour_err'] = $data['failure_url'];

        return [
            'action' => $this->getEndpointUrl(static::TYPE_PAYMENT),
            'method' => 'POST',
            'fields' => $fields,
        ];
    }

    /**
     * Checks the payment response integrity.
     *
     * @param array $data
     *
     * @return bool
     */
    public function checkPaymentResponse(array $data)
    {
        if (!isset($data['MAC'])) {
            return false;
        }

        $data = array_replace([
            'date'        => null,
            'montant'     => null,
            'reference'   => null,
            'texte-libre' => null,
            'code-retour' => null,
            'cvx'         => null,
            'vld'         => null,
            'brand'       => null,
            'status3ds'   => null,
            'numauto'     => null,
            'motifrefus'  => null,
            'originecb'   => null,
            'bincb'       => null,
            'hpancb'      => null,
            'ipclient'    => null,
            'originetr'   => null,
            'veres'       => null,
            'pares'       => null,
        ], $data);

        $macData = [
            $this->config['tpe'],
            $data["date"],
            $data['montant'],
            $data['reference'],
            $data['texte-libre'],
            static::VERSION,
            $data['code-retour'],
            $data['cvx'],
            $data['vld'],
            $data['brand'],
            $data['status3ds'],
            $data['numauto'],
            $data['motifrefus'],
            $data['originecb'],
            $data['bincb'],
            $data['hpancb'],
            $data['ipclient'],
            $data['originetr'],
            $data['veres'],
            $data['pares'],
            null,
        ];

        return strtolower($data['MAC']) === $this->computeMac($macData);
    }

    /**
     * Generates the signature.
     *
     * @param array $data
     *
     * @return string
     */
    public function computeMac(array $data)
    {
        for ($i = count($data); $i < 19; $i++) {
            $data[$i] = null;
        }

        return strtolower(hash_hmac("sha1", implode('*', array_values($data)), $this->getMacKey()));
    }

    /**
     * Returns the key formatted for mac generation.
     *
     * @return string
     */
    public function getMacKey()
    {
        $key = $this->config['key'];

        $hexStrKey = substr($key, 0, 38);
        $hexFinal = "" . substr($key, 38, 2) . "00";

        $cca0 = ord($hexFinal);

        if ($cca0 > 70 && $cca0 < 97) {
            $hexStrKey .= chr($cca0 - 23) . substr($hexFinal, 1, 1);
        } else {
            if (substr($hexFinal, 1, 1) == "M") {
                $hexStrKey .= substr($hexFinal, 0, 1) . "0";
            } else {
                $hexStrKey .= substr($hexFinal, 0, 2);
            }
        }

        return pack("H*", $hexStrKey);
    }

    /**
     * Check that the API has been configured.
     *
     * @throws LogicException
     */
    private function ensureApiIsConfigured()
    {
        if (null === $this->config) {
            throw new LogicException('You must first configure the API.');
        }
    }

    /**
     * Encode html string.
     *
     * @param string $data
     *
     * @return string
     */
    private function htmlEncode($data)
    {
        if (empty($data)) {
            return null;
        }

        $safeChars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890._-";

        $result = "";
        for ($i = 0; $i < strlen($data); $i++) {
            if (strstr($safeChars, $data[$i])) {
                $result .= $data[$i];
            } else if ("7F" >= $var = bin2hex(substr($data, $i, 1))) {
                $result .= "&#x" . $var . ";";
            } else {
                $result .= $data[$i];
            }
        }

        return $result;
    }

    /**
     * Returns the endpoint url.
     *
     * @param string $type
     *
     * @return string
     */
    private function getEndpointUrl($type)
    {
        switch ($this->config['bank']) {
            case static::BANK_CM:
                $host = 'https://paiement.creditmutuel.fr/';
                break;
            case static::BANK_CIC:
                $host = 'https://ssl.paiement.cic-banques.fr/';
                break;
            case static::BANK_OBC:
                $host = 'https://ssl.paiement.banque-obc.fr/';
                break;
            default:
                throw new RuntimeException('Failed to determine the endpoint host.');
        }

        switch ($type) {
            case static::TYPE_PAYMENT:
                $path = 'paiement.cgi';
                break;
            case static::BANK_CIC:
                $path = 'capture_paiement.cgi';
                break;
            case static::BANK_OBC:
                $path = 'recredit_paiement.cgi';
                break;
            default:
                throw new RuntimeException('Failed to determine the endpoint path.');
        }

        if ($this->config['mode'] === static::MODE_TEST) {
            $path = 'test/' . $path;
        }

        return $host . $path;
    }

    /**
     * Returns the config option resolver.
     *
     * @return OptionsResolver
     */
    private function getConfigResolver()
    {
        if (null !== $this->configResolver) {
            return $this->configResolver;
        }

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'mode'  => static::MODE_TEST,
                'debug' => false,
            ])
            ->setRequired([
                'bank',
                'tpe',
                'key',
                'company',
            ])
            ->setAllowedTypes('bank', 'string')
            ->setAllowedTypes('mode', 'string')
            ->setAllowedTypes('tpe', 'string')
            ->setAllowedTypes('key', 'string')
            ->setAllowedTypes('company', 'string')
            ->setAllowedTypes('debug', 'bool')
            ->setAllowedValues('bank', [static::BANK_CM, static::BANK_CIC, static::BANK_OBC])
            ->setAllowedValues('mode', [static::MODE_TEST, static::MODE_PRODUCTION]);

        return $this->configResolver = $resolver;
    }

    /**
     * Returns request options resolver.
     *
     * @return OptionsResolver
     */
    private function getRequestOptionsResolver()
    {
        if (null !== $this->requestOptionsResolver) {
            return $this->requestOptionsResolver;
        }

        $amountValidator = function ($value) {
            return preg_match('~^[0-9]+(\.[0-9]{2,3})?$~', $value);
        };

        $scheduleResolver = new OptionsResolver();
        $scheduleResolver
            ->setRequired(['date', 'amount'])
            ->setAllowedTypes('date', 'string')
            ->setAllowedTypes('amount', 'numeric')
            ->setAllowedValues('date', function ($value) {
                return preg_match('~^[0-9]{2}/[0-9]{2}/[0-9]{4}$~', $value);
            })
            ->setAllowedValues('amount', $amountValidator);

        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'reference',
                'date',
                'amount',
                'currency',
                'email',
                'return_url',
                'success_url',
                'failure_url',
            ])
            ->setDefaults([
                'locale'   => 'FR',
                'comment'  => null,
                'schedule' => [],
                'options'  => [],
            ])
            ->setAllowedTypes('reference', 'string')
            ->setAllowedTypes('date', 'string')
            ->setAllowedTypes('amount', 'numeric')
            ->setAllowedTypes('currency', 'string')
            ->setAllowedTypes('email', 'string')
            ->setAllowedTypes('return_url', 'string')
            ->setAllowedTypes('success_url', 'string')
            ->setAllowedTypes('failure_url', 'string')
            ->setAllowedTypes('comment', 'string')
            ->setAllowedTypes('schedule', 'array')
            ->setAllowedTypes('options', 'array')
            ->setAllowedValues('date', function ($value) {
                return preg_match('~^[0-9]{2}/[0-9]{2}/[0-9]{4}:[0-9]{2}:[0-9]{2}:[0-9]{2}$~', $value);
            })
            ->setAllowedValues('amount', $amountValidator)
            ->setAllowedValues('locale', ['FR', 'EN', 'DE', 'IT', 'ES', 'NL', 'PT'])
            ->setNormalizer('schedule', function (
                /** @noinspection PhpUnusedParameterInspection */
                Options $options, $value
            ) use ($scheduleResolver) {
                $schedule = [];

                foreach ($value as $data) {
                    $schedule[] = $scheduleResolver->resolve($data);
                }

                return $schedule;
            })
            ->setAllowedValues('schedule', function ($value) {
                return empty($value) || (1 < count($value));
            });

        return $this->requestOptionsResolver = $resolver;
    }
}
