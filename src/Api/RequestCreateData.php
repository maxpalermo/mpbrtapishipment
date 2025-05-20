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

class RequestCreateData
{
    public $account;
    public $createData;
    public $isLabelRequired;
    public $labelParameters;
    public $actualSender;
    public $returnShipmentConsignee;

    public function __construct($account, $createData, $isLabelRequired, $labelParameters, $actualSender, $returnShipmentConsignee)
    {
        $this->account = $account;
        $this->createData = $createData;
        $this->isLabelRequired = $isLabelRequired;
        $this->labelParameters = $labelParameters;
        $this->actualSender = $actualSender;
        $this->returnShipmentConsignee = $returnShipmentConsignee;
    }

    public static function fromArray($arr)
    {
        return new self($arr['account'] ?? null, $arr['createData'] ?? [], $arr['isLabelRequired'] ?? 0, $arr['labelParameters'] ?? [], $arr['actualSender'] ?? [], $arr['returnShipmentConsignee'] ?? []);
    }

    public function toArray()
    {
        return [
            'account' => $this->account,
            'createData' => $this->createData,
            'isLabelRequired' => $this->isLabelRequired,
            'labelParameters' => $this->labelParameters,
            'actualSender' => $this->actualSender,
            'returnShipmentConsignee' => $this->returnShipmentConsignee,
        ];
    }

    public function getDefaultParams()
    {
        $request = [
            'account' => [
                'userID' => ['mandatory' => true],
                'password' => ['mandatory' => true],
            ],
            'createData' => [
                'network' => ['mandatory' => false],
                'departureDepot' => ['mandatory' => true],
                'senderCustomerCode' => ['mandatory' => true],
                'deliveryFreightTypeCode' => ['mandatory' => true],
                'consigneeCompanyName' => ['mandatory' => 'Condizionato: obbligatorio se brtServiceCode != B15'],
                'consigneeAddress' => ['mandatory' => 'Condizionato: obbligatorio se brtServiceCode != B15'],
                'consigneeZIPCode' => ['mandatory' => 'Condizionato: obbligatorio se brtServiceCode != B15'],
                'consigneeCity' => ['mandatory' => 'Condizionato: obbligatorio se brtServiceCode != B15'],
                'consigneeProvinceAbbreviation' => ['mandatory' => false],
                'consigneeCountryAbbreviationISOAlpha2' => ['mandatory' => 'Condizionato: obbligatorio se brtServiceCode != B15'],
                'consigneeClosingShift1_DayOfTheWeek' => ['mandatory' => false],
                'consigneeClosingShift1_PeriodOfTheDay' => ['mandatory' => false],
                'consigneeClosingShift2_DayOfTheWeek' => ['mandatory' => false],
                'consigneeClosingShift2_PeriodOfTheDay' => ['mandatory' => false],
                'consigneeContactName' => ['mandatory' => false],
                'consigneeTelephone' => ['mandatory' => false],
                'consigneeEMail' => ['mandatory' => false],
                'consigneeMobilePhoneNumber' => ['mandatory' => false],
                'isAlertRequired' => ['mandatory' => false],
                'consigneeVATNumber' => ['mandatory' => false],
                'consigneeVATNumberCountryISOAlpha2' => ['mandatory' => false],
                'consigneeItalianFiscalCode' => ['mandatory' => false],
                'pricingConditionCode' => ['mandatory' => false],
                'serviceType' => ['mandatory' => false],
                'insuranceAmount' => ['mandatory' => false],
                'insuranceAmountCurrency' => ['mandatory' => false],
                'senderParcelType' => ['mandatory' => false],
                'numberOfParcels' => ['mandatory' => true],
                'weightKG' => ['mandatory' => true],
                'volumeM3' => ['mandatory' => false],
                'quantityToBeInvoiced' => ['mandatory' => false],
                'cashOnDelivery' => ['mandatory' => false],
                'isCODMandatory' => ['mandatory' => false],
                'codPaymentType' => ['mandatory' => false],
                'codCurrency' => ['mandatory' => false],
                'numericSenderReference' => ['mandatory' => true],
                'alphanumericSenderReference' => ['mandatory' => false],
                'notes' => ['mandatory' => false],
                'parcelsHandlingCode' => ['mandatory' => false],
                'deliveryDateRequired' => ['mandatory' => false],
                'deliveryType' => ['mandatory' => false],
                'declaredParcelValue' => ['mandatory' => false],
                'declaredParcelValueCurrency' => ['mandatory' => false],
                'particularitiesDeliveryManagementCode' => ['mandatory' => false],
                'particularitiesHoldOnStockManagementCode' => ['mandatory' => false],
                'variousParticularitiesManagementCode' => ['mandatory' => false],
                'particularDelivery1' => ['mandatory' => false],
                'particularDelivery2' => ['mandatory' => false],
                'palletType1' => ['mandatory' => false],
                'palletType1Number' => ['mandatory' => 'Condizionato: obbligatorio se palletType1 valorizzato'],
                'palletType2' => ['mandatory' => false],
                'palletType2Number' => ['mandatory' => 'Condizionato: obbligatorio se palletType2 valorizzato'],
                'originalSenderCompanyName' => ['mandatory' => false],
                'originalSenderZIPCode' => ['mandatory' => false],
                'originalSenderCountryAbbreviationISOAlpha2' => ['mandatory' => false],
                'cmrCode' => ['mandatory' => 'Condizionato: obbligatorio se si passa da accorpamento bolle'],
                'neighborNameMandatoryAuthorization' => ['mandatory' => false],
                'pinCodeMandatoryAuthorization' => ['mandatory' => false],
                'packingListPDFName' => ['mandatory' => false],
                'packingListPDFFlagPrint' => ['mandatory' => false],
                'packingListPDFFlagEmail' => ['mandatory' => false],
                'pudoId' => ['mandatory' => false],
                'brtServiceCode' => ['mandatory' => false],
                'returnDepot' => ['mandatory' => 'Condizionato: obbligatorio per B15 o se richiesto dal servizio'],
                'expiryDate' => ['mandatory' => 'Condizionato: obbligatorio per servizio Fresh'],
                'holdForPickup' => ['mandatory' => false],
                'genericReference' => ['mandatory' => false],
            ],
            'isLabelRequired' => ['mandatory' => true],
            'labelParameters' => [
                'outputType' => ['mandatory' => 'Condizionato: obbligatorio se isLabelRequired = 1'],
                'offsetX' => ['mandatory' => 'Condizionato: obbligatorio se isLabelRequired = 1'],
                'offsetY' => ['mandatory' => 'Condizionato: obbligatorio se isLabelRequired = 1'],
                'isBorderRequired' => ['mandatory' => 'Condizionato: obbligatorio se isLabelRequired = 1'],
                'isLogoRequired' => ['mandatory' => 'Condizionato: obbligatorio se isLabelRequired = 1'],
                'isBarcodeControlRowRequired' => ['mandatory' => 'Condizionato: obbligatorio se isLabelRequired = 1'],
                'labelFormat' => ['mandatory' => false],
            ],
            'actualSender' => [
                'actualSenderName' => ['mandatory' => true],
                'actualSenderCity' => ['mandatory' => false],
                'actualSenderAddress' => ['mandatory' => false],
                'actualSenderZIPCode' => ['mandatory' => false],
                'actualSenderProvince' => ['mandatory' => false],
                'actualSenderCountry' => ['mandatory' => false],
                'actualSenderEmail' => ['mandatory' => 'Condizionato: obbligatorio almeno uno tra actualSenderEmail e actualSenderMobilePhoneNumber'],
                'actualSenderMobilePhoneNumber' => ['mandatory' => 'Condizionato: obbligatorio almeno uno tra actualSenderEmail e actualSenderMobilePhoneNumber'],
                'actualSenderPudoId' => ['mandatory' => false],
            ],
            'returnShipmentConsignee' => [
                'returnShipmentConsigneeName' => ['mandatory' => true],
                'returnShipmentConsigneeCity' => ['mandatory' => true],
                'returnShipmentConsigneeAddress' => ['mandatory' => true],
                'returnShipmentConsigneeZIPCode' => ['mandatory' => true],
                'returnShipmentConsigneeProvince' => ['mandatory' => false],
                'returnShipmentConsigneeCountry' => ['mandatory' => true],
                'returnShipmentConsigneeEmail' => ['mandatory' => 'Condizionato: obbligatorio almeno uno tra returnShipmentConsigneeEmail e returnShipmentConsigneeMobilePhoneNumber'],
                'returnShipmentConsigneeMobilePhoneNumber' => ['mandatory' => 'Condizionato: obbligatorio almeno uno tra returnShipmentConsigneeEmail e returnShipmentConsigneeMobilePhoneNumber'],
                'returnShipmentConsigneePudoId' => ['mandatory' => false],
            ],
        ];

        return $request;
    }

