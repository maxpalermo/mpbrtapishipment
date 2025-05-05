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

use MpSoft\MpBrtApiShipment\Helpers\GetByNumericReference;

class ModelBrtShipmentResponse extends \ObjectModel
{
    public $id;
    public $current_time_utc;
    public $arrival_terminal;
    public $arrival_depot;
    public $delivery_zone;
    public $parcel_number_from;
    public $parcel_number_to;
    public $departure_depot;
    public $series_number;
    public $service_type;
    public $consignee_company_name;
    public $consignee_address;
    public $consignee_zip_code;
    public $consignee_city;
    public $consignee_province_abbreviation;
    public $consignee_country_abbreviation_brt;
    public $cash_on_delivery;
    public $number_of_parcels;
    public $weight_kg;
    public $volume_m3;
    public $numeric_sender_reference;
    public $alphanumeric_sender_reference;
    public $sender_company_name;
    public $sender_province_abbreviation;
    public $disclaimer;
    public $execution_message;

    public static $definition = [
        'table' => 'brt_shipment_response',
        'primary' => 'id_brt_shipment_response',
        'fields' => [
            'current_time_utc' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'arrival_terminal' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'arrival_depot' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'delivery_zone' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'parcel_number_from' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'parcel_number_to' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'departure_depot' => ['type' => self::TYPE_STRING, 'validate' => 'isUnsignedInt'],
            'series_number' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'service_type' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'consignee_company_name' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'consignee_address' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'consignee_zip_code' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'consignee_city' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'consignee_province_abbreviation' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'consignee_country_abbreviation_brt' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'cash_on_delivery' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'number_of_parcels' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'weight_kg' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'volume_m3' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'numeric_sender_reference' => ['type' => self::TYPE_STRING, 'size' => 15, 'validate' => 'isUnsignedInt'],
            'alphanumeric_sender_reference' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'sender_company_name' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'sender_province_abbreviation' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'disclaimer' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything'],
            'execution_message' => ['type' => self::TYPE_HTML, 'validate' => 'isJson'],
        ],
    ];
    // Relazione: le label sono in ModelBrtShipmentResponseLabel con id_brt_shipment_response

    public static function exists($numericSenderReference)
    {
        $db = \Db::getInstance();
        $query = new \DbQuery();
        $query->select('id_brt_shipment_response')
            ->from('brt_shipment_response')
            ->where('numeric_sender_reference = '.(int) $numericSenderReference);

        return (bool) $db->getValue($query);
    }

    public static function getByNumericSenderReference($numericSenderReference): ModelBrtShipmentResponse
    {
        $result = (new GetByNumericReference($numericSenderReference, self::$definition['table'], self::$definition['primary']))->run(self::class);
        if ($result) {
            return $result[0];
        }

        return new self();
    }
}
