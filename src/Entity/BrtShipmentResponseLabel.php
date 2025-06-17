<?php

namespace MpSoft\MpBrtApiShipment\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 *
 * @ORM\Entity()
 */
class BrtShipmentResponseLabel
{
    /**
     * Identificativo univoco dell'etichetta di risposta di spedizione.
     */
    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @ORM\Column(type="integer", name="id_brt_shipment_response_label")
     */
    private int $id;

    /**
     * Risposta di spedizione a cui appartiene l'etichetta.
     */
    /**
     * @ORM\Column(type="integer", name="id_brt_shipment_response")
     */
    private $idBrtShipmentResponse;

    /**
     * Riferimento numerico del mittente.
     */
    /**
     * @ORM\Column(type="string", length=15, name="numeric_sender_reference")
     */
    private string $numericSenderReference;

    /**
     * @ORM\Column(type="string", length=32, nullable=true, name="alphanumeric_sender_reference")
     */
    private ?string $alphanumericSenderReference = null;

    /**
     * @ORM\Column(type="integer", name="number")
     */
    private int $number;

    /**
     * Data e ora della misurazione.
     */
    /**
     * @ORM\Column(type="datetime", name="measure_date", nullable=true)
     */
    private ?\DateTimeInterface $measureDate = null;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private int $x = 0;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private int $y = 0;

    /**
     * @ORM\Column(type="integer", options={"default":0})
     */
    private int $z = 0;

    public function getMeasureDate(): ?\DateTimeInterface
    {
        return $this->measureDate;
    }

    public function setMeasureDate($measureDate): self
    {
        if (is_string($measureDate)) {
            $measureDate = new \DateTime($measureDate);
        }
        $this->measureDate = $measureDate;
        return $this;
    }

    /**
     * @ORM\Column(type="decimal", precision=5, scale=1, options={"default":1.0})
     */
    private float $weight = 1.0;

    /**
     * @ORM\Column(type="decimal", precision=5, scale=3, options={"default":0.000})
     */
    private float $volume = 0.000;

    /**
     * @ORM\Column(type="string", length=16, nullable=true, name="fiscal_id")
     */
    private ?string $fiscalId = null;

    /**
     * @ORM\Column(type="boolean", nullable=true, name="p_flag")
     */
    private ?bool $pFlag = null;

    /**
     * @ORM\Column(type="integer", name="data_length")
     */
    private int $dataLength;

    /**
     * @ORM\Column(type="string", length=64, nullable=true  , name="parcel_id")
     */
    private ?string $parcelId = null;

    /**
     * @ORM\Column(type="text", nullable=true, name="stream")
     */
    private ?string $stream = null;

