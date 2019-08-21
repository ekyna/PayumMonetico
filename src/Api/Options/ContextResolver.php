<?php

namespace Ekyna\Component\Payum\Monetico\Api\Options;

use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ContextResolver
 * @package Ekyna\Component\Payum\Monetico\Api\Options
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ContextResolver extends OptionsResolver
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        $this
            ->setRequired('billing')
            ->setDefined([
                'shipping',
                'shoppingCart',
                'client',
            ])
            ->setAllowedTypes('billing', 'array')
            ->setNormalizer('billing', $this->createNormalizer($this->getBillingResolver()))
            ->setAllowedTypes('shipping', ['null', 'array'])
            ->setNormalizer('shipping', $this->createNormalizer($this->getShippingResolver(), false))
            ->setAllowedTypes('shoppingCart', ['null', 'array'])
            ->setNormalizer('shoppingCart', $this->createNormalizer($this->getCartResolver(), false))
            ->setAllowedTypes('client', ['null', 'array'])
            ->setNormalizer('client', $this->createNormalizer($this->getClientResolver(), false));
    }

    /**
     * Returns the billing options resolver.
     *
     * @return OptionsResolver
     */
    private function getBillingResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'addressLine1',
                'city',
                'postalCode',
                'country',
            ])
            ->setDefined([
                'civility',
                'name',
                'firstName',
                'lastName',
                'middleName',
                'address',
                'addressLine2',
                'addressLine3',
                'stateOrProvince',
                'countrySubdivision',
                'email',
                'phone',
                'mobilePhone',
                'homePhone',
                'workPhone',
            ]);

        $resolver
            ->setAllowedValues('civility', Assert::civility())
            ->setAllowedValues('name', Assert::string(45))
            ->setAllowedValues('firstName', Assert::string(45))
            ->setAllowedValues('lastName', Assert::string(45))
            ->setAllowedValues('middleName', Assert::string(45))
            ->setAllowedValues('address', Assert::string(255))
            ->setAllowedValues('addressLine1', Assert::string(50, true))
            ->setAllowedValues('addressLine2', Assert::string(50))
            ->setAllowedValues('addressLine3', Assert::string(255))
            ->setAllowedValues('city', Assert::string(50, true))
            ->setAllowedValues('postalCode', Assert::string(10, true))
            ->setAllowedValues('country', Assert::country(true))
            ->setAllowedValues('stateOrProvince', Assert::countrySubdivision())
            ->setAllowedValues('countrySubdivision', Assert::countrySubdivision())
            ->setAllowedValues('email', Assert::email())
            ->setAllowedValues('phone', Assert::phone())
            ->setAllowedValues('mobilePhone', Assert::phone())
            ->setAllowedValues('homePhone', Assert::phone())
            ->setAllowedValues('workPhone', Assert::phone());

        return $resolver;
    }

    /**
     * Returns the shipping options resolver.
     *
     * @return OptionsResolver
     */
    private function getShippingResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'addressLine1',
                'city',
                'postalCode',
                'country',
            ])
            ->setDefined([
                'civility',
                'name',
                'firstName',
                'lastName',
                'address',
                'addressLine2',
                'addressLine3',
                'stateOrProvince',
                'countrySubdivision',
                'email',
                'phone',
                'shipIndicator',
                'deliveryTimeframe',
                'firstUseDate',
                'matchBillingAddress',
            ]);

        $resolver
            ->setAllowedValues('civility', Assert::civility())
            ->setAllowedValues('name', Assert::string(45))
            ->setAllowedValues('firstName', Assert::string(45))
            ->setAllowedValues('lastName', Assert::string(45))
            ->setAllowedValues('address', Assert::string(255))
            ->setAllowedValues('addressLine1', Assert::string(50, true))
            ->setAllowedValues('addressLine2', Assert::string(50))
            ->setAllowedValues('addressLine3', Assert::string(255))
            ->setAllowedValues('city', Assert::string(50, true))
            ->setAllowedValues('postalCode', Assert::string(10, true))
            ->setAllowedValues('country', Assert::country(true))
            ->setAllowedValues('stateOrProvince', Assert::countrySubdivision())
            ->setAllowedValues('countrySubdivision', Assert::countrySubdivision())
            ->setAllowedValues('email', Assert::email())
            ->setAllowedValues('phone', Assert::phone());

        return $resolver;
    }

    /**
     * Returns the shopping cart options resolver.
     *
     * @return OptionsResolver
     */
    private function getCartResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'shoppingCartItems',
            ])
            ->setDefined([
                'giftCardAmount',
                'giftCardCount',
                'giftCardCurrency',
                'preOrderDate',
                'preorderIndicator',
                'reorderIndicator',
            ]);

        $resolver
            ->setAllowedTypes('shoppingCartItems', 'array')
            ->setNormalizer('shoppingCartItems', function (
            /** @noinspection PhpUnusedParameterInspection */
            Options $options,
            $value
        ) {
            if (empty($value)) {
                throw new InvalidOptionsException("The 'shoppingCartItems' entry cannot be empty.");
            }

            $resolver = $this->getItemResolver();

            $items = [];
            foreach ($value as $data) {
                $items[] = $resolver->resolve($data);
            }

            return $items;
        });

        $resolver
            ->setAllowedValues('giftCardAmount', Assert::integer(12))
            ->setAllowedValues('giftCardCount', Assert::integer(2))
            ->setAllowedValues('giftCardCurrency', Assert::currency())
            ->setAllowedValues('preOrderDate', Assert::date())
            ->setAllowedTypes('preorderIndicator', ['bool', 'null'])
            ->setAllowedTypes('reorderIndicator', ['bool', 'null']);

        return $resolver;
    }

    /**
     * Returns the shopping cart item options resolver.
     *
     * @return OptionsResolver
     */
    private function getItemResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setRequired([
                'unitPrice',
                'quantity',
            ])
            ->setDefined([
                'name',
                'description',
                'productCode',
                'imageURL',
                'productSKU',
                'productRisk',
            ]);

        $resolver
            ->setAllowedValues('unitPrice', Assert::integer(12))
            ->setAllowedValues('quantity', Assert::integer(6)) // Max size not specified in doc
            ->setAllowedValues('name', Assert::string(45))
            ->setAllowedValues('description', Assert::string(2048))
            ->setAllowedValues('productCode', [
                'adult_content',
                'coupon',
                'default',
                'electronic_good',
                'electronic_software',
                'gift_certificate',
                'handling_only',
                'service',
                'shipping_and_handling',
                'shipping_only',
                'subscription',
            ])
            ->setAllowedValues('imageURL', Assert::string(2000))
            ->setAllowedValues('productSKU', Assert::string(255))
            ->setAllowedValues('productRisk', ['low', 'normal', 'high']);

        return $resolver;
    }

    /**
     * Returns the client options resolver.
     *
     * @return OptionsResolver
     */
    private function getClientResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver
            ->setDefined([
                'civility',
                'name',
                'firstName',
                'lastName',
                'middleName',
                'address',
                'addressLine1',
                'addressLine2',
                'addressLine3',
                'city',
                'postalCode',
                'country',
                'stateOrProvince',
                'countrySubdivision',
                'email',
                'birthLastName',
                'birthCity',
                'birthPostalCode',
                'birthCountry',
                'birthStateOrProvince',
                'birthCountrySubdivision',
                'birthdate',
                'phone',
                'nationalIDNumber',
                'suspiciousAccountActivity',
                'authenticationMethod',
                'authenticationTimestamp',
                'priorAuthenticationMethod',
                'priorAuthenticationTimestamp',
                'paymentMeanAge',
                'lastYearTransactions',
                'last24HoursTransactions',
                'addCardNbLast24Hours',
                'last6MonthsPurchase',
                'lastPasswordChange',
                'accountAge',
                'lastAccountModification',
            ]);

        $resolver
            ->setAllowedValues('civility', Assert::civility())
            ->setAllowedValues('name', Assert::string(45))
            ->setAllowedValues('firstName', Assert::string(45))
            ->setAllowedValues('lastName', Assert::string(45))
            ->setAllowedValues('middleName', Assert::string(45))
            ->setAllowedValues('address', Assert::string(255))
            ->setAllowedValues('addressLine1', Assert::string(50))
            ->setAllowedValues('addressLine2', Assert::string(50))
            ->setAllowedValues('addressLine3', Assert::string(255))
            ->setAllowedValues('city', Assert::string(50))
            ->setAllowedValues('postalCode', Assert::string(10))
            ->setAllowedValues('country', Assert::country())
            ->setAllowedValues('stateOrProvince', Assert::countrySubdivision())
            ->setAllowedValues('countrySubdivision', Assert::countrySubdivision())
            ->setAllowedValues('email', Assert::email())
            ->setAllowedValues('birthLastName', Assert::string(45))
            ->setAllowedValues('birthCity', Assert::string(50))
            ->setAllowedValues('birthPostalCode', Assert::string(10))
            ->setAllowedValues('birthCountry', Assert::country())
            ->setAllowedValues('birthStateOrProvince', Assert::countrySubdivision())
            ->setAllowedValues('birthCountrySubdivision', Assert::countrySubdivision())
            ->setAllowedValues('birthdate', Assert::date())
            ->setAllowedValues('phone', Assert::phone())
            ->setAllowedValues('nationalIDNumber', Assert::string(255));

        $resolver
            ->setAllowedTypes('suspiciousAccountActivity', ['null', 'bool'])
            ->setAllowedValues('authenticationMethod', [
                'guest',
                'own_credentials',
                'federated_id',
                'issuer_credentials',
                'third_party_authentication',
                'fido',
            ])
            ->setAllowedValues('authenticationTimestamp', Assert::utcDate())
            ->setAllowedValues('priorAuthenticationMethod', [
                'guest',
                'own_credentials',
                'federated_id',
                'issuer_credentials',
                'third_party_authentication',
                'fido',
            ])
            ->setAllowedValues('priorAuthenticationTimestamp', Assert::utcDate())
            ->setAllowedValues('paymentMeanAge', Assert::date())
            ->setAllowedTypes('lastYearTransactions', ['null', 'int'])
            ->setAllowedTypes('last24HoursTransactions', ['null', 'int'])
            ->setAllowedValues('addCardNbLast24Hours', Assert::date())
            ->setAllowedTypes('last6MonthsPurchase', ['null', 'int'])
            ->setAllowedValues('lastPasswordChange', Assert::date())
            ->setAllowedValues('accountAge', Assert::date())
            ->setAllowedValues('lastAccountModification', Assert::date());

        return $resolver;
    }

    /**
     * Creates a nested options normalizer.
     *
     * @param OptionsResolver $resolver
     * @param bool            $required
     *
     * @return callable
     */
    private function createNormalizer(OptionsResolver $resolver, bool $required = true): callable
    {
        return function (
            /** @noinspection PhpUnusedParameterInspection */
            Options $options,
            $value
        ) use ($resolver, $required) {
            if (empty($value)) {
                if ($required) {
                    throw new InvalidOptionsException();
                }

                return null;
            }

            return $resolver->resolve($value);
        };
    }
}
