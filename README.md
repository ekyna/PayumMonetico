# ekyna/PayumMonetico

Payum Monetico (Credit Mutuel/CIC/OBC) payment gateway.

[![Build Status](https://travis-ci.org/ekyna/PayumMonetico.svg?branch=master)](https://travis-ci.org/ekyna/PayumMonetico)

## Installation / Configuration

```
composer req ekyna/payum-monetico
```

```

use Ekyna\Component\Payum\Monetico\Api\Api;
use Ekyna\Component\Payum\Monetico\MoneticoGatewayFactory;

$factory = new MoneticoGatewayFactory();

$gateway = $factory->create([
    'bank'      => Api::BANK_CM,
    'mode'      => Api::MODE_PRODUCTION,
    'tpe'       => '123456',
    'key'       => '123456',
    'company'   => 'foobar',
]);

```
