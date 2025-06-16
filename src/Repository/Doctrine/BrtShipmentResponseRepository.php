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

namespace MpSoft\MpBrtApiShipment\Repository\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentResponse;

class BrtShipmentResponseRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Salva la response e le label di Bartolini.
     *
     * @param array|string $executionMessage
     * @param array        $response
     *
     * @return array ['success' => bool, 'id_brt_shipment_response' => int|null, 'message' => string]
     */
    public function execute($numericSenderReference, $executionMessage, $response)
    {
        if (!$numericSenderReference) {
            return ['success' => false, 'message' => 'numericSenderReference mancante'];
        }

        $executionMessageJson = is_array($executionMessage) ? json_encode($executionMessage) : $executionMessage;
        $responseJson = is_array($response) ? json_encode($response) : $response;

        // Cerca una spedizione esistente con lo stesso numericSenderReference
        $existing = $this->findBy(['numericSenderReference' => $numericSenderReference]);
        if ($existing && count($existing) > 0) {
            $entity = $existing[0];
            $entity->setDateUpd(new \DateTime());
        } else {
            $entity = new BrtShipmentResponse();
            $entity->setDateAdd(new \DateTime());
            $entity->setDateUpd(new \DateTime());
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
        if (!$entity->getDateUpd()) {
            $entity->setDateUpd(new \DateTime());
        }

        $this->save($entity);

        return [
            'success' => true,
            'id_brt_shipment_response' => $entity->getId(),
            'message' => 'Response salvata con successo',
        ];
    }

    public function getBorderoRowsId($bordero_number = null)
    {
        if (!$bordero_number) {
            $rows = $this->getUnprintedBorderoRows();
            $ids = array_map(fn ($row) => $row->getId(), $rows);

            return $ids;
        }

        $rows = $this->entityManager->getRepository(BrtShipmentResponse::class)->findBy(['borderoNumber' => $bordero_number], ['id' => 'ASC']);
        $ids = array_map(fn ($row) => $row->getId(), $rows);

        return $ids;
    }

    public function getLastBorderoNumber()
    {
        $lastBorderoNumber = $this->entityManager->getRepository(BrtShipmentResponse::class)->findOneBy(['borderoNumber' => 'is NOT NULL'], ['id' => 'ASC']);

        return (int) $lastBorderoNumber->getBorderoNumber();
    }

    public function getUnprintedBorderoRows()
    {
        return $this->entityManager->getRepository(BrtShipmentResponse::class)->findBy(['printed' => false]);
    }

    public function printBorderoRows($bordero_number)
    {
        $rows = $this->entityManager->getRepository(BrtShipmentResponse::class)->findBy(['borderoNumber' => $bordero_number]);
        foreach ($rows as $row) {
            $row->setPrinted(true);
            $this->entityManager->persist($row);
        }
        $this->entityManager->flush();
    }

    public function findBy(array $criteria): array
    {
        return $this->entityManager->getRepository(BrtShipmentResponse::class)->findBy($criteria);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(BrtShipmentResponse::class)->findAll();
    }

    public function remove(BrtShipmentResponse $entity): void
    {
        $this->entityManager->remove($entity);
        $this->entityManager->flush();
    }

    public function find(int $id): ?BrtShipmentResponse
    {
        return $this->entityManager->getRepository(BrtShipmentResponse::class)->find($id);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null)
    {
        return $this->entityManager->getRepository(BrtShipmentResponse::class)->findOneBy($criteria, $orderBy);
    }

    public function findOneOrNullBy(array $criteria)
    {
        return $this->entityManager->getRepository(BrtShipmentResponse::class)->findOneOrNullBy($criteria);
    }

    public function count(array $criteria): int
    {
        return $this->entityManager->getRepository(BrtShipmentResponse::class)->count($criteria);
    }

    public function save(BrtShipmentResponse $entity): void
    {
        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
