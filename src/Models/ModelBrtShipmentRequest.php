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

namespace MpSoft\MpBrtApiShipment\Models;

use MpSoft\MpBrtApiShipment\Helpers\DeleteByNumericReference;
use MpSoft\MpBrtApiShipment\Helpers\GetByNumericReference;

class ModelBrtShipmentRequest extends \ObjectModel
{
    public $id;
    public $order_id;
    public $numeric_sender_reference;
    public $alphanumeric_sender_reference;
    public $account_json;
    public $create_data_json;
    public $is_label_required;
    public $label_parameters_json;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'brt_shipment_request',
        'primary' => 'id_brt_shipment_request',
        'fields' => [
            'order_id' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'numeric_sender_reference' => ['type' => self::TYPE_STRING, 'size' => 15, 'validate' => 'isUnsignedInt', 'required' => true],
            'alphanumeric_sender_reference' => ['type' => self::TYPE_STRING, 'size' => 15, 'validate' => 'isAnything', 'required' => true],
            'account_json' => ['type' => self::TYPE_HTML, 'validate' => 'isJson'],
            'create_data_json' => ['type' => self::TYPE_HTML, 'validate' => 'isJson'],
            'is_label_required' => ['type' => self::TYPE_INT, 'validate' => 'isBool'],
            'label_parameters_json' => ['type' => self::TYPE_HTML, 'validate' => 'isJson'],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function getNumericSenderReferenceByOrderId($orderId)
    {
        $db = \Db::getInstance();
        $query = new \DbQuery();
        $query->select('numeric_sender_reference')
            ->from(self::$definition['table'])
            ->where('order_id = '.(int) $orderId);

        return (int) $db->getValue($query);
    }

    public static function getAlphanumericSenderReferenceByOrderId($orderId)
    {
        $db = \Db::getInstance();
        $query = new \DbQuery();
        $query->select('alphanumeric_sender_reference')
            ->from(self::$definition['table'])
            ->where('order_id = '.(int) $orderId);

        return (string) $db->getValue($query);
    }

    public static function getByNumericSenderReference($numericSenderReference): ModelBrtShipmentRequest
    {
        $result = (new GetByNumericReference($numericSenderReference, self::$definition['table'], self::$definition['primary']))->run(self::class);
        if ($result) {
            return $result[0];
        }

        return new self();
    }

    public static function getByIdOrder($idOrder): ModelBrtShipmentRequest
    {
        $numericSenderReference = self::getNumericSenderReferenceByOrderId($idOrder);

        return self::getByNumericSenderReference($numericSenderReference);
    }

    public static function deleteByNumericSenderReference($numericSenderReference): bool
    {
        return (new DeleteByNumericReference($numericSenderReference, self::$definition['table']))->run();
    }

    public static function deleteByIdOrder($idOrder): bool
    {
        $numericSenderReference = self::getNumericSenderReferenceByOrderId($idOrder);

        return self::deleteByNumericSenderReference($numericSenderReference);
    }
}
