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

namespace MpSoft\MpBrtApiShipment\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 *
 * @ORM\Entity()
 */
class BrtShipmentResponse
{
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer", name="id_brt_shipment_response")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="current_time_utc")
     */
    private ?string $currentTimeUtc = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="arrival_terminal")
     */
    private ?string $arrivalTerminal = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="arrival_depot")
     */
    private ?string $arrivalDepot = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="delivery_zone")
     */
    private ?string $deliveryZone = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="parcel_number_from")
     */
    private ?string $parcelNumberFrom = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="parcel_number_to")
     */
    private ?string $parcelNumberTo = null;
    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="departure_depot")
     */
    private ?string $departureDepot = null;
    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="series_number")
     */
    private ?string $seriesNumber = null;
    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="service_type")
     */
    private ?string $serviceType = null;
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="consignee_company_name")
     */
    private ?string $consigneeCompanyName = null;
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="consignee_address")
     */
    private ?string $consigneeAddress = null;
    /**
     * @ORM\Column(type="string", length=32, nullable=true, name="consignee_zip_code")
     */
    private ?string $consigneeZipCode = null;
    /**
     * @ORM\Column(type="string", length=128, nullable=true, name="consignee_city")
     */
    private ?string $consigneeCity = null;
    /**
     * @ORM\Column(type="string", length=8, nullable=true, name="consignee_province_abbreviation")
     */
    private ?string $consigneeProvinceAbbreviation = null;
    /**
     * @ORM\Column(type="string", length=8, nullable=true, name="consignee_country_abbreviation_iso_alpha_2")
     */
    private ?string $consigneeCountryAbbreviationIsoAlpha2 = null;
    /**
     * @ORM\Column(type="string", length=35, nullable=true, name="consignee_contact_name")
     */
    private ?string $consigneeContactName = null;
    /**
     * @ORM\Column(type="string", length=16, nullable=true, name="consignee_telephone")
     */
    private ?string $consigneeTelephone = null;
    /**
     * @ORM\Column(type="string", length=16, nullable=true, name="consignee_mobile_phone_number")
     */
    private ?string $consigneeMobilePhoneNumber = null;
    /**
     * @ORM\Column(type="string", length=70, nullable=true, name="consignee_email")
     */
    private ?string $consigneeEmail = null;
    /**
     * @ORM\Column(type="float", nullable=true, name="cash_on_delivery")
     */
    private ?float $cashOnDelivery = null;
    /**
     * @ORM\Column(type="integer", nullable=true, options={"default"=0}, name="number_of_parcels")
     */
    private ?int $numberOfParcels = 0;
    /**
     * @ORM\Column(type="float", nullable=true, options={"default"=0}, name="weight_kg")
     */
    private ?float $weightKg = 0;
    /**
     * @ORM\Column(type="float", nullable=true, options={"default"=0}, name="volume_m3")
     */
    private ?float $volumeM3 = 0;
    /**
     * @ORM\Column(type="string", length=15, nullable=true, name="numeric_sender_reference")
     */
    private ?string $numericSenderReference = null;
    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="alphanumeric_sender_reference")
     */
    private ?string $alphanumericSenderReference = null;
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="sender_company_name")
     */
    private ?string $senderCompanyName = null;
    /**
     * @ORM\Column(type="string", length=8, nullable=true, name="sender_province_abbreviation")
     */
    private ?string $senderProvinceAbbreviation = null;
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="disclaimer")
     */
    private ?string $disclaimer = null;
    /**
     * @ORM\Column(type="text", nullable=true, name="response")
     */
    private ?string $response = null;
    /**
     * @ORM\Column(type="text", nullable=true, name="execution_message")
     */
    private ?string $executionMessage = null;
    /**
     * @ORM\Column(type="integer", nullable=true, name="bordero_number")
     */
    private ?int $borderoNumber = null;
    /**
     * @ORM\Column(type="integer", nullable=true, name="bordero_date")
     */
    private ?int $borderoDate = null;
    /**
     * @ORM\Column(type="boolean", nullable=true, name="printed")
     */
    private ?bool $printed = null;
    /**
     * @ORM\Column(type="datetime", nullable=true, name="date_add")
     */
    private ?\DateTimeInterface $dateAdd = null;
    /**
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"}, name="date_upd")
     */
    private ?\DateTimeInterface $dateUpd = null;

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'current_time_utc' => $this->getCurrentTimeUtc(),
            'arrival_terminal' => $this->getArrivalTerminal(),
            'arrival_depot' => $this->getArrivalDepot(),
            'delivery_zone' => $this->getDeliveryZone(),
            'parcel_number_from' => $this->getParcelNumberFrom(),
            'parcel_number_to' => $this->getParcelNumberTo(),
            'departure_depot' => $this->getDepartureDepot(),
            'series_number' => $this->getSeriesNumber(),
            'service_type' => $this->getServiceType(),
            'consignee_company_name' => $this->getConsigneeCompanyName(),
            'consignee_address' => $this->getConsigneeAddress(),
            'consignee_zip_code' => $this->getConsigneeZipCode(),
            'consignee_city' => $this->getConsigneeCity(),
            'consignee_province_abbreviation' => $this->getConsigneeProvinceAbbreviation(),
            'consignee_country_abbreviation_iso_alpha_2' => $this->getConsigneeCountryAbbreviationIsoAlpha2(),
            'consignee_contact_name' => $this->getConsigneeContactName(),
            'consignee_telephone' => $this->getConsigneeTelephone(),
            'consignee_mobile_phone_number' => $this->getConsigneeMobilePhoneNumber(),
            'consignee_email' => $this->getConsigneeEmail(),
            'cash_on_delivery' => $this->getCashOnDelivery(),
            'number_of_parcels' => $this->getNumberOfParcels(),
            'weight_kg' => $this->getWeightKg(),
            'volume_m3' => $this->getVolumeM3(),
            'numeric_sender_reference' => $this->getNumericSenderReference(),
            'alphanumeric_sender_reference' => $this->getAlphanumericSenderReference(),
            'sender_company_name' => $this->getSenderCompanyName(),
            'sender_province_abbreviation' => $this->getSenderProvinceAbbreviation(),
            'disclaimer' => $this->getDisclaimer(),
            'response' => $this->getResponse(),
            'execution_message' => $this->getExecutionMessage(),
            'bordero_number' => $this->getBorderoNumber(),
            'bordero_date' => $this->getBorderoDate(),
            'printed' => $this->getPrinted(),
            'date_add' => $this->getDateAdd() ? $this->getDateAdd()->format('Y-m-d H:i:s') : null,
            'date_upd' => $this->getDateUpd() ? $this->getDateUpd()->format('Y-m-d H:i:s') : null,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCurrentTimeUtc(): ?string
    {
        return $this->currentTimeUtc;
    }

    public function setCurrentTimeUtc(?string $currentTimeUtc): self
    {
        $this->currentTimeUtc = $currentTimeUtc;

        return $this;
    }

    public function getArrivalTerminal(): ?string
    {
        return $this->arrivalTerminal;
    }

    public function setArrivalTerminal(?string $arrivalTerminal): self
    {
        $this->arrivalTerminal = $arrivalTerminal;

        return $this;
    }

    public function getArrivalDepot(): ?string
    {
        return $this->arrivalDepot;
    }

    public function setArrivalDepot(?string $arrivalDepot): self
    {
        $this->arrivalDepot = $arrivalDepot;

        return $this;
    }

    public function getDeliveryZone(): ?string
    {
        return $this->deliveryZone;
    }

    public function setDeliveryZone(?string $deliveryZone): self
    {
        $this->deliveryZone = $deliveryZone;

        return $this;
    }

    public function getParcelNumberFrom(): ?string
    {
        return $this->parcelNumberFrom;
    }

    public function setParcelNumberFrom(?string $parcelNumberFrom): self
    {
        $this->parcelNumberFrom = $parcelNumberFrom;

        return $this;
    }

    public function getParcelNumberTo(): ?string
    {
        return $this->parcelNumberTo;
    }

    public function setParcelNumberTo(?string $parcelNumberTo): self
    {
        $this->parcelNumberTo = $parcelNumberTo;

        return $this;
    }

    public function getDepartureDepot(): ?string
    {
        return $this->departureDepot;
    }

    public function setDepartureDepot(?string $departureDepot): self
    {
        $this->departureDepot = $departureDepot;

        return $this;
    }

    public function getSeriesNumber(): ?string
    {
        return $this->seriesNumber;
    }

    public function setSeriesNumber(?string $seriesNumber): self
    {
        $this->seriesNumber = $seriesNumber;

        return $this;
    }

    public function getServiceType(): ?string
    {
        return $this->serviceType;
    }

    public function setServiceType(?string $serviceType): self
    {
        $this->serviceType = $serviceType;

        return $this;
    }

    public function getConsigneeCompanyName(): ?string
    {
        return $this->consigneeCompanyName;
    }

    public function setConsigneeCompanyName(?string $consigneeCompanyName): self
    {
        $this->consigneeCompanyName = $consigneeCompanyName;

        return $this;
    }

    public function getConsigneeAddress(): ?string
    {
        return $this->consigneeAddress;
    }

    public function setConsigneeAddress(?string $consigneeAddress): self
    {
        $this->consigneeAddress = $consigneeAddress;

        return $this;
    }

    public function getConsigneeZipCode(): ?string
    {
        return $this->consigneeZipCode;
    }

    public function setConsigneeZipCode(?string $consigneeZipCode): self
    {
        $this->consigneeZipCode = $consigneeZipCode;

        return $this;
    }

    public function getConsigneeCity(): ?string
    {
        return $this->consigneeCity;
    }

    public function setConsigneeCity(?string $consigneeCity): self
    {
        $this->consigneeCity = $consigneeCity;

        return $this;
    }

    public function getConsigneeProvinceAbbreviation(): ?string
    {
        return $this->consigneeProvinceAbbreviation;
    }

    public function setConsigneeProvinceAbbreviation(?string $consigneeProvinceAbbreviation): self
    {
        $this->consigneeProvinceAbbreviation = $consigneeProvinceAbbreviation;

        return $this;
    }

    public function getConsigneeCountryAbbreviationIsoAlpha2(): ?string
    {
        return $this->consigneeCountryAbbreviationIsoAlpha2;
    }

    public function setConsigneeCountryAbbreviationIsoAlpha2(?string $consigneeCountryAbbreviationIsoAlpha2): self
    {
        $this->consigneeCountryAbbreviationIsoAlpha2 = $consigneeCountryAbbreviationIsoAlpha2;

        return $this;
    }

    public function getConsigneeContactName(): ?string
    {
        return $this->consigneeContactName;
    }

    public function setConsigneeContactName(?string $consigneeContactName): self
    {
        $this->consigneeContactName = $consigneeContactName;

        return $this;
    }

    public function getConsigneeTelephone(): ?string
    {
        return $this->consigneeTelephone;
    }

    public function setConsigneeTelephone(?string $consigneeTelephone): self
    {
        $this->consigneeTelephone = $consigneeTelephone;

        return $this;
    }

    public function getConsigneeMobilePhoneNumber(): ?string
    {
        return $this->consigneeMobilePhoneNumber;
    }

    public function setConsigneeMobilePhoneNumber(?string $consigneeMobilePhoneNumber): self
    {
        $this->consigneeMobilePhoneNumber = $consigneeMobilePhoneNumber;

        return $this;
    }

    public function getConsigneeEmail(): ?string
    {
        return $this->consigneeEmail;
    }

    public function setConsigneeEmail(?string $consigneeEmail): self
    {
        $this->consigneeEmail = $consigneeEmail;

        return $this;
    }

    public function getCashOnDelivery(): ?float
    {
        return $this->cashOnDelivery;
    }

    public function setCashOnDelivery(?float $cashOnDelivery): self
    {
        $this->cashOnDelivery = $cashOnDelivery;

        return $this;
    }

    public function getNumberOfParcels(): ?int
    {
        return $this->numberOfParcels;
    }

    public function setNumberOfParcels(?int $numberOfParcels): self
    {
        $this->numberOfParcels = $numberOfParcels;

        return $this;
    }

    public function getWeightKg(): ?float
    {
        return $this->weightKg;
    }

    public function setWeightKg(?float $weightKg): self
    {
        $this->weightKg = $weightKg;

        return $this;
    }

    public function getVolumeM3(): ?float
    {
        return $this->volumeM3;
    }

    public function setVolumeM3(?float $volumeM3): self
    {
        $this->volumeM3 = $volumeM3;

        return $this;
    }

    public function getNumericSenderReference(): ?string
    {
        return $this->numericSenderReference;
    }

    public function setNumericSenderReference(?string $numericSenderReference): self
    {
        $this->numericSenderReference = $numericSenderReference;

        return $this;
    }

    public function getAlphanumericSenderReference(): ?string
    {
        return $this->alphanumericSenderReference;
    }

    public function setAlphanumericSenderReference(?string $alphanumericSenderReference): self
    {
        $this->alphanumericSenderReference = $alphanumericSenderReference;

        return $this;
    }

    public function getSenderCompanyName(): ?string
    {
        return $this->senderCompanyName;
    }

    public function setSenderCompanyName(?string $senderCompanyName): self
    {
        $this->senderCompanyName = $senderCompanyName;

        return $this;
    }

    public function getSenderProvinceAbbreviation(): ?string
    {
        return $this->senderProvinceAbbreviation;
    }

    public function setSenderProvinceAbbreviation(?string $senderProvinceAbbreviation): self
    {
        $this->senderProvinceAbbreviation = $senderProvinceAbbreviation;

        return $this;
    }

    public function getDisclaimer(): ?string
    {
        return $this->disclaimer;
    }

    public function setDisclaimer(?string $disclaimer): self
    {
        $this->disclaimer = $disclaimer;

        return $this;
    }

    public function getResponse(): ?string
    {
        return $this->response;
    }

    public function setResponse(?string $response): self
    {
        $this->response = $response;

        return $this;
    }

    public function getExecutionMessage(): ?string
    {
        return $this->executionMessage;
    }

    public function setExecutionMessage(?string $executionMessage): self
    {
        $this->executionMessage = $executionMessage;

        return $this;
    }

    public function getBorderoNumber(): ?int
    {
        return $this->borderoNumber;
    }

    public function setBorderoNumber(?int $borderoNumber): self
    {
        $this->borderoNumber = $borderoNumber;

        return $this;
    }

    public function getBorderoDate(): ?int
    {
        return $this->borderoDate;
    }

    public function setBorderoDate(?int $borderoDate): self
    {
        $this->borderoDate = $borderoDate;

        return $this;
    }

    public function getPrinted(): ?bool
    {
        return $this->printed;
    }

    public function setPrinted(?bool $printed): self
    {
        $this->printed = $printed;

        return $this;
    }

    public function getDateAdd(): \DateTimeInterface
    {
        if (!$this->dateAdd) {
            $this->dateAdd = new \DateTime();
        }

        return $this->dateAdd;
    }

    public function setDateAdd($dateAdd): self
    {
        if (is_string($dateAdd)) {
            $dateAdd = new \DateTime($dateAdd);
        }
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getDateUpd(): \DateTimeInterface
    {
        if (!$this->dateUpd) {
            $this->dateUpd = new \DateTime();
        }

        return $this->dateUpd;
    }

    public function setDateUpd($dateUpd): self
    {
        if (is_string($dateUpd)) {
            $dateUpd = new \DateTime($dateUpd);
        }
        $this->dateUpd = $dateUpd;

        return $this;
    }

    public static function getSqlCreateStatement()
    {
        $prefix = _DB_PREFIX_;

        return "CREATE TABLE IF NOT EXISTS `{$prefix}brt_shipment_response` (
            `id_brt_shipment_response` int(11) NOT NULL AUTO_INCREMENT,
            `current_time_utc` varchar(64) DEFAULT NULL,
            `arrival_terminal` varchar(64) DEFAULT NULL,
            `arrival_depot` varchar(64) DEFAULT NULL,
            `delivery_zone` varchar(64) DEFAULT NULL,
            `parcel_number_from` varchar(64) DEFAULT NULL,
            `parcel_number_to` varchar(64) DEFAULT NULL,
            `departure_depot` varchar(64) DEFAULT NULL,
            `series_number` varchar(64) DEFAULT NULL,
            `service_type` varchar(64) DEFAULT NULL,
            `consignee_company_name` varchar(255) DEFAULT NULL,
            `consignee_address` varchar(255) DEFAULT NULL,
            `consignee_zip_code` varchar(32) DEFAULT NULL,
            `consignee_city` varchar(128) DEFAULT NULL,
            `consignee_province_abbreviation` varchar(8) DEFAULT NULL,
            `consignee_country_abbreviation_iso_alpha_2` varchar(8) DEFAULT NULL,
            `consignee_contact_name` varchar(35) DEFAULT NULL,
            `consignee_telephone` varchar(16) DEFAULT NULL,
            `consignee_mobile_phone_number` varchar(16) DEFAULT NULL,
            `consignee_email` varchar(70) DEFAULT NULL,
            `cash_on_delivery` float DEFAULT NULL,
            `number_of_parcels` int(11) DEFAULT 0,
            `weight_kg` float DEFAULT 1,
            `volume_m3` float DEFAULT 0,
            `numeric_sender_reference` varchar(15) DEFAULT NULL,
            `alphanumeric_sender_reference` varchar(64) DEFAULT NULL,
            `sender_company_name` varchar(255) DEFAULT NULL,
            `sender_province_abbreviation` varchar(8) DEFAULT NULL,
            `disclaimer` varchar(255) DEFAULT NULL,
            `response` text DEFAULT NULL,
            `execution_message` text DEFAULT NULL,
            `bordero_number` int(11) DEFAULT NULL,
            `bordero_date` int(11) DEFAULT NULL,
            `printed` tinyint(1) DEFAULT NULL,
            `date_add` datetime DEFAULT NULL,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_brt_shipment_response`),
            KEY `numeric_sender_reference` (`numeric_sender_reference`),
            KEY `alphanumeric_sender_reference` (`alphanumeric_sender_reference`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
}
