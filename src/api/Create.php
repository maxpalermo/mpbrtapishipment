<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
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

use MpSoft\MpBrtApiShipment\Api\Account;
use MpSoft\MpBrtApiShipment\Api\LabelParameters;
use MpSoft\MpBrtApiShipment\Api\ShipmentRequest;
use MpSoft\MpBrtApiShipment\Api\ShipmentResponse;
use MpSoft\MpBrtApiShipment\Api\BrtAuthManager;

class Create
{
    /**
     * Invia una spedizione a BRT usando un ordine PrestaShop
     * @param int $orderId ID ordine PrestaShop
     * @param array $config Configurazione dati fissi BRT (senza credenziali)
     * @param string|null $env Ambiente ('real' o 'sandbox'), se null prende da config PrestaShop
     * @return ShipmentResponse Risposta API BRT come oggetto
     */
    public static function sendShipment($orderId, $config, $env = null)
    {
        // Recupera oggetti PrestaShop
        $order = new \Order($orderId);
        $customer = new \Customer($order->id_customer);
        $address = new \Address($order->id_address_delivery);

        // Recupera dati Paese e Stato
        $countryIso = \Country::getIsoById($address->id_country);
        $province = $address->id_state ? \State::getNameById($address->id_state) : '';

        // Prepara i dati spedizione secondo la documentazione BRT (tutti i campi, default dove mancante)
        $shipmentData = [
            'network' => $config['network'] ?? '',
            'departureDepot' => $config['departureDepot'],
            'senderCustomerCode' => $config['senderCustomerCode'],
            'deliveryFreightTypeCode' => $config['deliveryFreightTypeCode'] ?? 'DAP',
            'consigneeCompanyName' => $address->company ?: ($customer->firstname . ' ' . $customer->lastname),
            'consigneeAddress' => $address->address1,
            'consigneeCountryAbbreviationISOAlpha2' => $countryIso,
            'consigneeZIPCode' => $address->postcode,
            'consigneeCity' => $address->city,
            'consigneeProvinceAbbreviation' => $province,
            'consigneeContactName' => $customer->firstname . ' ' . $customer->lastname,
            'consigneeTelephone' => $address->phone ?: $address->phone_mobile,
            'consigneeEMail' => $customer->email,
            'consigneeMobilePhoneNumber' => '',
            'isAlertRequired' => '0',
            'consigneeVATNumber' => '',
            'consigneeVATNumberCountryISOAlpha2' => '',
            'consigneeItalianFiscalCode' => '',
            'pricingConditionCode' => $config['pricingConditionCode'] ?? '',
            'serviceType' => $config['serviceType'] ?? '',
            'insuranceAmount' => 0,
            'insuranceAmountCurrency' => 'EUR',
            'senderParcelType' => '',
            'quantityToBeInvoiced' => 0.0,
            'cashOnDelivery' => 0,
            'isCODMandatory' => '0',
            'codPaymentType' => '',
            'codCurrency' => 'EUR',
            'notes' => '',
            'parcelsHandlingCode' => '',
            'deliveryDateRequired' => '',
            'deliveryType' => '',
            'declaredParcelValue' => 0,
            'declaredParcelValueCurrency' => 'EUR',
            'particularitiesDeliveryManagementCode' => '',
            'particularitiesHoldOnStockManagementCode' => '',
            'variousParticularitiesManagementCode' => '',
            'particularDelivery1' => '',
            'particularDelivery2' => '',
            'palletType1' => '',
            'palletType1Number' => 0,
            'palletType2' => '',
            'palletType2Number' => 0,
            'originalSenderCompanyName' => '',
            'originalSenderZIPCode' => '',
            'originalSenderCountryAbbreviationISOAlpha2' => '',
            'cmrCode' => '',
            'neighborNameMandatoryAuthorization' => '',
            'pinCodeMandatoryAuthorization' => '',
            'packingListPDFName' => '',
            'packingListPDFFlagPrint' => '',
            'packingListPDFFlagEmail' => '',
            'numericSenderReference' => $order->id,
            'alphanumericSenderReference' => $order->reference,
            'numberOfParcels' => 1,
            'weightKG' => $order->getTotalWeight(),
            'volumeM3' => 0,
            'consigneeClosingShift1_DayOfTheWeek' => '',
            'consigneeClosingShift1_PeriodOfTheDay' => '',
            'consigneeClosingShift2_DayOfTheWeek' => '',
            'consigneeClosingShift2_PeriodOfTheDay' => '',
            // Campi relativi a return shipment
            'returnDepot' => '',
            'expiryDate' => '',
            'holdForPickup' => '',
            'genericReference' => '',
            // Campi relativi a labelParameters non qui ma nella root
            // Campi relativi a actualSender
            'actualSender' => [
                'actualSenderName' => '',
                'actualSenderCity' => '',
                'actualSenderAddress' => '',
                'actualSenderZIPCode' => '',
                'actualSenderProvince' => '',
                'actualSenderCountry' => '',
                'actualSenderEmail' => '',
                'actualSenderMobilePhoneNumber' => '',
                'actualSenderPudoId' => '',
            ],
            // Campi relativi a returnShipmentConsignee
            'returnShipmentConsignee' => [
                'returnShipmentConsigneeName' => '',
                'returnShipmentConsigneeCity' => '',
                'returnShipmentConsigneeAddress' => '',
                'returnShipmentConsigneeZIPCode' => '',
                'returnShipmentConsigneeProvince' => '',
                'returnShipmentConsigneeCountry' => '',
                'returnShipmentConsigneeEmail' => '',
                'returnShipmentConsigneeMobilePhoneNumber' => '',
                'returnShipmentConsigneePudoId' => '',
            ],
            'pudoId' => '',
            'brtServiceCode' => '',
        ];

        // Consenti override dei dati da $config
        if (!empty($config['createData'])) {
            $shipmentData = array_merge_recursive($shipmentData, $config['createData']);
        }

        $labelParameters = [
            'outputType' => 'ZPL',
            'offsetX' => 0,
            'offsetY' => 0,
            'isBorderRequired' => '0',
            'isLogoRequired' => '0',
            'isBarcodeControlRowRequired' => '0',
        ];
        if (!empty($config['labelParameters'])) {
            $labelParameters = array_merge($labelParameters, $config['labelParameters']);
        }

        // Gestione ambiente e credenziali
        if ($env === null) {
            $env = \Configuration::get('BRT_ENVIRONMENT') ?: BrtAuthManager::ENV_REAL;
        }
        $authManager = new BrtAuthManager();
        $account = $authManager->getAccount($env);
        $labelObj = isset($config['labelParameters']) ? LabelParameters::fromArray($config['labelParameters']) : new LabelParameters();
        $isLabelRequired = $config['isLabelRequired'] ?? 1;
        $shipmentRequest = new ShipmentRequest($account, $shipmentData, $isLabelRequired, $labelObj);
        return self::sendShipmentRequest($shipmentRequest);
    }