    /**
     * @ORM\Column(type="text", nullable=true, name="stream_digital_label")
     */
    private ?string $streamDigitalLabel = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="parcel_number_geo_post")
     */
    private ?string $parcelNumberGeoPost = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true, name="tracking_by_parcel_id")
     */
    private ?string $trackingByParcelId = null;

    /**
     * @ORM\Column(type="string", length=16, nullable=true, name="format")
     */
    private ?string $format = null;

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'idBrtShipmentResponse' => $this->getIdBrtShipmentResponse(),
            'numeric_sender_reference' => $this->getNumericSenderReference(),
            'alphanumeric_sender_reference' => $this->getAlphanumericSenderReference(),
            'number' => $this->getNumber(),
            'measure_date' => $this->getMeasureDate(),
            'x' => $this->getX(),
            'y' => $this->getY(),
            'z' => $this->getZ(),
            'weight' => $this->getWeight(),
            'volume' => $this->getVolume(),
            'fiscal_id' => $this->getFiscalId(),
            'p_flag' => $this->getPFlag(),
            'data_length' => $this->getDataLength(),
            'parcel_id' => $this->getParcelId(),
            'stream' => $this->getStream(),
            'stream_digital_label' => $this->getStreamDigitalLabel(),
            'parcel_number_geo_post' => $this->getParcelNumberGeoPost(),
            'tracking_by_parcel_id' => $this->getTrackingByParcelId(),
            'format' => $this->getFormat(),
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIdBrtShipmentResponse()
    {
        return $this->idBrtShipmentResponse;
    }

    public function setIdBrtShipmentResponse($idBrtShipmentResponse): self
    {
        $this->idBrtShipmentResponse = $idBrtShipmentResponse;

        return $this;
    }

    public function getNumericSenderReference(): string
    {
        return $this->numericSenderReference;
    }

    public function setNumericSenderReference(string $val): self
    {
        $this->numericSenderReference = $val;

        return $this;
    }

    public function getAlphanumericSenderReference(): ?string
    {
        return $this->alphanumericSenderReference;
    }

    public function setAlphanumericSenderReference(?string $val): self
    {
        $this->alphanumericSenderReference = $val;

        return $this;
    }

    public function getNumber(): int
    {
        return $this->number;
    }

    public function setNumber(int $val): self
    {
        $this->number = $val;

        return $this;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function setX(int $val): self
    {
        $this->x = $val;

        return $this;
    }

    public function getY(): int
    {
        return $this->y;
    }

    public function setY(int $val): self
    {
        $this->y = $val;

        return $this;
    }

    public function getZ(): int
    {
        return $this->z;
    }

    public function setZ(int $val): self
    {
        $this->z = $val;

        return $this;
    }

    public function getWeight(): float
    {
        return $this->weight;
    }

    public function setWeight(float $val): self
    {
        $this->weight = $val;

        return $this;
    }

    public function getVolume(): float
    {
        return $this->volume;
    }

    public function setVolume(float $val): self
    {
        $this->volume = $val;

        return $this;
    }

    public function getFiscalId(): ?string
    {
        return $this->fiscalId;
    }

    public function setFiscalId(?string $val): self
    {
        $this->fiscalId = $val;

        return $this;
    }

    public function getPFlag(): ?bool
    {
        return $this->pFlag;
    }

    public function setPFlag(?bool $val): self
    {
        $this->pFlag = $val;

        return $this;
    }

    public function getDataLength(): int
    {
        return $this->dataLength;
    }

    public function setDataLength(int $val): self
    {
        $this->dataLength = $val;

        return $this;
    }

    public function getParcelId(): ?string
    {
        return $this->parcelId;
    }

    public function setParcelId(?string $val): self
    {
        $this->parcelId = $val;

        return $this;
    }

    public function getStream(): ?string
    {
        return $this->stream;
    }

    public function setStream(?string $val): self
    {
        $this->stream = $val;

        return $this;
    }

    public function getStreamDigitalLabel(): ?string
    {
        return $this->streamDigitalLabel;
    }

    public function setStreamDigitalLabel(?string $val): self
    {
        $this->streamDigitalLabel = $val;

        return $this;
    }

    public function getParcelNumberGeoPost(): ?string
    {
        return $this->parcelNumberGeoPost;
    }

    public function setParcelNumberGeoPost(?string $val): self
    {
        $this->parcelNumberGeoPost = $val;

        return $this;
    }

    public function getTrackingByParcelId(): ?string
    {
        return $this->trackingByParcelId;
    }

    public function setTrackingByParcelId(?string $val): self
    {
        $this->trackingByParcelId = $val;

        return $this;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function setFormat(?string $val): self
    {
        $this->format = $val;

        return $this;
    }

    public static function getSqlCreateStatement()
    {
        $prefix = _DB_PREFIX_;

        return "CREATE TABLE IF NOT EXISTS `{$prefix}brt_shipment_response_label` (
            `id_brt_shipment_response_label` int(11) NOT NULL AUTO_INCREMENT,
            `id_brt_shipment_response` int(11) NOT NULL,
            `numeric_sender_reference` varchar(15) NOT NULL,
            `alphanumeric_sender_reference` varchar(32) DEFAULT NULL,
            `number` int(11) NOT NULL,
            `measure_date` datetime DEFAULT NULL,
            `x` int(11) NOT NULL DEFAULT 0,
            `y` int(11) NOT NULL DEFAULT 0,
            `z` int(11) NOT NULL DEFAULT 0,
            `weight` decimal(5,1) NOT NULL DEFAULT 1.0,
            `volume` decimal(5,3) NOT NULL DEFAULT 0.000,
            `fiscal_id` varchar(16) DEFAULT NULL,
            `p_flag` tinyint(1) DEFAULT NULL,
            `data_length` int(11) NOT NULL,
            `parcel_id` varchar(64) DEFAULT NULL,
            `stream` text DEFAULT NULL,
            `stream_digital_label` text DEFAULT NULL,
            `parcel_number_geo_post` varchar(64) DEFAULT NULL,
            `tracking_by_parcel_id` varchar(64) DEFAULT NULL,
            `format` varchar(16) DEFAULT NULL,
            PRIMARY KEY (`id_brt_shipment_response_label`),
            KEY `id_brt_shipment_response` (`id_brt_shipment_response`),
            KEY `numeric_sender_reference` (`numeric_sender_reference`),
            KEY `number` (`number`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    }
}
