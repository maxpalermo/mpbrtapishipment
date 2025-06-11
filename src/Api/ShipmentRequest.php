<?php

namespace MpSoft\MpBrtApiShipment\Api;

class ShipmentRequest
{
    public $account;
    public $createData;
    public $isLabelRequired;
    public $labelParameters;

    public function __construct(array $RequestArray)
    {
        $this->account = Account::fromArray($RequestArray['account'] ?? []);
        $this->createData = $RequestArray['createData'] ?? [];
        $this->isLabelRequired = $RequestArray['isLabelRequired'] ?? 1;
        $this->labelParameters = $RequestArray['labelParameters'] ?? LabelParameters::fromConfiguration();
    }

    public function toArray()
    {
        return [
            'account' => $this->account->toArray(),
            'createData' => $this->createData,
            'isLabelRequired' => $this->isLabelRequired,
            'labelParameters' => $this->labelParameters->toArray(),
        ];
    }
}
