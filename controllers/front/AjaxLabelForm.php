<?php

use MpSoft\MpBrtApiShipment\Api\ExecutionMessage;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentLabelWeight;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentRequest;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;

/**
 * Controller AJAX per restituire il form etichetta BRT (labelForm.tpl)
 * URL: /module/mpbrtapishipment/AjaxLabelForm.
 */
class MpBrtApiShipmentAjaxLabelFormModuleFrontController extends ModuleFrontController
{
    public function displayAjaxPrintLabel($params = null)
    {
        header('Content-Type: application/json');
        $message = '';

        if (!isset($params['numericSenderReference']) || !is_numeric($params['numericSenderReference'])) {
            http_response_code(400);

            return ['success' => false, 'message' => 'ID ordine mancante o non valido.'];
        }

        $numericSenderReference = $params['numericSenderReference'] ?? 0;
        if ($numericSenderReference) {
            $shipmentsRequestModel = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);
            if (Validate::isLoadedObject($shipmentsRequestModel)) {
                $label = MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel::createLabelPdf($shipmentsRequestModel->id);

                return [
                    'success' => true,
                    'stream' => base64_encode($label),
                    'bordero' => ModelBrtShipmentBordero::compileBordero(['numericSenderReference' => $numericSenderReference]),
                ];
            }
        }

