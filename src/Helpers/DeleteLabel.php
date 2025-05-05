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

namespace MpSoft\MpBrtApiShipment\Helpers;

use MpSoft\MpBrtApiShipment\Api\Delete;
use MpSoft\MpBrtApiShipment\Api\ExecutionMessage;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentRequest;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel;

class DeleteLabel
{
    private $numericSenderReference;
    private $alphanumericSenderReference;

    public function __construct($numericSenderReference, $alphanumericSenderReference)
    {
        $this->numericSenderReference = $numericSenderReference;
        $this->alphanumericSenderReference = $alphanumericSenderReference;
    }

    public function run()
    {
        $ApiDelete = new Delete();
        $response = $ApiDelete->deleteShipment($this->numericSenderReference, $this->alphanumericSenderReference);
        $executionMessage = ExecutionMessage::fromArray($response);
        if (0 == $executionMessage->code) {
            $modelBrtShipmentRequest = ModelBrtShipmentRequest::getByNumericSenderReference($this->numericSenderReference);
            if (\Validate::isLoadedObject($modelBrtShipmentRequest)) {
                $modelBrtShipmentRequest->delete();
            }

            $modelBrtShipmentResponse = ModelBrtShipmentResponse::getByNumericSenderReference($this->numericSenderReference);
            if (\Validate::isLoadedObject($modelBrtShipmentResponse)) {
                $modelBrtShipmentResponse->delete();
            }

            $modelBrtShipmentResponseLabels = ModelBrtShipmentResponseLabel::getByNumericSenderReference($this->numericSenderReference);
            foreach ($modelBrtShipmentResponseLabels as $modelBrtShipmentResponseLabel) {
                if (\Validate::isLoadedObject($modelBrtShipmentResponseLabel)) {
                    $modelBrtShipmentResponseLabel->delete();
                }
            }

            $modelBrtShipmentBordero = ModelBrtShipmentBordero::getByNumericSenderReference($this->numericSenderReference);
            if (\Validate::isLoadedObject($modelBrtShipmentBordero)) {
                $modelBrtShipmentBordero->delete();
            }
        }

        return ['response' => $response];
    }
}
