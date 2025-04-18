<?php
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller per la stampa etichetta BRT da action della grid ordini
 */
class MpBrtApiShipmentPrintLabelController extends FrameworkBundleAdminController
{
    /**
     * Stampa etichetta BRT per ordine
     * Rotta: /mpbrtapishipment/printlabel/{orderId}
     *
     * @param Request $request
     * @param int $orderId
     * @return Response
     */
    public function printLabel(Request $request, $orderId)
    {
        // 1. Recupera l'ordine
        $order = new \Order((int)$orderId);
        if (!Validate::isLoadedObject($order)) {
            $this->addFlash('error', 'Ordine non trovato.');
            return $this->redirectToRoute('admin_orders_index');
        }

        // 2. Recupera parametri di configurazione
        $env = Configuration::get('BRT_ENVIRONMENT');
        $departureDepot = Configuration::get('BRT_DEPARTURE_DEPOT');
        $senderCustomerCode = Configuration::get('BRT_SENDER_CUSTOMER_CODE');
        $outputType = Configuration::get('BRT_LABEL_OUTPUT_TYPE');
        $offsetX = (int) Configuration::get('BRT_LABEL_OFFSET_X');
        $offsetY = (int) Configuration::get('BRT_LABEL_OFFSET_Y');
        $isBorderRequired = Configuration::get('BRT_LABEL_BORDER');
        $isLogoRequired = Configuration::get('BRT_LABEL_LOGO');
        $isBarcodeControlRowRequired = Configuration::get('BRT_LABEL_BARCODE');

        // 3. Prepara array di configurazione per Create::sendShipment
        // Recupera oggetti PrestaShop necessari
        $customer = new \Customer($order->id_customer);
        $address = new \Address($order->id_address_delivery);
        $countryIso = \Country::getIsoById($address->id_country);
        $province = $address->id_state ? \State::getNameById($address->id_state) : '';

        // Gestione dettagli avanzati spedizione
        // Recupera metodo di pagamento per eventuale contrassegno
        $paymentModule = isset($order->module) ? $order->module : '';
        $codModules = ['cashondelivery', 'ps_cashondelivery', 'mp_cod', 'brtcontrassegno']; // aggiungi qui i moduli COD usati
        $isCOD = in_array($paymentModule, $codModules);
        $codAmount = $isCOD ? $order->total_paid : 0;

        // Assicurazione (puoi attivare da config, qui placeholder su totale ordine)
        $insuranceEnabled = false; // true per attivare assicurazione
        $insuranceAmount = $insuranceEnabled ? $order->total_paid : 0;
        $insuranceCurrency = 'EUR';

        // Numero colli (se vuoi puoi calcolare da prodotti o lasciare 1)
        $numberOfParcels = 1;
        // Valore dichiarato (se vuoi assicurazione)
        $declaredParcelValue = $insuranceEnabled ? $order->total_paid : 0;

        // Note ordine
        $orderMessage = '';
        if (method_exists($order, 'getFirstMessage')) {
            $orderMessage = $order->getFirstMessage();
        }

        $config = [
            'departureDepot' => $departureDepot,
            'senderCustomerCode' => $senderCustomerCode,
            'outputType' => $outputType,
            'offsetX' => $offsetX,
            'offsetY' => $offsetY,
            'isBorderRequired' => $isBorderRequired,
            'isLogoRequired' => $isLogoRequired,
            'isBarcodeControlRowRequired' => $isBarcodeControlRowRequired,
            // Campi spedizione BRT principali
            'network' => '', // opzionale, valorizza da config se necessario
            'deliveryFreightTypeCode' => 'DAP',
            'consigneeCompanyName' => $address->company ?: ($customer->firstname . ' ' . $customer->lastname),
            'consigneeAddress' => $address->address1,
            'consigneeCountryAbbreviationISOAlpha2' => $countryIso,
            'consigneeZIPCode' => $address->postcode,
            'consigneeCity' => $address->city,
            'consigneeProvinceAbbreviation' => $province,
            'consigneeContactName' => $customer->firstname . ' ' . $customer->lastname,
            'consigneeTelephone' => $address->phone ?: $address->phone_mobile,
            'consigneeEMail' => $customer->email,
            'consigneeMobilePhoneNumber' => '', // opzionale
            // Servizi speciali e condizioni di prezzo (placeholder)
            'pricingConditionCode' => '', // es: "FISSO", "CONVENZIONE", da config se serve
            'serviceType' => '', // es: "EXPRESS", da config se serve
            // Assicurazione
            'insuranceAmount' => $insuranceAmount,
            'insuranceAmountCurrency' => $insuranceCurrency,
            'senderParcelType' => '',
            'quantityToBeInvoiced' => 0.0,
            // Contrassegno (COD)
            'cashOnDelivery' => $codAmount,
            'isCODMandatory' => $isCOD ? '1' : '0',
            'codPaymentType' => $isCOD ? 'CONTANTI' : '',
            'codCurrency' => 'EUR',
            // Note ordine
            'notes' => $orderMessage,
            'parcelsHandlingCode' => '',
            'deliveryDateRequired' => '',
            'deliveryType' => '',
            // Valore dichiarato
            'declaredParcelValue' => $declaredParcelValue,
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
            'numericSenderReference' => $order->id,
            'alphanumericSenderReference' => $order->reference,
            'numberOfParcels' => $numberOfParcels,
            'weightKG' => $order->getTotalWeight(),
            'volumeM3' => 0,
            // Campi opzionali avanzati (chiavi vuote di default)
            'consigneeClosingShift1_DayOfTheWeek' => '',
            'consigneeClosingShift1_PeriodOfTheDay' => '',
            'consigneeClosingShift2_DayOfTheWeek' => '',
            'consigneeClosingShift2_PeriodOfTheDay' => '',
            'returnDepot' => '',
            'expiryDate' => '',
            'holdForPickup' => '',
            'genericReference' => '',
            // Campi relativi a actualSender e returnShipmentConsignee lasciati vuoti
        ];

        try {
            // 4. Invia richiesta a BRT usando la firma corretta
            $response = \MpSoft\MpBrtApiShipment\Api\Create::sendShipment((int)$orderId, $config, $env);
            if (isset($response->labels) && is_array($response->labels) && count($response->labels) > 0) {
                $label = $response->labels[0];
                $pdfContent = base64_decode($label->stream);
                return new Response($pdfContent, 200, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="brt_label_'.$orderId.'.pdf"'
                ]);
            } else {
                $msg = $response->executionMessage->message ?? 'Nessuna etichetta restituita da BRT.';
                $this->addFlash('error', $msg);
            }
        } catch (\Exception $e) {
            $this->addFlash('error', 'Errore generazione etichetta: ' . $e->getMessage());
        }
        return $this->redirectToRoute('admin_orders_view', ['orderId' => (int)$orderId]);
    }
}
