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

use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentRequest;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel;

class Create
{
    /**
     * Invia una spedizione a BRT usando un ordine PrestaShop.
     *
     * @param int         $orderId ID ordine PrestaShop
     * @param array       $config  Configurazione dati fissi BRT (senza credenziali)
     * @param string|null $env     Ambiente ('real' o 'sandbox'), se null prende da config PrestaShop
     *
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
        $account = (new BrtAuthManager())->getAccount();
        $weightKg = $order->getTotalWeight();
        if (!$weightKg) {
            $weightKg = 1;
        }
        $shipmentData = [
            'network' => $config['network'] ?? '',
            'departureDepot' => $config['departureDepot'],
            'senderCustomerCode' => $account->userID,
            'deliveryFreightTypeCode' => $config['deliveryFreightTypeCode'] ?? 'DAP',
            'consigneeCompanyName' => $address->company ?: ($customer->firstname.' '.$customer->lastname),
            'consigneeAddress' => $address->address1,
            'consigneeCountryAbbreviationISOAlpha2' => $countryIso,
            'consigneeZIPCode' => $address->postcode,
            'consigneeCity' => $address->city,
            'consigneeProvinceAbbreviation' => $province,
            'consigneeContactName' => $customer->firstname.' '.$customer->lastname,
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
            'weightKG' => $weightKg,
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
        if (null === $env) {
            $env = \Configuration::get('BRT_ENVIRONMENT') ?: BrtAuthManager::ENV_REAL;
        }
        $authManager = new BrtAuthManager();
        $account = $authManager->getAccount();
        $labelObj = isset($config['labelParameters']) ? LabelParameters::fromArray($config['labelParameters']) : new LabelParameters();
        $isLabelRequired = $config['isLabelRequired'] ?? 1;
        $shipmentRequest = new ShipmentRequest($account, $shipmentData, $isLabelRequired, $labelObj);

        return self::sendShipmentRequest($shipmentRequest);
    }

    /**
     * Invia una spedizione a BRT passando direttamente tutti i dati necessari (object style).
     *
     * @param string|null $env Ambiente ('real' o 'sandbox'), opzionale solo se vuoi cambiare endpoint in base all'ambiente
     */
    public static function sendShipmentRequest(ShipmentRequest $shipmentRequest, $env = null): ShipmentResponse
    {
        // Salva la richiesta
        $orderId = isset($shipmentRequest->createData['numericSenderReference']) ? $shipmentRequest->createData['numericSenderReference'] : 0;
        $modelRequest = new ModelBrtShipmentRequest();
        $modelRequest->order_id = $orderId;
        $modelRequest->numeric_sender_reference = $shipmentRequest->createData['numericSenderReference'];
        $modelRequest->account_json = json_encode($shipmentRequest->account->toArray());
        $modelRequest->create_data_json = json_encode($shipmentRequest->createData);
        $modelRequest->is_label_required = (int) $shipmentRequest->isLabelRequired;
        $modelRequest->label_parameters_json = json_encode($shipmentRequest->labelParameters->toArray());
        $modelRequest->date_add = date('Y-m-d H:i:s');
        $modelRequest->date_upd = date('Y-m-d H:i:s');
        $modelRequest->save();

        $payload = $shipmentRequest->toArray();
        $response = self::callApi($payload);

        // Interpreta la risposta come ShipmentResponse
        $shipmentResponse = null;
        if (!empty($response['data']['createResponse'])) {
            $shipmentResponse = new ShipmentResponse($response['data']['createResponse']);
        } else {
            // Risposta di errore generica
            $shipmentResponse = new ShipmentResponse([
                'executionMessage' => [
                    'code' => $response['data']['executionMessage']['code'] ?? -999,
                    'severity' => 'ERROR',
                    'codeDesc' => 'NO RESPONSE',
                    'message' => $response['error'] ?? 'Errore sconosciuto',
                ],
            ]);
        }

        // Salva la response
        $modelResponse = new ModelBrtShipmentResponse();
        $modelResponse->current_time_utc = $shipmentResponse->currentTimeUTC;
        $modelResponse->arrival_terminal = $shipmentResponse->arrivalTerminal;
        $modelResponse->arrival_depot = $shipmentResponse->arrivalDepot;
        $modelResponse->delivery_zone = $shipmentResponse->deliveryZone;
        $modelResponse->parcel_number_from = $shipmentResponse->parcelNumberFrom;
        $modelResponse->parcel_number_to = $shipmentResponse->parcelNumberTo;
        $modelResponse->departure_depot = $shipmentResponse->departureDepot;
        $modelResponse->series_number = $shipmentResponse->seriesNumber;
        $modelResponse->service_type = $shipmentResponse->serviceType;
        $modelResponse->consignee_company_name = $shipmentResponse->consigneeCompanyName;
        $modelResponse->consignee_address = $shipmentResponse->consigneeAddress;
        $modelResponse->consignee_zip_code = $shipmentResponse->consigneeZIPCode;
        $modelResponse->consignee_city = $shipmentResponse->consigneeCity;
        $modelResponse->consignee_province_abbreviation = $shipmentResponse->consigneeProvinceAbbreviation;
        $modelResponse->consignee_country_abbreviation_brt = $shipmentResponse->consigneeCountryAbbreviationBRT;
        $modelResponse->number_of_parcels = $shipmentResponse->numberOfParcels;
        $modelResponse->weight_kg = $shipmentResponse->weightKG;
        $modelResponse->volume_m3 = $shipmentResponse->volumeM3;
        $modelResponse->numeric_sender_reference = $shipmentRequest->createData['numericSenderReference'];
        $modelResponse->alphanumeric_sender_reference = $shipmentResponse->alphanumericSenderReference;
        $modelResponse->sender_company_name = $shipmentResponse->senderCompanyName;
        $modelResponse->sender_province_abbreviation = $shipmentResponse->senderProvinceAbbreviation;
        $modelResponse->disclaimer = $shipmentResponse->disclaimer;
        $modelResponse->execution_message = json_encode($shipmentResponse->executionMessage);
        $modelResponse->save();

        // Salva le label (se presenti)
        if (is_array($shipmentResponse->labels) && !empty($shipmentResponse->labels)) {
            foreach ($shipmentResponse->getLabels() as $key => $label) {
                $modelLabel = new ModelBrtShipmentResponseLabel();
                $modelLabel->id_brt_shipment_response = $modelResponse->id;
                $modelLabel->number = $key + 1;
                $modelLabel->data_length = $label->dataLength ?? 0;
                $modelLabel->parcel_id = $label->parcelID ?? '';
                $modelLabel->stream = $label->stream ?? '';
                $modelLabel->stream_digital_label = $label->streamDigitalLabel ?? '';
                $modelLabel->parcel_number_geo_post = $label->parcelNumberGeoPost ?? '';
                $modelLabel->tracking_by_parcel_id = $label->trackingByParcelId ?? '';
                $modelLabel->format = \Configuration::get('BRT_LABEL_FORMAT') ?? 'BASE64';
                $modelLabel->save();
            }
        }

        return $shipmentResponse;
    }

    /**
     * Esegue la chiamata HTTP POST all'API BRT.
     *
     * @param array $payload
     *
     * @return array
     */
    protected static function callApi($payload)
    {
        // elimino id_order dall'array
        if (isset($payload['createData']['id_order'])) {
            unset($payload['createData']['id_order']);
        }

        try {
            // controllo peso e misure
            if ($payload['createData']['weightKG'] <= 0) {
                $payload['createData']['weightKG'] = 1;
            }
            if ($payload['createData']['numberOfParcels'] <= 0) {
                $payload['createData']['numberOfParcels'] = 1;
            }
        } catch (\Exception $ex) {
            return [
                'success' => false,
                'error' => $ex->getMessage(),
            ];
        }

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

        if (200 == $httpcode && $result) {
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
     * Decodifica lo stream etichetta (Base64).
     *
     * @param string $stream
     *
     * @return string|false
     */
    public static function decodeLabel($stream)
    {
        return base64_decode($stream);
    }
}

/*
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
