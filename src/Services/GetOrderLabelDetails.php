<?php

namespace MpSoft\MpBrtApiShipment\Services;

use Doctrine\DBAL\Connection;
use MpSoft\MpBrtApiShipment\Api\BrtAuthManager;
use MpSoft\MpBrtApiShipment\Api\BrtConfiguration;

final class GetOrderLabelDetails
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function run($orderId): array
    {
        $conf = new BrtConfiguration();
        $order = new \Order($orderId);
        $codModules = $conf->get('cash_on_delivery_modules') ?? [];
        if (is_string($codModules)) {
            $codModules = [$codModules];
        }

        if (!\Validate::isLoadedObject($order)) {
            return [
                'success' => false,
                'message' => 'Ordine non trovato',
            ];
        }

        $account = (new BrtAuthManager())->getAccount();
        $address = new \Address($order->id_address_delivery);
        $country = new \Country($address->id_country);
        $state = new \State($address->id_state);
        $customer = new \Customer($order->id_customer);

        $module = $order->module;
        $cashOnDeliveryModulesName = array_filter(array_map(function ($module) {
            $paymentModule = \Module::getInstanceById((int) $module);
            if (!\Validate::isLoadedObject($paymentModule)) {
                return 0;
            }

            return $paymentModule->name;
        }, $codModules));
        if (!in_array('mpcodfee', $cashOnDeliveryModulesName)) {
            $cashOnDeliveryModulesName[] = 'mpcodfee';
        }
        $cashOnDelivery = in_array($module, $cashOnDeliveryModulesName);
        $cashOnDeliveryAmount = number_format((float) ($cashOnDelivery ? $order->total_paid_tax_incl : 0), 2);

        $labelDetails = [
            'account' => $account->toArray(),
            'departure_depot' => (new BrtConfiguration())->get('departure_depot'),
            'numeric_sender_reference' => $order->id,
            'alphanumeric_sender_reference' => $order->reference,
            'consignee_company_name' => $address->company,
            'consignee_contact_name' => $address->firstname.' '.$address->lastname,
            'consignee_address' => $address->address1,
            'consignee_zip_code' => $address->postcode,
            'consignee_city' => $address->city,
            'consignee_province_abbreviation' => $state->iso_code,
            'consignee_country_abbreviation_iso_alpha_2' => $country->iso_code,
            'consignee_telephone' => $address->phone,
            'consignee_mobile_phone_number' => $address->phone_mobile,
            'consignee_email' => $customer->email,
            'service_type' => (new BrtConfiguration())->get('service_type'),
            'network' => 'DEF',
            'delivery_freight_type_code' => (new BrtConfiguration())->get('delivery_freight_type_code'),
            'sender_parcel_type' => (new BrtConfiguration())->get('sender_parcel_type'),
            'notes' => $address->other,
            'is_cod_mandatory' => (int) $cashOnDelivery,
            'cash_on_delivery' => $cashOnDeliveryAmount,
            'cod_currency' => \Context::getContext()->currency->iso_code,
            'number_of_parcels' => 1,
            'weight_kg' => 1,
            'volume_m3' => 0,
        ];

        return [
            'success' => true,
            'details' => $labelDetails,
        ];
    }
}
