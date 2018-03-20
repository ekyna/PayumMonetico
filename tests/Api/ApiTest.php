<?php

namespace Ekyna\Component\Payum\Cybermut\Api;

use Ekyna\Component\Payum\Cybermut\TestCase;
use Payum\Core\Exception\InvalidArgumentException;

/**
 * Class ApiTest
 * @package Ekyna\Component\Payum\Cybermut
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ApiTest extends TestCase
{
    public function test_invalid_config()
    {
        $this->expectException(InvalidArgumentException::class);

        $api = new Api();
        $api->setConfig([
            'bank'    => null,
            'mode'    => null,
            'tpe'     => null,
            'key'     => null,
            'company' => null,
        ]);
    }

    public function test_valid_config()
    {
        $this->createApi();

        $this->assertTrue(true);
    }

    public function test_compute_mac()
    {
        $api = $this->createApi();

        $actual = $api->computeMac([
            '123456',              // TPE
            '17/03/2018:10:40:10', // date
            '24.80EUR',            // montant
            '100008783',           // reference
            'Commande 100008783',  // texte-libre
            '3.0',                 // version
            'FR',                  // lgue
            'foobar',              // societe
            'test@example.org',    // mail
        ]);

        $expected = 'e13096f4559dd4a2c2f581d44bef6cd482ddefa0';

        $this->assertEquals($expected, $actual);
    }

    public function test_create_payment_form()
    {
        $api = $this->createApi();

        $actual = $api->createPaymentForm([
            'date'        => '17/03/2018:10:40:10',
            'amount'      => '24.80',
            'currency'    => 'EUR',
            'reference'   => '100008783',
            'comment'     => 'Commande 100008783',
            'locale'      => 'FR',
            'email'       => 'test@example.org',
            'return_url'  => 'http://example.org',
            'success_url' => 'http://example.org',
            'failure_url' => 'http://example.org',
        ]);

        $expected = [
            'action' => 'https://paiement.creditmutuel.fr/paiement.cgi',
            'method' => 'POST',
            'fields' => [
                'TPE'            => '123456',
                'date'           => '17/03/2018:10:40:10',
                'montant'        => '24.80EUR',
                'reference'      => '100008783',
                'texte-libre'    => 'Commande&#x20;100008783',
                'version'        => '3.0',
                'lgue'           => 'FR',
                'societe'        => 'foobar',
                'mail'           => 'test@example.org',
                'MAC'            => '7da4821b50e5971be44993fdbee2b7696469948f',
                'url_retour'     => 'http://example.org',
                'url_retour_ok'  => 'http://example.org',
                'url_retour_err' => 'http://example.org',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_check_payment_response()
    {
        $data = [
            'MAC'         => '9e1aef18d37e66ba2f8bcaa75c97e0e4aed4fd89',
            'date'        => '17/03/2018_a_10:40:10',
            'montant'     => '24.80EUR',
            'reference'   => '100008783',
            'texte-libre' => 'Commande 100008783',
            'code-retour' => 'paiement',
            'cvx'         => 'oui',
            'vld'         => '1208',
            'brand'       => 'VI',
            'status3ds'   => '1',
            'numauto'     => '010101',
            'originecb'   => 'FRA',
            'bincb'       => '010101',
            'hpancb'      => '74E94B03C22D786E0F2C2CADBFC1C00B004B7C45',
            'ipclient'    => '127.0.0.1',
            'originetr'   => 'FRA',
        ];

        $api = $this->createApi();

        $this->assertTrue($api->checkPaymentResponse($data));
    }

    /**
     * Returns the instance.
     *
     * @param string $mode
     *
     * @return Api
     */
    private function createApi($mode = Api::MODE_PRODUCTION)
    {
        $api = new Api();

        $api->setConfig([
            'bank'    => Api::BANK_CM,
            'mode'    => $mode,
            'tpe'     => '123456',
            'key'     => '123456',
            'company' => 'foobar',
        ]);

        return $api;
    }
}