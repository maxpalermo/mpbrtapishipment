<?php
namespace MpSoft\MpBrtApiShipment\Api;

require_once __DIR__ . '/Create.php';

class CreateTest
{
    public static function testShipment()
    {
        // Configurazione credenziali e dati fissi (sostituisci con dati reali per test reale)
        $config = [
            'userID' => 'TESTUSER',
            'password' => 'TESTPASS',
            'departureDepot' => '101',
            'senderCustomerCode' => '1234567',
            'deliveryFreightTypeCode' => 'DAP',
            'pricingConditionCode' => '000',
        ];

        // Dati spedizione di esempio
        $shipmentData = [
            'network' => '',
            'departureDepot' => '101',
            'senderCustomerCode' => '1234567',
            'deliveryFreightTypeCode' => 'DAP',
            'consigneeCompanyName' => 'Azienda Test',
            'consigneeAddress' => 'Via Finta 123',
            'consigneeCountryAbbreviationISOAlpha2' => 'IT',
            'consigneeZIPCode' => '20100',
            'consigneeCity' => 'Milano',
            'consigneeProvinceAbbreviation' => 'MI',
            'consigneeContactName' => 'Mario Rossi',
            'consigneeTelephone' => '021234567',
            'consigneeEMail' => 'test@example.com',
            'consigneeMobilePhoneNumber' => '3331234567',
            'isAlertRequired' => '0',
            'consigneeVATNumber' => '',
            'consigneeVATNumberCountryISOAlpha2' => '',
            'consigneeItalianFiscalCode' => '',
            'pricingConditionCode' => '000',
            'serviceType' => '',
            'insuranceAmount' => 0,
            'insuranceAmountCurrency' => 'EUR',
            'senderParcelType' => '',
            'quantityToBeInvoiced' => 0.0,
            'cashOnDelivery' => 0,
            'isCODMandatory' => '0',
            'codPaymentType' => '',
            'codCurrency' => 'EUR',
            'notes' => 'Test spedizione',
            'parcelsHandlingCode' => '',
            'deliveryDateRequired' => '',
            'deliveryType' => '',
            'declaredParcelValue' => 0,
            'declaredParcelValueCurrency' => 'EUR',
            'particularitiesDeliveryManagementCode' => '',
            'particularitiesHoldOnStockManagementCode' => '',
            'variousParticularitiesManagementCode' => '',
            'particularDelivery1' => '',
            'particularDelivery2' => '',
            'palletType1' => '',
            'palletType1Number' => 0,
            'palletType2' => '',
            'palletType2Number' => 0,
            'originalSenderCompanyName' => '',
            'originalSenderZIPCode' => '',
            'originalSenderCountryAbbreviationISOAlpha2' => '',
            'cmrCode' => '',
            'neighborNameMandatoryAuthorization' => '',
            'pinCodeMandatoryAuthorization' => '',
            'packingListPDFName' => '',
            'packingListPDFFlagPrint' => '',
            'packingListPDFFlagEmail' => '',
            'numericSenderReference' => 999,
            'alphanumericSenderReference' => 'TEST123',
            'numberOfParcels' => 1,
            'weightKG' => 2.5,
            'volumeM3' => 0.01,
            'consigneeClosingShift1_DayOfTheWeek' => '',
            'consigneeClosingShift1_PeriodOfTheDay' => '',
            'consigneeClosingShift2_DayOfTheWeek' => '',
            'consigneeClosingShift2_PeriodOfTheDay' => '',
            'returnDepot' => '',
            'expiryDate' => '',
            'holdForPickup' => '',
            'genericReference' => '',
            'actualSender' => [
                'actualSenderName' => 'Test Mittente',
                'actualSenderCity' => 'Roma',
                'actualSenderAddress' => 'Via Prova 1',
                'actualSenderZIPCode' => '00100',
                'actualSenderProvince' => 'RM',
                'actualSenderCountry' => 'IT',
                'actualSenderEmail' => 'mittente@example.com',
                'actualSenderMobilePhoneNumber' => '3399999999',
                'actualSenderPudoId' => '',
            ],
            'returnShipmentConsignee' => [
                'returnShipmentConsigneeName' => '',
                'returnShipmentConsigneeCity' => '',
                'returnShipmentConsigneeAddress' => '',
                'returnShipmentConsigneeZIPCode' => '',
                'returnShipmentConsigneeProvince' => '',
                'returnShipmentConsigneeCountry' => '',
                'returnShipmentConsigneeEmail' => '',
                'returnShipmentConsigneeMobilePhoneNumber' => '',
                'returnShipmentConsigneePudoId' => '',
            ],
            'pudoId' => '',
            'brtServiceCode' => '',
        ];

        $labelParameters = [
            'outputType' => 'PDF',
            'offsetX' => 0,
            'offsetY' => 0,
            'isBorderRequired' => '1',
            'isLogoRequired' => '1',
            'isBarcodeControlRowRequired' => '0',
        ];

        $response = Create::sendShipmentFromArray($shipmentData, $config, $labelParameters);
        echo "Risposta API:\n";
        print_r($response);
    }
}

// Esegui il test solo se eseguito da CLI
defined('STDIN') && CreateTest::testShipment();
