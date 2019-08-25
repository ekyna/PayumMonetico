<?php

/*
 * Use this script to test your credentials.
 * 'php -S localhost:8000' to start a web server.
 */

require __DIR__ . '/bootstrap.php';

use Ekyna\Component\Payum\Monetico\MoneticoGatewayFactory;
use Ekyna\Component\Payum\Monetico\Request\PaymentForm;
use League\Uri\Http;
use Payum\Core\Model\Payment;
use Payum\Core\PayumBuilder;
use Payum\Core\Reply\HttpResponse;

$paymentClass = Payment::class;

$defaultConfig = [
    'mode'    => '',
    'tpe'     => '',
    'key'     => '',
    'company' => '',
    'debug'   => true,
];

/** @var \Payum\Core\Payum $payum */
$payum = (new PayumBuilder())
    ->addDefaultStorages()
    ->addGatewayFactory('monetico', new MoneticoGatewayFactory($defaultConfig))
    ->addGateway('monetico', [
        'factory' => 'monetico',
        'sandbox' => true,
    ])
    ->getPayum();

/** @var \Payum\Core\Gateway $gateway */
$gateway = $payum->getGateway('monetico');

$uri = Http::createFromServer($_SERVER);

$request = new PaymentForm([
    'date'        => '25/08/2019:16:30:15',
    'amount'      => '24.80',
    'currency'    => 'EUR',
    'reference'   => '100008784',
    'comment'     => 'Commande 100008784',
    'locale'      => 'FR',
    'email'       => 'test@example.org',
    'success_url' => (string)$uri->withPath('/done.php'),
    'failure_url' => (string)$uri->withPath('/done.php'),
    'context'     => [
        'billing' => [
            "addressLine1" => "101 Rue de Roisel",
            "city"         => "Y",
            "postalCode"   => "80190",
            "country"      => "FR",
        ],
    ],
]);

try {
    $gateway->execute($request);
} catch (HttpResponse $response) {
    echo $response->getContent();
}
