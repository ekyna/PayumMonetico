<?php

namespace Ekyna\Component\Payum\Monetico\Action\Api;

use Ekyna\Component\Payum\Monetico\Api\Api;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareInterface;
use Payum\Core\GatewayAwareTrait;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AbstractApiAction
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractApiAction implements ActionInterface, GatewayAwareInterface, ApiAwareInterface, LoggerAwareInterface
{
    use GatewayAwareTrait;

    /**
     * @var Api
     */
    protected $api;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * @inheritDoc
     */
    public function setApi($api)
    {
        if (!$api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Logs the given message.
     *
     * @param string $message
     */
    protected function log($message)
    {
        if (!$this->logger) {
            return;
        }

        $this->logger->debug($message);
    }

    /**
     * Logs the given message and data.
     *
     * @param string $message
     * @param array  $data
     * @param array  $filterKeys
     */
    protected function logData($message, array $data, array $filterKeys = [])
    {
        if (!$this->logger) {
            return;
        }

        if (!empty($filterKeys)) {
            $data = array_intersect_key($data, array_flip($filterKeys));
        }

        $data = array_map(function ($key, $value) {
            return "$key: $value";
        }, array_keys($data), $data);

        $this->logger->debug($message . ': ' . implode(', ', $data));
    }
}
