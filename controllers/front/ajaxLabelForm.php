<?php

use MpSoft\MpBrtApiShipment\Api\ExecutionMessage;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;

/**
 * Controller AJAX per restituire il form etichetta BRT (labelForm.tpl)
 * URL: /module/mpbrtapishipment/ajaxLabelForm.
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
                    'bordero' => ModelBrtShipmentBordero::compileBordero(),
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

        if (!isset($params['id_order']) || !is_numeric($params['id_order'])) {
            http_response_code(400);

            return ['success' => false, 'message' => 'ID ordine mancante o non valido.'];
        }

        $id_order = (int) $params['id_order'];
        $shipmentsRequestModel = MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentRequest::getByIdOrder($id_order);
        if (Validate::isLoadedObject($shipmentsRequestModel)) {
            $labelShown = true;
        }

        return ['success' => true, 'labelShown' => $labelShown, 'message' => $message];
    }

    public function displayAjaxDeleteLabel($params = null)
    {
        header('Content-Type: application/json');
        $labelDeleted = false;
        $message = '';

        if (isset($params['numericSenderReference']) && isset($params['alphanumericSenderReference'])) {
            $numericSenderReference = (int) $params['numericSenderReference'];
            $alphanumericSenderReference = (string) $params['alphanumericSenderReference'];
            $response = $this->apiRequestDelete($numericSenderReference, $alphanumericSenderReference);

            $labelDeleted = $this->checkResponse($response, $message);
        }

        if (!isset($params['id_order']) || !is_numeric($params['id_order'])) {
            http_response_code(400);

            return ['success' => false, 'message' => 'ID ordine mancante o non valido.'];
        }

        $id_order = (int) $params['id_order'];
        $this->deleteLabelReference($id_order);

        if (!$labelDeleted) {
            $order = new Order($id_order);
            if (!Validate::isLoadedObject($order)) {
                http_response_code(404);

                return ['success' => false, 'message' => 'Richiesta non trovata.'];
            }

            $numericSenderReference = $order->id;
            $alphanumericSenderReference = $order->reference;
            $response = $this->apiRequestDelete($numericSenderReference, $alphanumericSenderReference);

            $labelDeleted = $this->checkResponse($response, $message);
            if (!$labelDeleted) {
                return [
                    'success' => false,
                    'message' => $message,
                ];
            }
        }

        return ['success' => true, 'message' => 'Richiesta e label eliminate con successo.'];
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

    protected function deleteLabelReference($id_order)
    {
        // Prelevo NumericReference e alphaNumericReference
        $shipmentsRequestModel = MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentRequest::getByIdOrder($id_order);
        foreach ($shipmentsRequestModel as $shipmentRequestModel) {
            $data_json = json_decode($shipmentRequestModel->create_data_json, true);
            if (is_array($data_json)) {
                $numericSenderReference = $data_json['numericSenderReference'];
                $alphanumericSenderReference = $data_json['alphanumericSenderReference'];
            }
            // 1. Rimuovi la richiesta
            if (Validate::isLoadedObject($shipmentRequestModel)) {
                $shipmentRequestModel->delete();
            }
            // 2. Rimuovi la Response
            $shipmentsResponseModel = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);
            foreach ($shipmentsResponseModel as $shipmentResponseModel) {
                if (Validate::isLoadedObject($shipmentResponseModel)) {
                    $shipmentResponseId = (int) $shipmentResponseModel->id;
                    $shipmentResponseModel->delete();

                    // 3. Rimuovi le label
                    $labelsModel = MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel::getByShipmentResponseId($shipmentResponseId);
                    foreach ($labelsModel as $labelModel) {
                        if (Validate::isLoadedObject($labelModel)) {
                            $labelModel->delete();
                        }
                    }
                }
            }
        }
    }

    public function displayAjaxCreateLabel($params = null)
    {
        header('Content-Type: application/json');
        if (!isset($params['data']) || !is_array($params['data'])) {
            http_response_code(400);

            return ['success' => false, 'message' => 'Dati mancanti o non validi.'];
        }
        $data = $params['data'];
        $order_id = isset($data['id_order']) ? (int) $data['id_order'] : 0;

        if (!$order_id) {
            http_response_code(400);

            return ['success' => false, 'message' => 'ID ordine mancante.'];
        }

        // 1. Salva la richiesta
        $account = (new MpSoft\MpBrtApiShipment\Api\BrtAuthManager())->getAccount();
        $params['account'] = $account->toArray();

        // Sposto il parametro parcels
        $parcels = $data['parcels'];
        unset($data['parcels']);

        // creo i parametri per la label
        $labelParameters = MpSoft\MpBrtApiShipment\Api\LabelParameters::fromConfiguration();

        $shipmentRequestModel = MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentRequest::getByIdOrder($order_id);

        $shipmentRequestModel->order_id = $order_id;
        $shipmentRequestModel->numeric_sender_reference = $data['numericSenderReference'];
        $shipmentRequestModel->account_json = json_encode($params['account']);
        $shipmentRequestModel->create_data_json = json_encode($data);
        $shipmentRequestModel->is_label_required = (int) (Configuration::get('BRT_IS_LABEL_REQUIRED') ?? 0);
        $shipmentRequestModel->label_parameters_json = json_encode([]); // Puoi popolare se necessario
        $shipmentRequestModel->date_add = date('Y-m-d H:i:s');
        $shipmentRequestModel->date_upd = date('Y-m-d H:i:s');
        $shipmentRequestModel->save();

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
            $account = new MpSoft\MpBrtApiShipment\Api\Account($params['account']['userID'], $params['account']['password']);
            // $shipmentRequest = new MpSoft\MpBrtApiShipment\Api\ShipmentRequest($account, $data, 1, $labelParameters);
            $shipmentRequest = new MpSoft\MpBrtApiShipment\Api\ShipmentRequest($apiRequestArray->toArray());
            $shipmentResponse = MpSoft\MpBrtApiShipment\Api\Create::sendShipmentRequest($shipmentRequest);
            $modelShipmentResponse = ModelBrtShipmentResponse::getByNumericSenderReference($order_id);

            // 3. Salva la risposta (già fatto dentro sendShipmentRequest, ma puoi aggiungere logica custom qui)
            // 4. Salva le label (già fatto dentro sendShipmentRequest)
            // 5. Aggiorna il bordero

            $bordero = new ModelBrtShipmentBordero();
            if (Validate::isLoadedObject($bordero)) {
                $bordero->id_brt_shipment_response = $modelShipmentResponse->id;
                $bordero->bordero_number = ModelBrtShipmentBordero::getLatestBorderoNumber();
                $bordero->bordero_date = date('Y-m-d H:i:s');
                $bordero->bordero_status = 0;
                $bordero->date_add = date('Y-m-d H:i:s');
                $bordero->date_upd = date('Y-m-d H:i:s');
                $bordero->save();
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

    public function displayAjaxLabelForm($params = null)
    {
        header('Content-Type: text/html; charset=utf-8');

        if (isset($params['id_order']) && (int) $params['id_order']) {
            $id_order = (int) $params['id_order'];
        }

        if (!$id_order) {
            http_response_code(400);
            exit('<div style="color:red;padding:2em;">ID ordine mancante.</div>');
        }

        $codPaymentType = Configuration::get('BRT_PAYMENT_COD') ?? '';

        // Recupera ordine
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            http_response_code(404);
            exit('<div style="color:red;padding:2em;">Ordine non trovato.</div>');
        }

        $orderPaymentMethod = $order->module ?? '';
        $configPaymentMethod = explode(',', Configuration::get('BRT_PAYMENT_MODULES_COD'));

        $configPaymentMethod = array_unique(array_merge($configPaymentMethod, ['mpcodfee']));

        if (in_array($orderPaymentMethod, $configPaymentMethod)) {
            $isCod = true;
            $totalOrder = number_format((float) $order->total_paid_tax_incl, 2, '.', ',');
        } else {
            $isCod = false;
            $totalOrder = '';
        }

        // Recupera dati destinatario
        $customer = new Customer($order->id_customer);
        $address = new Address($order->id_address_delivery);
        $countryIso = $address->id_country ? Country::getIsoById($address->id_country) : '';
        $province = $address->id_state ? $this->getProvinceById($address->id_state) : '';
        $weightKg = $order->getTotalWeight();
        if (!$weightKg) {
            $weightKg = 1;
        }

        $account = (new MpSoft\MpBrtApiShipment\Api\BrtAuthManager())->getAccount();

        // Prepara dati per precompilazione
        $formData = [
            // Dati destinatario
            'id_order' => $id_order,
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
            'network' => '',
            'numericSenderReference' => $order->id ?? '',
            'alphanumericSenderReference' => $order->reference ?? '',
            'declaredParcelValue' => '',
            'insuranceAmount' => '',
            'serviceType' => '',
            'deliveryNote' => '',
            'numberOfParcels' => '',
            'volumeM3' => '',
            'weightKG' => $weightKg,
            // Opzioni avanzate
            'isCODMandatory' => (int) $isCod,
            'cashOnDelivery' => $totalOrder,
            'codPaymentType' => $isCod ? $codPaymentType : '',
            'parcelsHandlingCode' => '',
            'particularitiesDeliveryManagementCode' => '',
            'particularitiesHoldOnStockManagementCode' => '',
            'notifyByEmail' => Configuration::get('BRT_ALERT_BY_EMAIL') ?? 0,
            'notifyBySms' => Configuration::get('BRT_ALERT_BY_SMS') ?? 0,
            // Altri dati avanzati
            'consigneeCountryISOAlpha2' => $countryIso,
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
            'parcels' => [],
        ];

        $tpl = $this->context->smarty->createTemplate('module:mpbrtapishipment/views/templates/labelForm.tpl');
        $tpl->assign('formData', $formData);

        return ['html' => $tpl->fetch()];
    }

    private function getProvinceById($id)
    {
        $province = new State($id);
        if (!Validate::isLoadedObject($province)) {
            return '';
        }

        return $province->iso_code;
    }
}
