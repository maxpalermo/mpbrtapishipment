<?php

namespace MpSoft\MpBrtApiShipment\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table()
 *
 * @ORM\Entity()
 */
class BrtShipmentRequest
{
    /**
     * @ORM\Id
     *
     * @ORM\Column(type="integer", name="id_brt_shipment_request")
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="integer", name="order_id", nullable=true)
     */
    private ?int $orderId = null;

    /**
     * @ORM\Column(type="string", length=15, name="numeric_sender_reference")
     */
    private string $numericSenderReference;

    /**
     * @ORM\Column(type="string", length=15, name="alphanumeric_sender_reference")
     */
    private string $alphanumericSenderReference;

    /**
     * @ORM\Column(type="text", name="account_json")
     */
    private string $accountJson;

    /**
     * @ORM\Column(type="text", name="create_data_json")
     */
    private string $createDataJson;

    /**
     * @ORM\Column(type="boolean", name="is_label_required", options={"default":1})
     */
    private bool $isLabelRequired = true;

    /**
     * @ORM\Column(type="text", name="label_parameters_json")
     */
    private string $labelParametersJson;

    /**
     * @ORM\Column(type="datetime", name="date_add")
     */
    private \DateTimeInterface $dateAdd;

    /**
     * @ORM\Column(type="datetime", name="date_upd")
     */
    private \DateTimeInterface $dateUpd;

    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'order_id' => $this->getOrderId(),
            'numeric_sender_reference' => $this->getNumericSenderReference(),
            'alphanumeric_sender_reference' => $this->getAlphanumericSenderReference(),
            'account_json' => $this->getAccountJson(),
            'create_data_json' => $this->getCreateDataJson(),
            'is_label_required' => $this->isLabelRequired(),
            'label_parameters_json' => $this->getLabelParametersJson(),
            'date_add' => $this->getDateAdd(),
            'date_upd' => $this->getDateUpd(),
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrderId(): ?int
    {
        return $this->orderId;
    }

    public function setOrderId(?int $orderId): self
    {
        $this->orderId = $orderId;

        return $this;
    }

    public function getNumericSenderReference(): string
    {
        return $this->numericSenderReference;
    }

    public function setNumericSenderReference(string $numericSenderReference): self
    {
        $this->numericSenderReference = $numericSenderReference;

        return $this;
    }

    public function getAlphanumericSenderReference(): string
    {
        return $this->alphanumericSenderReference;
    }

    public function setAlphanumericSenderReference(string $alphanumericSenderReference): self
    {
        $this->alphanumericSenderReference = $alphanumericSenderReference;

        return $this;
    }

    public function getAccountJson(): array
    {
        $data = $this->accountJson;
        try {
            $json = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            return $json;
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function setAccountJson($accountJson): self
    {
        if (is_array($accountJson)) {
            $accountJson = json_encode($accountJson);
        }
        $this->accountJson = $accountJson;

        return $this;
    }

    public function getCreateDataJson(): array
    {
        $data = $this->createDataJson;
        try {
            $json = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            return $json;
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function setCreateDataJson($createDataJson): self
    {
        if (is_array($createDataJson)) {
            $createDataJson = json_encode($createDataJson);
        }
        $this->createDataJson = $createDataJson;

        return $this;
    }

    public function isLabelRequired(): bool
    {
        return (int) $this->isLabelRequired;
    }

    public function setIsLabelRequired(bool $isLabelRequired): self
    {
        $this->isLabelRequired = (int) $isLabelRequired;

        return $this;
    }

    public function getLabelParametersJson(): array
    {
        $data = $this->labelParametersJson;
        try {
            $json = json_decode($data, true, 512, JSON_THROW_ON_ERROR);

            return $json;
        } catch (\Throwable $th) {
            return [];
        }
    }

    public function setLabelParametersJson($labelParametersJson): self
    {
        if (is_array($labelParametersJson)) {
            $labelParametersJson = json_encode($labelParametersJson);
        }
        $this->labelParametersJson = $labelParametersJson;

        return $this;
    }

    public function getDateAdd(): \DateTimeInterface
    {
        return $this->dateAdd;
    }

    public function setDateAdd(\DateTimeInterface $dateAdd): self
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    public function getDateUpd(): \DateTimeInterface
    {
        return $this->dateUpd;
    }

    public function setDateUpd(\DateTimeInterface $dateUpd): self
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    public static function getSqlCreateStatement()
    {
        $prefix = _DB_PREFIX_;

        return "CREATE TABLE IF NOT EXISTS `{$prefix}brt_shipment_request` (
            `id_brt_shipment_request` int(11) NOT NULL AUTO_INCREMENT,
            `order_id` int(11) DEFAULT NULL,
            `numeric_sender_reference` varchar(15) NOT NULL,
            `alphanumeric_sender_reference` varchar(64) DEFAULT NULL,
            `account_json` text DEFAULT NULL,
            `create_data_json` text DEFAULT NULL,
            `is_label_required` tinyint(1) DEFAULT NULL,
            `label_parameters_json` text DEFAULT NULL,
            `date_add` datetime DEFAULT NULL,
            `date_upd` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_brt_shipment_request`),
            KEY `order_id` (`order_id`),
            KEY `numeric_sender_reference` (`numeric_sender_reference`),
            KEY `alphanumeric_sender_reference` (`alphanumeric_sender_reference`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    }
}