    /**
     * Invia una spedizione a BRT passando direttamente tutti i dati necessari (object style)
     * @param ShipmentRequest $shipmentRequest
     * @param string|null $env Ambiente ('real' o 'sandbox'), opzionale solo se vuoi cambiare endpoint in base all'ambiente
     * @return ShipmentResponse
     */
    public static function sendShipmentRequest(ShipmentRequest $shipmentRequest, $env = null)
    {
        $payload = $shipmentRequest->toArray();
        $response = self::callApi($payload);
        // Interpreta la risposta come ShipmentResponse
        if (!empty($response['data']['createResponse'])) {
            return new ShipmentResponse($response['data']['createResponse']);
        } else {
            // Risposta di errore generica
            return new ShipmentResponse([
                'executionMessage' => [
                    'code' => $response['data']['executionMessage']['code'] ?? -999,
                    'severity' => 'ERROR',
                    'codeDesc' => 'NO RESPONSE',
                    'message' => $response['error'] ?? 'Errore sconosciuto',
                ]
            ]);
        }
    }

    /**
     * Esegue la chiamata HTTP POST all'API BRT
     * @param array $payload
     * @return array
     */
    protected static function callApi($payload)
    {
        $url = 'https://api.brt.it/rest/v1/shipments/shipment';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpcode == 200 && $result) {
            $data = json_decode($result, true);
            return [
                'success' => true,
                'data' => $data,
            ];
        } else {
            return [
                'success' => false,
                'error' => $error,
                'httpcode' => $httpcode,
                'data' => $result ? json_decode($result, true) : [],
            ];
        }
    }

    /**
     * Decodifica lo stream etichetta (Base64)
     * @param string $stream
     * @return string|false
     */
    public static function decodeLabel($stream)
    {
        return base64_decode($stream);
    }
}

/**
 * Esempio pratico di utilizzo in un controller PrestaShop:
 *
 * use MpSoft\MpBrtApiShipment\Api\Create;
 * use MpSoft\MpBrtApiShipment\Api\Account;
 * use MpSoft\MpBrtApiShipment\Api\LabelParameters;
 * use MpSoft\MpBrtApiShipment\Api\ShipmentRequest;
 *
 * $account = new Account('USERID', 'PASSWORD');
 * $labelParams = new LabelParameters('PDF', 0, 0, '1', '1', '0');
 * $createData = [...]; // tutti i campi della spedizione
 * $request = new ShipmentRequest($account, $createData, 1, $labelParams);
 * $shipmentResponse = Create::sendShipmentRequest($request);
 * if ($shipmentResponse->executionMessage && $shipmentResponse->executionMessage->code === 0) {
 *     // Successo
 *     foreach ($shipmentResponse->labels as $label) {
 *         // $label->stream è la base64 della label
 *     }
 * } else {
 *     // Errore o warning
 *     echo $shipmentResponse->executionMessage->message;
 * }
 */



