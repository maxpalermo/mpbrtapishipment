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

namespace MpSoft\MpBrtApiShipment\Services;

class SaveResponseBrtLabel
{
    private ModelBrtShipmentResponseService $service;

    public function __construct(ModelBrtShipmentResponseService $service)
    {
        $this->service = $service;
    }

    /**
     * Salva la response e le label di Bartolini.
     *
     * @param array|string $executionMessage
     * @param array        $response
     * @param array        $labels
     *
     * @return array ['success' => bool, 'id_brt_shipment_response' => int|null, 'message' => string]
     */
    public function execute($executionMessage, $response, $labels)
    {
        $numericSenderReference = $response['numericSenderReference'] ?? null;
        if (!$numericSenderReference) {
            return ['success' => false, 'message' => 'numericSenderReference mancante'];
        }

        $executionMessageJson = is_array($executionMessage) ? json_encode($executionMessage) : $executionMessage;
        $responseJson = is_array($response) ? json_encode($response) : $response;

        // Cerca una spedizione esistente con lo stesso numericSenderReference
        $existing = $this->service->findBy(['numericSenderReference' => $numericSenderReference]);
        if ($existing && count($existing) > 0) {
            $entity = $existing[0];
            $entity->setDateUpd(new \DateTime());
        } else {
            $entity = new BrtShipmentResponse();
            $entity->setDateAdd(new \DateTime());
        }
        $entity->setCurrentTimeUtc($response['currentTimeUTC'] ?? null);
        $entity->setArrivalTerminal($response['arrivalTerminal'] ?? null);
        $entity->setArrivalDepot($response['arrivalDepot'] ?? null);
        $entity->setDeliveryZone($response['deliveryZone'] ?? null);
        $entity->setParcelNumberFrom($response['parcelNumberFrom'] ?? null);
        $entity->setParcelNumberTo($response['parcelNumberTo'] ?? null);
        $entity->setDepartureDepot($response['departureDepot'] ?? null);
        $entity->setSeriesNumber($response['seriesNumber'] ?? null);
        $entity->setServiceType($response['serviceType'] ?? null);
        $entity->setConsigneeCompanyName($response['consigneeCompanyName'] ?? null);
        $entity->setConsigneeAddress($response['consigneeAddress'] ?? null);
        $entity->setConsigneeZipCode($response['consigneeZIPCode'] ?? null);
        $entity->setConsigneeCity($response['consigneeCity'] ?? null);
        $entity->setConsigneeProvinceAbbreviation($response['consigneeProvinceAbbreviation'] ?? null);
        $entity->setConsigneeCountryAbbreviationIsoAlpha2($response['consigneeCountryAbbreviationBRT'] ?? null);
        $entity->setConsigneeContactName($response['consigneeContactName'] ?? null);
        $entity->setConsigneeTelephone($response['consigneeTelephone'] ?? null);
        $entity->setConsigneeMobilePhoneNumber($response['consigneeMobilePhoneNumber'] ?? null);
        $entity->setConsigneeEmail($response['consigneeEmail'] ?? null);
        $entity->setCashOnDelivery(isset($response['cashOnDelivery']) ? (float) $response['cashOnDelivery'] : null);
        $entity->setNumberOfParcels(isset($response['numberOfParcels']) ? (int) $response['numberOfParcels'] : null);
        $entity->setWeightKg(isset($response['weightKG']) ? (float) $response['weightKG'] : null);
        $entity->setVolumeM3(isset($response['volumeM3']) ? (float) $response['volumeM3'] : null);
        $entity->setNumericSenderReference($numericSenderReference);
        $entity->setAlphanumericSenderReference($response['alphanumericSenderReference'] ?? null);
        $entity->setSenderCompanyName($response['senderCompanyName'] ?? null);
        $entity->setSenderProvinceAbbreviation($response['senderProvinceAbbreviation'] ?? null);
        $entity->setDisclaimer($response['disclaimer'] ?? null);
        $entity->setResponse($responseJson);
        $entity->setExecutionMessage($executionMessageJson);
        $entity->setBorderoNumber(null);
        $entity->setBorderoDate(null);
        $entity->setPrinted(null);
        if (!$entity->getDateAdd()) {
            $entity->setDateAdd(new \DateTime());
        }

        $this->service->save($entity);

        return [
            'success' => true,
            'id_brt_shipment_response' => $entity->getId(),
            'message' => 'Response salvata con successo',
        ];
    }
}
