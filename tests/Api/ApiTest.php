<?php

namespace Ekyna\Component\Payum\Monetico\Api;

use Payum\Core\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Class ApiTest
 * @package Ekyna\Component\Payum\Monetico
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ApiTest extends TestCase
{
    public function test_invalid_config()
    {
        $this->expectException(InvalidArgumentException::class);

        $api = new Api();
        $api->setConfig([
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
            'TPE'              => '123456',
            'date'             => '17/03/2018:10:40:10',
            'montant'          => '24.80EUR',
            'reference'        => '100008783',
            'texte-libre'      => 'Commande 100008783',
            'version'          => '3.0',
            'lgue'             => 'FR',
            'societe'          => 'foobar',
            'mail'             => 'test@example.org',
            'context_commande' => base64_encode(utf8_encode(json_encode([
                'billing' => [
                    'addressLine1' => '101 Rue de Roisel',
                    'city'         => 'Y',
                    'postalCode'   => '80190',
                    'country'      => 'FR',
                ],
            ]))),
        ]);

        $expected = '037989707dd8a50de71d74c52dd7f8bba9951d4f';

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
            'success_url' => 'http://example.org',
            'failure_url' => 'http://example.org',
            'context'     => [
                'billing' => [
                    'addressLine1'    => '1st some road',
                    'city'            => 'some city',
                    'postalCode'      => '12345',
                    'country'         => 'US',
                    'stateOrProvince' => 'US-CA',
                ],
            ],
        ]);

        $expected = [
            'action' => 'https://p.monetico-services.com/paiement.cgi',
            'method' => 'POST',
            'fields' => [
                'TPE'               => '1234567',
                'contexte_commande' => 'eyJiaWxsaW5nIjp7ImFkZHJlc3NMaW5lMSI6IjFzdCBzb21lIHJvYWQiLCJjaXR5Ijoic29tZSBjaXR5IiwicG9zdGFsQ29kZSI6IjEyMzQ1IiwiY291bnRyeSI6IlVTIiwic3RhdGVPclByb3ZpbmNlIjoiVVMtQ0EifX0=',
                'date'              => '17/03/2018:10:40:10',
                'dateech1'          => null,
                'dateech2'          => null,
                'dateech3'          => null,
                'dateech4'          => null,
                'lgue'              => 'FR',
                'mail'              => 'test@example.org',
                'montant'           => '24.80EUR',
                'montantech1'       => null,
                'montantech2'       => null,
                'montantech3'       => null,
                'montantech4'       => null,
                'nbrech'            => 0,
                'reference'         => '100008783',
                'societe'           => 'foobar',
                'texte-libre'       => 'Commande&#x20;100008783',
                'url_retour_ok'     => 'http://example.org',
                'url_retour_err'    => 'http://example.org',
                'version'           => '3.0',
                'MAC'               => '14ea2a9f24e2ce030889a8176f0cf2c87abf5a10',
            ],
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_check_payment_response()
    {
        $data = [
            'TPE'              => '1234567',
            'date'             => '05/12/2006_a_11:55:23',
            'montant'          => '62.75EU',
            'reference'        => 'ABERTYP00145',
            'MAC'              => '7f7a072d294eead5278d982ea949fad28b3437ad',
            'texte-libre'      => 'LeTexteLibre',
            'coderetour'       => 'paiement',
            'cvx'              => 'oui',
            'vld'              => '1208',
            'brand'            => 'VI',
            'status3ds'        => '1',
            'numauto'          => '010101',
            'originecb'        => 'FRA',
            'bincb'            => '010101',
            'hpancb'           => '74E94B03C22D786E0F2C2CADBFC1C00B004B7C45',
            'ipclient'         => '127.0.0.1',
            'originetr'        => 'FRA',
            'veres'            => 'Y',
            'pares'            => 'Y',
            'authentification' => 'ewoJInN0YXR1cyIgOiAiYXV0aGVudGljYXRlZCIsCgkicHJvdG9jb2wiIDogIjNEU2VjdXJlIiwKCSJ2ZXJzaW9uIiA
iMi4xLjAiLAoJImRldGFpbHMiIDogCgl7CgkJImxpYWJpbGl0eVNoaWZ0IiA6ICJZIiwKCQkiQVJlcyIgOiAiQyIsCg
kJIkNSZXMiIDogIlkiLAoJCSJtZXJjaGFudFByZWZlcmVuY2UiIDogIm5vX3ByZWZlcmVuY2UiLAoJCSJ0cmFuc
2FjdGlvbklEIiA6ICI1NTViZDlkOS0xY2YxLTRiYTgtYjM3Yy0xYTk2YmM4YjYwM2EiLAoJCSJhdXRoZW50aWN
hdGlvblZhbHVlIiA6ICJjbUp2ZDBJNFNIazNVVFJrWWtGU1EzRllZM1U9IgoJfQp9',
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
            'mode'    => $mode,
            'tpe'     => '1234567',
            'key'     => '1234567',
            'company' => 'foobar',
        ]);

        return $api;
    }
}