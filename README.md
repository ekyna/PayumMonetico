# ekyna/PayumMonetico

Payum Monetico (Credit Mutuel/CIC/OBC) payment gateway.

[![Build Status][ico-github-actions]][link-github-actions]

* __Symfony__ bundle available [here](https://github.com/ekyna/PayumMoneticoBundle).
* __Sylius__ plugin available [here](https://github.com/FLUX-SE/SyliusPayumMoneticoPlugin).

## Installation / Configuration

```bash
composer req ekyna/payum-monetico
```

```php
use Ekyna\Component\Payum\Monetico\Api\Api;
use Ekyna\Component\Payum\Monetico\MoneticoGatewayFactory;

$factory = new MoneticoGatewayFactory();

$gateway = $factory->create([
    'mode'      => Api::MODE_PRODUCTION,
    'tpe'       => '123456',
    'key'       => '123456',
    'company'   => 'foobar',
]);

// Register your convert payment action
// $gateway->addAction(new \Acme\ConvertPaymentAction());

```

### Create your convert action

See [src/Action/ConvertPaymentAction.php](https://github.com/ekyna/PayumMonetico/blob/master/src/Action/ConvertPaymentAction.php) sample.

### Create your notify controller

Example (Symfony):

```php
public function notifyAction(Request $request)
{
    // Get the reference you set in your ConvertAction
    if (null === $reference = $request->request->get('reference')) {
        throw new NotFoundHttpException();
    }

    // Find your payment entity
    $payment = $this
        ->get('acme.repository.payment')
        ->findOneBy(['number' => $reference]);

    if (null === $payment) {
        throw new NotFoundHttpException();
    }

    $payum = $this->get('payum');

    // Execute notify & status actions.
    $gateway = $payum->getGateway('monetico');
    $gateway->execute(new Notify($payment));
    $gateway->execute(new GetHumanStatus($payment));

    // Get the payment identity
    $identity = $payum->getStorage($payment)->identify($payment);

    // Invalidate payment tokens
    $tokens = $payum->getTokenStorage()->findBy([
        'details' => $identity,
    ]);
    foreach ($tokens as $token) {
        $payum->getHttpRequestVerifier()->invalidate($token);
    }

    // Return expected response
    return new Response(\Ekyna\Component\Payum\Monetico\Api\Api::NOTIFY_SUCCESS);
}
```

[ico-github-actions]: https://github.com/ekyna/PayumMonetico/workflows/Build/badge.svg
[link-github-actions]: https://github.com/ekyna/PayumMonetico/actions?query=workflow%3A"Build"
