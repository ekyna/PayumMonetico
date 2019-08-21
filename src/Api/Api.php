<?php

namespace Ekyna\Component\Payum\Monetico\Api;

use Ekyna\Component\Payum\Monetico\Api\Options\ConfigResolver;
use Ekyna\Component\Payum\Monetico\Api\Options\RequestResolver;
use Payum\Core\Exception\InvalidArgumentException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RuntimeException;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface;

/**
 * Class Api
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Api
{
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
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var RequestResolver
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
            'TPE'               => $this->config['tpe'],
            'societe'           => $this->config['company'],
            'version'           => static::VERSION,
            'lgue'              => $data['locale'],
            'date'              => $data['date'],
            'montant'           => $data['amount'] . $data['currency'],
            'reference'         => $data['reference'],
            'mail'              => $data['email'],
            'texte-libre'       => $data['comment'],
            'url_retour_ok'     => $data['success_url'],
            'url_retour_err'    => $data['failure_url'],
            'contexte_commande' => base64_encode(utf8_encode(json_encode($data['context']))),
        ];

        $fields['nbrech'] = count($data['schedule']);
        for ($i = 1; $i < 5; $i++) {
            if (!isset($data['schedule'][$i])) {
                $fields['dateech' . $i] = null;
                $fields['montantech' . $i] = null;
                continue;
            }

            $fields['dateech' . $i] = $data['schedule'][$i]['date'];
            $fields['montantech' . $i] = $data['schedule'][$i]['amount'] . $data['currency'];
        }

        $optional = [
            'aliascb',
            'forcesaisiecb',
            '3dsdebrayable',
            'ThreeDSecureChallenge',
            'libelleMonetique',
            'desactivemoyenpaiement',
            'protocole',
        ];
        foreach ($optional as $key) {
            if (isset($data[$key])) {
                $fields[$key] = (string)$data[$key];
            }
        }

        ksort($fields);

        $fields['MAC'] = $this->computeMac($fields);

        $fields['texte-libre'] = $this->htmlEncode($data['comment']);

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

        if (isset($data['TPE']) && $data['TPE'] != $this->config['tpe']) {
            return false;
        }

        if (isset($data['version']) && $data['version'] != static::VERSION) {
            return false;
        }

        $mac = strtolower($data['MAC']);

        unset($data['MAC']);

        ksort($data);

        $test = $this->computeMac($data);

        return $mac === $test;
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
        $hash = implode('*', array_map(function ($key, $value) {
            return "$key=$value";
        }, array_keys($data), array_values($data)));

        return strtolower(hash_hmac("sha1", $hash, $this->getMacKey()));
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
            } elseif ("7F" >= $var = bin2hex(substr($data, $i, 1))) {
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
        switch ($type) {
            case static::TYPE_PAYMENT:
            case static::TYPE_CANCEL:
                $host = 'https://p.monetico-services.com/';
                break;

            case static::TYPE_CAPTURE:
            case static::TYPE_REFUND:
                $host = 'https://payment-api.e-i.com/';
                break;

            default:
                throw new RuntimeException('Failed to determine the endpoint host.');
        }

        switch ($type) {
            case static::TYPE_PAYMENT:
                $path = 'paiement.cgi';
                break;

            case static::TYPE_CANCEL:
            case static::TYPE_CAPTURE:
                $path = 'capture_paiement.cgi';
                break;

            case static::TYPE_REFUND:
                $path = 'recredit_paiement.cgi';
                break;

            default:
                throw new RuntimeException('Failed to determine the endpoint path.');
        }

        if ($this->config['mode'] === static::MODE_TEST) {
            return $host . 'test/' . $path;
        }

        return $host . $path;
    }

    /**
     * Returns the config option resolver.
     *
     * @return ConfigResolver
     */
    private function getConfigResolver(): ConfigResolver
    {
        if (null !== $this->configResolver) {
            return $this->configResolver;
        }

        return $this->configResolver = new ConfigResolver();
    }

    /**
     * Returns request options resolver.
     *
     * @return RequestResolver
     */
    private function getRequestOptionsResolver(): RequestResolver
    {
        if (null !== $this->requestOptionsResolver) {
            return $this->requestOptionsResolver;
        }

        return $this->requestOptionsResolver = new RequestResolver();
    }
}