        return [
            'success' => false,
            'message' => $message,
        ];
    }

    public function displayAjaxShowPrintLabelButton($params = null)
    {
        header('Content-Type: application/json');
        $labelShown = false;
        $message = '';
        $orderId = (int) ($params['orderId'] ?? 0);
        $numericSenderReference = (int) ($params['numericSenderReference'] ?? 0);

        if (!$numericSenderReference && !$orderId) {
            http_response_code(400);

            return [
                'success' => false,
                'message' => 'ID ordine e Riferimento numerico mancanti o non validi.',
                'numericSenderReference' => $numericSenderReference,
                'orderId' => $orderId,
            ];
        }

        if (!$numericSenderReference && $orderId) {
            $numericSenderReference = ModelBrtShipmentRequest::getNumericSenderReferenceByOrderId($orderId);
        }

        $shipmentsRequestModel = ModelBrtShipmentRequest::getByNumericSenderReference($numericSenderReference);
        if (Validate::isLoadedObject($shipmentsRequestModel)) {
            $labelShown = true;
        }

        return [
            'success' => true,
            'labelShown' => $labelShown,
            'message' => $message,
            'numericSenderReference' => $numericSenderReference,
            'orderId' => $orderId,
        ];
    }

    public function displayAjaxDeleteBrtOrderLabel($params = null)
    {
        header('Content-Type: application/json');
        $message = '';

        if (isset($params['numericSenderReference']) && isset($params['alphanumericSenderReference'])) {
            $numericSenderReference = (int) $params['numericSenderReference'];
            $alphanumericSenderReference = (string) $params['alphanumericSenderReference'];
        } elseif (isset($params['orderID']) && is_numeric($params['orderID'])) {
            $id_order = (int) $params['orderID'];
            $numericSenderReference = ModelBrtShipmentRequest::getNumericSenderReferenceByOrderId($id_order);
            $alphanumericSenderReference = ModelBrtShipmentRequest::getAlphanumericSenderReferenceByOrderId($id_order);

            if (!$numericSenderReference || !$alphanumericSenderReference) {
                // Tento con i dati di default dell'ordine
                $order = new Order($id_order);
                $numericSenderReference = $order->id;
                $alphanumericSenderReference = $order->reference;
            }
        } else {
            return ['success' => false, 'message' => 'Dati non validi.'];
        }

        $message = $this->deleteLabelByNumericSenderReference($numericSenderReference, $alphanumericSenderReference);

        return ['success' => true === $message, 'message' => $message];
    }

    protected function checkResponse($response, &$message)
    {
        // Controllo l'esito della chiamata
        $executionMessage = ExecutionMessage::fromArray($response['response']['deleteResponse']['executionMessage']);
        if ($executionMessage && method_exists($executionMessage, 'toMsgError')) {
            $msg = $executionMessage->toMsgError();
            if ($executionMessage->code < 0) {
                $message = $msg;

                return false;
            } else {
                return true;
            }
        }

        return false;
    }

    protected function apiRequestDelete($numericSenderReference, $alphanumericSenderReference)
    {
        $ApiDelete = new MpSoft\MpBrtApiShipment\Api\Delete();
        $response = $ApiDelete->deleteShipment($numericSenderReference, $alphanumericSenderReference);

        return $response;
    }

    protected function deleteLabelByNumericSenderReference($numericSenderReference, $alphanumericSenderReference)
    {
        $message = '';

        // Rimuovo l'etichetta dal server
        $response = $this->apiRequestDelete($numericSenderReference, $alphanumericSenderReference);
        $this->checkResponse($response, $message);
        // 1. Rimuovi la richiesta
        $shipmentRequestModel = ModelBrtShipmentRequest::getByNumericSenderReference($numericSenderReference);
        if (Validate::isLoadedObject($shipmentRequestModel)) {
            $shipmentRequestModel->delete();
        }
        // 2. Rimuovi la Response
        $shipmentResponseModel = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);
        if (Validate::isLoadedObject($shipmentResponseModel)) {
            $shipmentResponseModel->delete();
        }

        // 3. Rimuovi le label
        $labelsModel = MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel::getByNumericSenderReference($numericSenderReference);
        foreach ($labelsModel as $labelModel) {
            if (Validate::isLoadedObject($labelModel)) {
                $labelModel->delete();
            }
        }

        return $message;
    }

    protected function deleteLabelByIdOrder($id_order)
    {
        // Prelevo NumericReference e alphaNumericReference
        $shipmentRequestModel = ModelBrtShipmentRequest::getByIdOrder($id_order);
        if (!Validate::isLoadedObject($shipmentRequestModel)) {
            return 'Ordine non trovato.';
        }
        $data_json = json_decode($shipmentRequestModel->create_data_json, true);
        if (is_array($data_json)) {
            $numericSenderReference = $data_json['numericSenderReference'];
            $alphanumericSenderReference = $data_json['alphanumericSenderReference'];
        }

        return $this->deleteLabelByNumericSenderReference($numericSenderReference, $alphanumericSenderReference);
    }

    public function displayAjaxCreateLabelRequest($params = null)
    {
        header('Content-Type: application/json');
        if (!isset($params['data']) || !is_array($params['data'])) {
            http_response_code(400);

            return ['success' => false, 'message' => 'Dati mancanti o non validi.'];
        }
        $data = $params['data'];
        $order_id = isset($data['id_order']) ? (int) $data['id_order'] : 0;
        $numericSenderReference = $data['numericSenderReference'];
        $alphanumericSenderReference = $data['alphanumericSenderReference'];

        if (!$order_id) {
            $order_id = 0;
        }

        // Controllo che non ci sia già un'etichetta creata
        $shipmentRequestModel = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);
        if (Validate::isLoadedObject($shipmentRequestModel)) {
            http_response_code(400);

            return ['success' => false, 'message' => 'Etichetta già creata.'];
        }

        // 1. Salva la richiesta
        $account = (new MpSoft\MpBrtApiShipment\Api\BrtAuthManager())->getAccount();
        $params['account'] = $account->toArray();

        // Sposto il parametro parcels
        $parcels = $data['parcels'];
        $this->updateParcelsMeasurement($parcels);
        unset($data['parcels']);

        if ('IT' == $data['consigneeCountryAbbreviationISOAlpha2']) {
            $data['consigneeCountryAbbreviationISOAlpha2'] = 'IT';
        }

        if ('DEF' == $data['serviceType']) {
            $data['serviceType'] = '';
        }

        if (!isset($data['senderCustomerCode']) || empty($data['senderCustomerCode'])) {
            $data['senderCustomerCode'] = $params['account']['userID'];
        }

        // creo i parametri per la label
        $labelParameters = MpSoft\MpBrtApiShipment\Api\LabelParameters::fromConfiguration();

        $shipmentRequestModel = ModelBrtShipmentRequest::getByNumericSenderReference($numericSenderReference);

        try {
            $shipmentRequestModel->order_id = $order_id;
            $shipmentRequestModel->numeric_sender_reference = $data['numericSenderReference'];
            $shipmentRequestModel->alphanumeric_sender_reference = $data['alphanumericSenderReference'];
            $shipmentRequestModel->account_json = json_encode($params['account']);
            $shipmentRequestModel->create_data_json = json_encode($data);
            $shipmentRequestModel->is_label_required = (int) (Configuration::get('BRT_IS_LABEL_REQUIRED') ?? 0);
            $shipmentRequestModel->label_parameters_json = json_encode($labelParameters->toArray());
            $shipmentRequestModel->date_add = date('Y-m-d H:i:s');
            $shipmentRequestModel->date_upd = date('Y-m-d H:i:s');
            $shipmentRequestModel->save();
        } catch (Throwable $th) {
            return [
                'success' => false,
                'message' => $th->getMessage(),
            ];
        }

        // creo la chiamata API
        $apiRequestArray = (new MpSoft\MpBrtApiShipment\Api\RequestCreateData(
            $params['account'],
            $data,
            (int) (Configuration::get('BRT_IS_LABEL_REQUIRED') ?? 0),
            $labelParameters,
            $params['actualSender'] ?? [],
            $params['returnShipmentConsignee'] ?? []
        ));

        // Controllo se i dati sono a posto
        if (!$apiRequestArray->compareWithDefaultParams()) {
            return [
                'success' => false,
                'message' => 'Parametri richiesta non validi.',
            ];
        }

        // 2. Chiamata reale all'API BRT
        try {
            $shipmentRequest = new MpSoft\MpBrtApiShipment\Api\ShipmentRequest($apiRequestArray->toArray());
            $shipmentResponse = MpSoft\MpBrtApiShipment\Api\Create::sendShipmentRequest($shipmentRequest);
            $modelShipmentResponse = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);

            // 3. Salva la risposta (già fatto dentro sendShipmentRequest)
            // 4. Salva le label (già fatto dentro sendShipmentRequest)
            // 5. Aggiorna il bordero

            if (Validate::isLoadedObject($modelShipmentResponse)) {
                $bordero = ModelBrtShipmentBordero::getByNumericSenderReference($numericSenderReference);
                $bordero->numeric_sender_reference = $numericSenderReference;
                $bordero->alphanumeric_sender_reference = $alphanumericSenderReference;
                $bordero->bordero_number = ModelBrtShipmentBordero::getLatestBorderoNumber();
                $bordero->bordero_status = 0;
                $bordero->bordero_date = date('Y-m-d H:i:s');
                $bordero->date_add = date('Y-m-d H:i:s');
                $bordero->date_upd = date('Y-m-d H:i:s');

                if (Validate::isLoadedObject($bordero)) {
                    $bordero->update();
                } else {
                    $bordero->id_brt_shipment_response = $modelShipmentResponse->id;
                    $bordero->add();
                }
            }

            if (isset($shipmentResponse->executionMessage) && $shipmentResponse->executionMessage && method_exists($shipmentResponse->executionMessage, 'toMsgError')) {
                $msg = $shipmentResponse->executionMessage->toMsgError();
                if ($shipmentResponse->executionMessage->code < 0) {
                    return [
                        'success' => false,
                        'message' => $msg,
                    ];
                }
            }

            // Recupera le label salvate
            $labels = [];
            if (method_exists($shipmentResponse, 'getLabels')) {
                foreach ($shipmentResponse->getLabels() as $lbl) {
                    $labels[] = $lbl->parcelID ?? ($lbl->parcel_id ?? null);
                }
            }

            return [
                'success' => true,
                'message' => 'Etichetta creata e dati salvati con successo.',
                'label_ids' => $labels,
            ];
        } catch (Exception $ex) {
            return [
                'success' => false,
                'message' => 'Errore durante la chiamata all\'API BRT: '.$ex->getMessage(),
            ];
        }
    }

    public $ssl = true;
    public $ajax = true;

    public function __construct()
    {
        parent::__construct();
        $data = json_decode(file_get_contents('php://input'), true);
        if ($data && isset($data['action'])) {
            $action = 'displayAjax'.Tools::ucfirst($data['action']);
            if (method_exists($this, $action)) {
                $result = $this->$action($data);
                exit(json_encode($result));
            }
            http_response_code(400);
            exit('<div style="color:red;padding:2em;">Azione non valida.</div>');
        }
    }

    public function displayAjaxShowBrtLabelForm($params = null)
    {
        header('Content-Type: text/html; charset=utf-8');

        if (isset($params['orderID']) && (int) $params['orderID']) {
            $id_order = (int) $params['orderID'];
        } else {
            $id_order = 0;
        }

        if (!$id_order) {
            // Non c'è un ordine. Imposto tutti i valori di default
            $orderPaymentMethod = '';
            $totalOrder = 0;
            $totalOrderCurrency = '';
            $customer = new Customer();
            $address = new Address();
            $weightKg = 1;
        } else {
            $order = new Order($id_order);
            if (!Validate::isLoadedObject($order)) {
                http_response_code(404);
                exit('<div style="color:red;padding:2em;">Ordine non trovato.</div>');
            }
            $orderPaymentMethod = $order->module ?? '';
            $customer = new Customer($order->id_customer);
            $address = new Address($order->id_address_delivery);
            $weightKg = $order->getTotalWeight();
            $totalOrder = (float) $order->total_paid_tax_incl;
            $totalOrderCurrency = number_format((float) $order->total_paid_tax_incl, 2, '.', ',');
        }

        $codPaymentType = Configuration::get('BRT_PAYMENT_COD') ?? '';
        $configPaymentMethod = explode(',', Configuration::get('BRT_PAYMENT_MODULES_COD'));
        // hack per avere il mio modulo mpcodfee tra i moduli del contrassegno
        $configPaymentMethod = array_unique(array_merge($configPaymentMethod, ['mpcodfee']));

        if (in_array($orderPaymentMethod, $configPaymentMethod)) {
            $isCod = true;
        } else {
            $isCod = false;
            $totalOrder = 0;
            $totalOrderCurrency = '';
        }

        // Recupera dati destinatario

        $countryIso = $address->id_country ? Country::getIsoById($address->id_country) : '';
        $province = $address->id_state ? $this->getProvinceById($address->id_state) : '';

        if (!$weightKg) {
            $weightKg = 1;
        }

        // Porto
        $deliveryFreightTypeCode = Configuration::get('BRT_PORT') ?? 'DAP';
        // Tipo di servizio
        $serviceType = Configuration::get('BRT_SERVICE_TYPE') ?? '';
        // Note di spedizione
        $deliveryNote = substr($address->other ?? '', 0, 70);
        // Network
        if ('IT' == $countryIso) {
            $network = '';
        } else {
            $network = Configuration::get('BRT_NETWORK') ?? 'DPD';
        }

        $account = (new MpSoft\MpBrtApiShipment\Api\BrtAuthManager())->getAccount();
        $id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        $currency = new Currency($id_currency, Context::getContext()->language->id);
        if (!Validate::isLoadedObject($currency)) {
            $currency = 'EUR';
        } else {
            $currency = $currency->iso_code;
        }

        // Prepara dati per precompilazione
        $formData = [
            // Dati destinatario
            'id_order' => $id_order,
            'currencyIsoCode' => $currency,
            'senderCustomerCode' => $account->userID,
            'departureDepot' => Configuration::get('BRT_DEPARTURE_DEPOT'),
            'consigneeCompanyName' => $address->company ?: ($customer->firstname.' '.$customer->lastname),
            'consigneeAddress' => $address->address1,
            'consigneeZIPCode' => $address->postcode,
            'consigneeCity' => $address->city,
            'consigneeProvinceAbbreviation' => $province,
            'consigneeCountryAbbreviationISOAlpha2' => $countryIso,
            'consigneeContactName' => $customer->firstname.' '.$customer->lastname,
            'consigneeEMail' => $customer->email,
            'consigneeTelephone' => $address->phone ?: $address->phone_mobile,
            'consigneeMobilePhoneNumber' => $address->phone_mobile,
            'consigneeVATNumber' => $address->vat_number,
            'consigneeItalianFiscalCode' => $address->dni,
            // Dati spedizione
            'network' => $network,
            'numericSenderReference' => $order->id ?? '',
            'alphanumericSenderReference' => $order->reference ?? '',
            'declaredParcelValue' => '',
            'insuranceAmount' => '',
            'deliveryFreightTypeCode' => $deliveryFreightTypeCode,
            'serviceType' => $serviceType,
            'deliveryNote' => $deliveryNote,
            'numberOfParcels' => '',
            'volumeM3' => '',
            'weightKG' => $weightKg,
            // Opzioni avanzate
            'isCODMandatory' => (int) $isCod,
            'cashOnDelivery' => $totalOrder,
            'cashOnDeliveryCurrency' => $totalOrderCurrency,
            'codPaymentType' => $isCod ? $codPaymentType : '',
            'parcelsHandlingCode' => '',
            'particularitiesDeliveryManagementCode' => '',
            'particularitiesHoldOnStockManagementCode' => '',
            'notifyByEmail' => Configuration::get('BRT_ALERT_BY_EMAIL') ?? 0,
            'notifyBySms' => Configuration::get('BRT_ALERT_BY_SMS') ?? 0,
            // Altri dati avanzati
            'pricingConditionCode' => '',
            'insuranceAmountCurrency' => 'EUR',
            'senderParcelType' => '',
            'quantityToBeInvoiced' => '',
            'codCurrency' => 'EUR',
            'deliveryType' => '',
            'declaredParcelValueCurrency' => 'EUR',
            'variousParticularitiesManagementCode' => '',
            'particularDelivery1' => '',
            'particularDelivery2' => '',
            'palletType1' => '',
            'palletType1Number' => '',
            'palletType2' => '',
            'palletType2Number' => '',
            'originalSenderCompanyName' => '',
            'originalSenderZIPCode' => '',
            'originalSenderCountryAbbreviationISOAlpha2' => '',
            'cmrCode' => '',
            'neighborNameMandatoryAuthorization' => '',
            'pinCodeMandatoryAuthorization' => '',
            'packingListPDFName' => '',
            'packingListPDFFlagPrint' => '',
            'packingListPDFFlagEmail' => '',
            'consigneeClosingShift1_DayOfTheWeek' => '',
            'consigneeClosingShift1_PeriodOfTheDay' => '',
            'consigneeClosingShift2_DayOfTheWeek' => '',
            'consigneeClosingShift2_PeriodOfTheDay' => '',
            'returnDepot' => '',
            'expiryDate' => '',
            'holdForPickup' => '',
            'genericReference' => '',
            'pudoId' => '',
            'brtServiceCode' => '',
            // Campi tabella colli (verranno gestiti JS, ma lasciamo il placeholder)
            'parcels' => ModelBrtShipmentLabelWeight::getPackages($id_order),
        ];

        $tpl = $this->context->smarty->createTemplate('module:mpbrtapishipment/views/templates/labelForm.tpl');
        $tpl->assign('formData', $formData);

        return ['html' => $tpl->fetch(), 'parcels' => $formData['parcels']];
    }

    private function getProvinceById($id)
    {
        $province = new State($id);
        if (!Validate::isLoadedObject($province)) {
            return '';
        }

        return $province->iso_code;
    }

    public function updateParcelsMeasurement($parcels)
    {
        foreach ($parcels as $parcel) {
            $barcode = $parcel['barcode'];
            $model = ModelBrtShipmentLabelWeight::getByBarcode($barcode);
            if ($model) {
                $model->barcode = $barcode;
                $model->x = $parcel['length_mm'];
                $model->y = $parcel['width_mm'];
                $model->z = $parcel['height_mm'];
                $model->volume = $parcel['volume_m3'];
                $model->weight = $parcel['weight_kg'];
                $model->is_read = true;
                $model->is_envelope = $parcel['is_envelope'] ?? 0;
                $model->save();
            }
        }
    }
}
