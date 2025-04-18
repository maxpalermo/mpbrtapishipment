<?php
namespace MpSoft\MpBrtApiShipment\Api;

class ShipmentRequest
{
    public $account;
    public $createData;
    public $isLabelRequired;
    public $labelParameters;

    public function __construct(Account $account, array $createData, $isLabelRequired = 1, LabelParameters $labelParameters = null)
    {
        $this->account = $account;
        $this->createData = $createData;
        $this->isLabelRequired = $isLabelRequired;
        $this->labelParameters = $labelParameters ?: new LabelParameters();
    }

    public static function fromArray($arr)
    {
        return new self(
            Account::fromArray($arr['account'] ?? []),
            $arr['createData'] ?? [],
            $arr['isLabelRequired'] ?? 1,
            isset($arr['labelParameters']) ? LabelParameters::fromArray($arr['labelParameters']) : null
        );
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
