<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace MpSoft\MpBrtApiShipment\Api;

class CashOnDelivery
{
    public const CASH_ON_DELIVERY_MODULES = 'cash_on_delivery_modules';

    public static function getCashOnDeliveryModules()
    {
        try {
            $modules = json_decode(\Configuration::get(self::CASH_ON_DELIVERY_MODULES), true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $th) {
            return [];
        }

        return $modules;
    }

    public static function setCashOnDeliveryModules(array $modules)
    {
        try {
            \Configuration::updateValue(self::CASH_ON_DELIVERY_MODULES, json_encode($modules));
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }
}
