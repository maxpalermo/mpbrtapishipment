<?php
namespace MpSoft\MpBrtApiShipment\Api;

class LabelParameters
{
    public $outputType;
    public $offsetX;
    public $offsetY;
    public $isBorderRequired;
    public $isLogoRequired;
    public $isBarcodeControlRowRequired;
    public $labelFormat;

    public function __construct($outputType = 'ZPL', $offsetX = 0, $offsetY = 0, $isBorderRequired = '0', $isLogoRequired = '0', $isBarcodeControlRowRequired = '0', $labelFormat = '')
    {
        $this->outputType = $outputType;
        $this->offsetX = $offsetX;
        $this->offsetY = $offsetY;
        $this->isBorderRequired = $isBorderRequired;
        $this->isLogoRequired = $isLogoRequired;
        $this->isBarcodeControlRowRequired = $isBarcodeControlRowRequired;
        $this->labelFormat = $labelFormat;
    }

    public static function fromArray($arr)
    {
        return new self(
            $arr['outputType'] ?? 'ZPL',
            $arr['offsetX'] ?? 0,
            $arr['offsetY'] ?? 0,
            $arr['isBorderRequired'] ?? '0',
            $arr['isLogoRequired'] ?? '0',
            $arr['isBarcodeControlRowRequired'] ?? '0',
            $arr['labelFormat'] ?? ''
        );
    }

    public function toArray()
    {
        return [
            'outputType' => $this->outputType,
            'offsetX' => $this->offsetX,
            'offsetY' => $this->offsetY,
            'isBorderRequired' => $this->isBorderRequired,
            'isLogoRequired' => $this->isLogoRequired,
            'isBarcodeControlRowRequired' => $this->isBarcodeControlRowRequired,
            'labelFormat' => $this->labelFormat,
        ];
    }
}
