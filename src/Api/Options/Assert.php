<?php

namespace Ekyna\Component\Payum\Monetico\Api\Options;

use Payum\ISO4217\ISO4217;
use Sokil\IsoCodes\IsoCodesFactory;

/**
 * Class Assert
 * @package Ekyna\Component\Payum\Monetico\Api\Options
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Assert
{
    /**
     * Assert civility.
     *
     * @return callable
     */
    public static function civility(): callable
    {
        return function ($value) {
            return is_null($value) || preg_match('~^[a-zA-Z]{1,32}$~', $value);
        };
    }

    /**
     * Asserts string with max length.
     *
     * @param int  $max
     * @param bool $required
     *
     * @return callable
     */
    public static function string(int $max, bool $required = false): callable
    {
        return function ($value) use ($max, $required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            if (!is_string($value)) {
                return false;
            }

            $length = strlen($value);

            return (1 < $length) && ($length <= $max);
        };
    }

    /**
     * Asserts integer with max size.
     *
     * @param int  $max
     * @param bool $required
     *
     * @return callable
     */
    public static function integer(int $max, bool $required = false): callable
    {
        return function ($value) use ($max, $required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            if (!is_int($value)) {
                return false;
            }

            $length = strlen($value);

            return (1 < $length) && ($length <= $max);
        };
    }

    /**
     * Assert currency.
     *
     * @param bool $required
     *
     * @return callable
     */
    public static function currency(bool $required = false): callable
    {
        return function ($value) use ($required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            return null !== (new ISO4217())->findByAlpha3($value);
        };
    }

    /**
     * Assert country.
     *
     * @param bool $required
     *
     * @return callable
     */
    public static function country(bool $required = false): callable
    {
        return function ($value) use ($required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            return null !== (new IsoCodesFactory())->getCountries()->getByAlpha2($value);
        };
    }

    /**
     * Assert country subdivision.
     *
     * @param bool $required
     *
     * @return callable
     */
    public static function countrySubdivision(bool $required = false): callable
    {
        return function ($value) use ($required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            return null !== (new IsoCodesFactory())->getSubdivisions()->getByCode($value);
        };
    }

    /**
     * Assert email.
     *
     * @param bool $required
     *
     * @return callable
     */
    public static function email(bool $required = false): callable
    {
        return function ($value) use ($required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            return filter_var($value, FILTER_VALIDATE_EMAIL);
        };
    }

    /**
     * Assert date.
     *
     * @param bool $required
     *
     * @return callable
     */
    public static function date(bool $required = false): callable
    {
        return function ($value) use ($required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            return preg_match('~^[0-9]{4}-[0-9]{2}-[0-9]{2}$~', $value) && date_create($value);
        };
    }

    /**
     * Assert UTC date.
     *
     * @param bool $required
     *
     * @return callable
     */
    public static function utcDate(bool $required = false): callable
    {
        return function ($value) use ($required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            return preg_match('~^[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}-[0-9]{2}-[0-9]{2}Z$~', $value)
                && date_create($value);
        };
    }

    /**
     * Assert date.
     *
     * @param bool $required
     *
     * @return callable
     */
    public static function phone(bool $required = false): callable
    {
        return function ($value) use ($required): bool {
            if (is_null($value)) {
                if ($required) {
                    return false;
                }

                return true;
            }

            return preg_match('~^\+(?:[0-9] ?){6,14}[0-9]$~', $value);
        };
    }
}