    public function createRequestParams()
    {
        // Crea l'array
        $array = [
            'account' => $this->account,
            'createData' => $this->createData,
            'isLabelRequired' => $this->isLabelRequired,
            'labelParameters' => $this->labelParameters,
            'actualSender' => $this->actualSender,
            'returnShipmentConsignee' => $this->returnShipmentConsignee,
        ];

        // Pulisco l'array dai dati non richiesti
        $defaultParams = $this->createDefaultParams(true)['createData'];
        $params = array_keys($defaultParams);
        $compare = array_keys($array['createData']);
        $diff = array_diff($compare, $params);
        foreach ($diff as $param) {
            unset($array['createData'][$param]);
        }

        $this->createData = $array['createData'];

        return $array;
    }

    public function createDefaultParams($allParams = false)
    {
        $defaults = $this->getDefaultParams();
        if ($allParams) {
            return $defaults;
        }

        $sections = [
            'account',
            'createData',
            'isLabelRequired',
            'labelParameters',
            'actualSender',
            'returnShipmentConsignee',
        ];
        foreach ($sections as $section) {
            foreach ($defaults[$section] as $key => $value) {
                if (isset($value['mandatory']) && false == $value['mandatory']) {
                    unset($defaults[$section][$key]);
                } elseif (isset($value['mandatory'])) {
                    unset($value['mandatory']);
                }
            }
        }

        return $defaults;
    }

    public function compareWithDefaultParams()
    {
        return true;

        // Compara l'array
        $request = $this->createRequestParams();
        $default = $this->createDefaultParams();
        $compare = array_keys($request['createData']);
        $params = array_keys($default['createData']);
        $diff = array_diff($params, $compare);
        if ($diff) {
            return false;
        }

        return true;
    }
}
