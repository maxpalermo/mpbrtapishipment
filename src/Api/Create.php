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

use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel;

class Create
{
    /**
     * Invia una spedizione a BRT passando direttamente tutti i dati necessari (object style).
     *
     * @param string|null $env Ambiente ('real' o 'sandbox'), opzionale solo se vuoi cambiare endpoint in base all'ambiente
     */
    public static function sendShipmentRequest(ShipmentRequest $shipmentRequest, $env = null): ShipmentResponse
    {
        $numericSenderReference = $shipmentRequest->createData['numericSenderReference'] ?? 0;
        $alphanumericSenderReference = $shipmentRequest->createData['alphanumericSenderReference'] ?? '';
        $cashOnDelivery = (float) ($shipmentRequest->createData['cashOnDelivery'] ?? 0);

        $payload = $shipmentRequest->toArray();
        $response = self::callApi($payload);
        $httpCode = $response['httpcode'] ?? 0;

        $error = '';
        if (500 == $httpCode) {
            $error = self::extractError($response['error'] ?? '');

            $shipmentResponse = new ShipmentResponse([
                'executionMessage' => [
                    'code' => -999,
                    'severity' => 'ERROR',
                    'codeDesc' => 'Errore nella creazione della spedizione',
                    'message' => $error,
                ],
            ]);

            return $shipmentResponse;
        }

        // Interpreta la risposta come ShipmentResponse
        $shipmentResponse = null;
        if (!empty($response['data']['createResponse'])) {
            $shipmentResponse = new ShipmentResponse($response['data']['createResponse']);

            if (!$shipmentResponse->executionMessage->hasError()) {
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
                $modelResponse->cash_on_delivery = $cashOnDelivery;
                $modelResponse->number_of_parcels = $shipmentResponse->numberOfParcels;
                $modelResponse->weight_kg = $shipmentResponse->weightKG;
                $modelResponse->volume_m3 = $shipmentResponse->volumeM3;
                $modelResponse->numeric_sender_reference = $numericSenderReference;
                $modelResponse->alphanumeric_sender_reference = $alphanumericSenderReference;
                $modelResponse->sender_company_name = $shipmentResponse->senderCompanyName;
                $modelResponse->sender_province_abbreviation = $shipmentResponse->senderProvinceAbbreviation;
                $modelResponse->disclaimer = $shipmentResponse->disclaimer;
                $modelResponse->execution_message = json_encode($shipmentResponse->executionMessage);
                $modelResponse->save();

                // Salva le label (se presenti)
                if (is_array($shipmentResponse->labels) && !empty($shipmentResponse->labels)) {
                    foreach ($shipmentResponse->getLabels() as $key => $label) {
                        $modelLabel = new ModelBrtShipmentResponseLabel();
                        $modelLabel->numeric_sender_reference = $numericSenderReference;
                        $modelLabel->alphanumeric_sender_reference = $alphanumericSenderReference;
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
            }
        } else {
            if (is_array($response) || is_object($response)) {
                $response = json_encode($response);
            }
            // Risposta di errore generica
            $shipmentResponse = new ShipmentResponse([
                'executionMessage' => [
                    'code' => $response['data']['executionMessage']['code'] ?? -999,
                    'severity' => 'ERROR',
                    'codeDesc' => $response,
                    'message' => $response['error'] ?? 'Errore sconosciuto',
                ],
            ]);
        }

        return $shipmentResponse;
    }

    protected static function extractError($response)
    {
        // 1. Cerca la sezione <div id="code">
        if (preg_match('/<div id="code">(.*?)<\/div>/is', $response, $matches)) {
            $codeBlock = strip_tags($matches[1]);
            // 2. Trova la prima riga significativa (Exception o Unrecognized)
            if (preg_match('/((Exception|Unrecognized).*?)(\n|$)/', $codeBlock, $errMatch)) {
                return trim($errMatch[1]);
            }

            // Se non trova, restituisci tutto il blocco code
            return trim($codeBlock);
        }
        // 3. Fallback: rimuovi html e cerca la riga con Exception
        $plain = strip_tags($response);
        if (preg_match('/((Exception|Unrecognized).*?)(\n|$)/', $plain, $errMatch)) {
            return trim($errMatch[1]);
        }

        // 4. Fallback generico
        return 'Errore non identificato';
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
            if (is_array($result) || is_object($result) && empty($error)) {
                $error = json_encode($result);
            }
            if (empty($error) && is_string($result)) {
                $error = $result;
            }

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
