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

    public static function fromConfiguration()
    {
        $labelFormat = \Configuration::get('BRT_LABEL_FORMAT');
        if (false === $labelFormat) {
            $labelFormat = '';
        }

        if (!in_array($labelFormat, ['', 'DP5', 'DPH'])) {
            $labelFormat = '';
        }

        return new self(
            \Configuration::get('BRT_LABEL_OUTPUT_TYPE') ?? 'ZPL',
            (int) (\Configuration::get('BRT_LABEL_OFFSET_X') ?? 0),
            (int) (\Configuration::get('BRT_LABEL_OFFSET_Y') ?? 0),
            (int) (\Configuration::get('BRT_LABEL_BORDER_REQUIRED') ?? 0),
            (int) (\Configuration::get('BRT_LABEL_LOGO_REQUIRED') ?? 0),
            (int) (\Configuration::get('BRT_LABEL_BARCODE_CONTROL_ROW_REQUIRED') ?? 0),
            $labelFormat
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
