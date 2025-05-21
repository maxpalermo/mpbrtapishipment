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

use MpSoft\MpBrtApiShipment\Api\ExecutionMessage;
use MpSoft\MpBrtApiShipment\Helpers\BrtBorderoPdf;
use MpSoft\MpBrtApiShipment\Helpers\DeleteLabel;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel;
use MpSoft\MpBrtApiShipment\Pdf\MpBrtBorderoPdf;

class AdminBrtShippingBorderoController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'brt_shipment_bordero'; // nome tabella (puoi modificarlo se necessario)
        $this->identifier = 'id_brt_shipment_bordero';
        $this->className = 'MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero';
        $this->lang = false;

        // Definisci le colonne della lista (modifica in base ai dati del borderò)
        $this->fields_list = [
            'id_brt_shipment_bordero' => [
                'title' => 'ID',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'numeric_sender_reference' => [
                'title' => 'Riferimento Numerico',
                'filter_key' => 'a!numeric_sender_reference',
            ],
            'alphanumeric_sender_reference' => [
                'title' => 'Riferimento Alfanumerico',
                'filter_key' => 'a!alphanumeric_sender_reference',
            ],
            'bordero_number' => [
                'title' => 'Bordero',
                'filter_key' => 'a!bordero_number',
            ],
            'bordero_date' => [
                'title' => 'Data',
                'type' => 'datetime',
                'filter_key' => 'a!bordero_date',
            ],
            'consignee_company_name' => [
                'title' => 'Destinatario',
                'filter_key' => 'b!consignee_company_name',
            ],
            'consignee_address' => [
                'title' => 'Indirizzo',
                'filter_key' => 'b!consignee_address',
            ],
            'consignee_zip_code' => [
                'title' => 'CAP',
                'filter_key' => 'b!consignee_zip_code',
            ],
            'consignee_city' => [
                'title' => 'Città',
                'filter_key' => 'b!consignee_city',
            ],
            'consignee_province_abbreviation' => [
                'title' => 'Provincia',
                'filter_key' => 'b!consignee_province_abbreviation',
            ],
            'series_number' => [
                'title' => 'Serie',
                'filter_key' => 'b!series_number',
            ],
            'cash_on_delivery' => [
                'title' => 'Contrassegno',
                'type' => 'price',
                'filter_key' => 'b!cash_on_delivery',
            ],
            'parcel_number_from' => [
                'title' => 'N. da',
                'filter_key' => 'b!parcel_number_from',
            ],
            'parcel_number_to' => [
                'title' => 'N. a',
                'filter_key' => 'b!parcel_number_to',
            ],
            'date_add' => [
                'title' => 'Data',
                'type' => 'datetime',
            ],
            'date_upd' => [
                'title' => 'Aggiornato',
                'type' => 'datetime',
            ],
        ];

        $this->_select = 'b.*';
        $this->_where = ' AND a.printed = 0';
        $this->_defaultOrderBy = 'a.id_brt_shipment_bordero';
        $this->_defaultOrderWay = 'ASC';

        $this->_join = ' LEFT JOIN '._DB_PREFIX_.'brt_shipment_response b ON a.id_brt_shipment_response = b.id_brt_shipment_response';

        $this->addRowAction('print');
        $this->addRowAction('view');
        $this->addRowAction('delete');

        parent::__construct();

        $phpInput = file_get_contents('php://input');
        if ($phpInput) {
            $jsonData = json_decode($phpInput, true);
            if ($jsonData && isset($jsonData['action'])) {
                $action = 'ajaxProcess'.Tools::ucfirst($jsonData['action']);
                if (method_exists($this, $action)) {
                    $result = $this->$action($jsonData);
                    $this->sendAjaxResponse($result);
                    exit;
                }
                http_response_code(400);
                exit('<div style="color:red;padding:2em;">Azione non valida.</div>');
            }
        }

        $this->bulk_actions = [
            'printLabels' => [
                'text' => $this->module->l('Stampa segnacolli'),
                'confirm' => $this->module->l('Vuoi stampare i segnacolli selezionati?'),
                'target' => '_blank',
                'href' => 'javascript:void(0);',
                'icon' => 'icon-barcode',
            ],
            'divider' => [
                'text' => 'divider',
            ],
            'printAllLabels' => [
                'text' => $this->module->l('Stampa tutti i segnacolli'),
                'confirm' => $this->module->l('Vuoi stampare tutti i segnacolli di questo borderò?'),
                'target' => '_blank',
                'href' => 'javascript:void(0);',
                'icon' => 'icon-barcode text-danger',
            ],
        ];
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $printBorderoUrl = $this->context->link->getAdminLink($this->controller_name, true, [], ['action' => 'printBordero']);

        $this->page_header_toolbar_btn['print'] = [
            'href' => $printBorderoUrl,
            'desc' => $this->trans('Stampa borderò', [], 'Admin.Actions'),
            'icon' => 'icon-file',
            'target' => '_blank',
            'class' => 'print',
        ];

        $this->page_header_toolbar_btn['history'] = [
            'href' => 'javascript:showHistory();',
            'desc' => $this->trans('Storico Borderò', [], 'Admin.Actions'),
            'icon' => 'icon-list',
            'class' => 'history',
        ];

        $this->page_header_toolbar_btn['delete'] = [
            'href' => 'javascript:showDeleteBrtLabel();',
            'desc' => $this->trans('Elimina Etichetta', [], 'Admin.Actions'),
            'icon' => 'icon-trash',
            'class' => 'delete',
        ];

        $this->page_header_toolbar_btn['newLabel'] = [
            'href' => 'javascript:showBrtLabelDialog();',
            'desc' => $this->trans('Nuova Etichetta', [], 'Admin.Actions'),
            'icon' => 'icon-plus',
            'class' => 'add',
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $path = _PS_MODULE_DIR_.$this->module->name.'/views/';

        $this->addJS([
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js',
            $path.'js/admin/FormComponentsArray.js',
            $path.'js/admin/BrtLabelFormClass.js',
            $path.'js/admin/TableColli.js',
            $path.'js/swal2/sweetalert2.all.min.js',
            $path.'js/swal2/request/SwalConfirm.js',
            $path.'js/swal2/request/SwalError.js',
            $path.'js/swal2/request/SwalSuccess.js',
            $path.'js/swal2/request/SwalWarning.js',
            $path.'js/admin/MpBrtApiShipment.js',
            $path.'js/admin/confirmBordero.js',
            $path.'js/admin/showHistory.js',
        ], true);
        $this->addCSS([
            $path.'js/swal2/sweetalert2.min.css',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
            'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css',
            $path.'style.css',
        ], 'all', 1001, true);
    }

    public function initContent()
    {
        $controllerURL = $this->context->link->getAdminLink($this->controller_name);

        $frontController = $this->context->link->getModuleLink($this->module->name, 'AjaxLabelForm');

        $script = <<<SCRIPT
            <script type="text/javascript">
                async function showDeleteBrtLabel() {
                    ApiShipmentJs.formControllerURL = MpBrtApiShipmentControllerURL;
                    ApiShipmentJs.orderID = 0
                    ApiShipmentJs.showDeleteBrtLabel();
                }
                async function showBrtLabelDialog() {
                    const brtLabelForm = new window.BrtLabelForm("{$frontController}");
                    await brtLabelForm.show();
                }
                async function createLabelRequest() {
                    ApiShipmentJs.formControllerURL = MpBrtApiShipmentFrontControllerURL;
                    ApiShipmentJs.showStaticVariables();
                    await ApiShipmentJs.createLabelRequest();
                }
                async function showModalColli() {
                    const tableColli = new TableColli();
                    await tableColli.showFormColli();
                }
                async function showHistory()
                {
                    const showHistory = new ShowHistory("{$controllerURL}");
                    const list = await showHistory.getHistory();
                    await showHistory.prepareDialog();
                    showHistory.showDialog();
                }
                
                (function() {
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', main);
                    } else {
                        console.log('Firing main()');
                        
                        main();
                    }
                    function main() {
                        const MpBrtApiShipmentControllerURL = "{$controllerURL}";
                        const MpBrtApiShipmentFrontControllerURL = "{$frontController}";
                        const ApiShipmentJs = window.MpBrtApiShipment;
                    }
                })();
            </script>
        SCRIPT;

        $this->content = $script;
        parent::initContent();
    }

    /**
     * Gestisce l'azione di stampa del borderò.
     *
     * @param string $token Token di sicurezza
     * @param int    $id    ID del borderò da stampare
     * @param string $name  Nome dell'azione
     *
     * @return string
     */
    public function displayPrintLink($token, $id, $name = null)
    {
        $tpl = $this->createTemplate('helpers/list/list_action_print.tpl');
        $controller = $this->context->link->getAdminLink($this->controller_name, true, [], ['id' => $id, 'action' => 'printLabel']);
        $tpl->assign([
            'href' => $controller,
            'action' => $this->module->l('Etichetta'),
            'id' => $id,
        ]);

        return $tpl->fetch();
    }

    /**
     * Processa la richiesta di stampa del borderò.
     */
    public function processPrintLabel()
    {
        $id_bordero = (int) Tools::getValue('id');

        if ($id_bordero > 0) {
            // Recupera i dati del borderò
            $bordero = new ModelBrtShipmentBordero($id_bordero);
            if (!Validate::isLoadedObject($bordero)) {
                $this->errors[] = $this->module->l('Riga borderò non trovata');

                return;
            }

            $id_response = $bordero->id_brt_shipment_response;
            $modelResponse = new ModelBrtShipmentResponse($id_response);

            if (!Validate::isLoadedObject($modelResponse)) {
                $this->errors[] = $this->module->l('Response segnacollo non trovato!');

                return;
            }

            $pdf = ModelBrtShipmentResponseLabel::createLabelPdf($id_response);

            if ($pdf) {
                // Apre il PDF in una nuova pagina
                header('Content-type: application/pdf');
                header('Content-Disposition: inline; filename="bordero_'.$bordero->bordero_number.'.pdf"');
                echo $pdf;
                exit;
            }
        }

        $this->errors[] = $this->module->l('Impossibile stampare il borderò');
    }

    public function processGetLabelLink()
    {
        $numericSenderReference = (int) Tools::getValue('numericSenderReference');
        $order = new Order($numericSenderReference);
        $result = false;
        if (!Validate::isLoadedObject($order)) {
            $result = false;
        } else {
            $shipmentResponse = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);
            if (!Validate::isLoadedObject($shipmentResponse)) {
                $result = false;
            } else {
                $result = true;
            }
        }

        $this->sendAjaxResponse(['success' => $result]);
    }

    public function processPrintLabelByNumericSenderReference()
    {
        $numericSenderReference = (int) Tools::getValue('numericSenderReference');
        $shipmentResponse = ModelBrtShipmentResponse::getByNumericSenderReference($numericSenderReference);
        if (!Validate::isLoadedObject($shipmentResponse)) {
            $this->sendAjaxResponse(['success' => false]);

            return;
        }

        $pdf = ModelBrtShipmentResponseLabel::createLabelPdf($shipmentResponse->id);

        if ($pdf) {
            // Apre il PDF in una nuova pagina
            header('Content-type: application/pdf');
            header('Content-Disposition: inline; filename="labelBrt'.$shipmentResponse->alphanumeric_sender_reference.'.pdf"');
            echo $pdf;
            exit;
        }

        $this->sendAjaxResponse(['success' => false]);
    }

    public function processPrintBordero()
    {
        $db = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $table = _DB_PREFIX_.'brt_shipment_bordero';
        $boxes = $db->executeS(
            "SELECT id_brt_shipment_bordero FROM {$table} WHERE bordero_number = 0 ORDER BY id_brt_shipment_bordero ASC"
        );

        if (!$boxes) {
            $lastBordero = ModelBrtShipmentBordero::getLastPrintedBorderoNumber();
            $boxes = $db->executeS(
                "SELECT id_brt_shipment_bordero FROM {$table} WHERE bordero_number = {$lastBordero} ORDER BY id_brt_shipment_bordero ASC"
            );
        }

        if (!is_array($boxes)) {
            $this->errors[] = $this->module->l('Impossibile stampare il borderò');

            return;
        }

        $rows = [];
        foreach ($boxes as $box) {
            $row = $this->prepareBorderoRow($box['id_brt_shipment_bordero']);
            if ($row) {
                $rows[] = $row;
            }
        }

        $bordero = new BrtBorderoPdf($rows);
        $bordero->render();
    }

    protected function prepareBorderoRow($id_bordero)
    {
        $row = [];
        $bordero = new ModelBrtShipmentBordero($id_bordero);
        if (!Validate::isLoadedObject($bordero)) {
            return;
        }
        $response = new ModelBrtShipmentResponse($bordero->id_brt_shipment_response);
        if (!Validate::isLoadedObject($response)) {
            return;
        }

        $row = [
            [
                'row1' => substr($response->consignee_company_name, 0, 30),
                'row2' => 'TIPO DI SERVIZIO: '.$response->service_type,
                'data' => [
                    'consigneeCompanyName' => $response->consignee_company_name,
                    'serviceType' => $response->service_type,
                ],
            ],
            [
                'row1' => substr($response->consignee_address, 0, 30),
                'row2' => "{$response->consignee_zip_code} {$response->consignee_city} {$response->consignee_province_abbreviation}",
                'data' => [
                    'consigneeAddress' => $response->consignee_address,
                    'consigneeZipCode' => $response->consignee_zip_code,
                    'consigneeCity' => $response->consignee_city,
                    'consigneeProvinceAbbreviation' => $response->consignee_province_abbreviation,
                ],
            ],
            [
                'row1' => $response->numeric_sender_reference,
                'row2' => $response->alphanumeric_sender_reference,
                'data' => [
                    'numericSenderReference' => $response->numeric_sender_reference,
                    'alphanumericSenderReference' => $response->alphanumeric_sender_reference,
                ],
            ],
            [
                'row1' => '',
                'row2' => '',
                'data' => [],
            ],
            [
                'row1' => $this->formatPrice($response->cash_on_delivery),
                'row2' => '',
                'data' => [
                    'cashOnDelivery' => $response->cash_on_delivery,
                ],
            ],
            [
                'row1' => $response->number_of_parcels,
                'row2' => '',
                'data' => [
                    'numberOfParcels' => $response->number_of_parcels,
                ],
            ],
            [
                'row1' => number_format($response->weight_kg, 1, ',', ' ').' kg',
                'row2' => '',
                'data' => [
                    'weightKg' => $response->weight_kg,
                ],
            ],
            [
                'row1' => number_format($response->volume_m3, 3, ',', ' ').' m3',
                'row2' => '',
                'data' => [
                    'volumeM3' => $response->volume_m3,
                ],
            ],
            [
                'row1' => $response->parcel_number_from,
                'row2' => $response->parcel_number_to,
                'data' => [
                    'parcelNumberFrom' => $response->parcel_number_from,
                    'parcelNumberTo' => $response->parcel_number_to,
                ],
            ],
        ];

        return $row;
    }

    public function formatPrice($price, $noReturnZeroValue = true)
    {
        if ($noReturnZeroValue && 0 == $price) {
            return '--';
        }

        $locale = Tools::getContextLocale(Context::getContext());
        $currency = Context::getContext()->currency;
        $price = $locale->formatPrice($price, $currency->iso_code);

        return $price;
    }

    public function processDelete()
    {
        $id_bordero = (int) Tools::getValue($this->identifier);
        $modelBrtBordero = new ModelBrtShipmentBordero($id_bordero);
        if (!Validate::isLoadedObject($modelBrtBordero)) {
            $this->errors[] = $this->module->l('Borderò non trovato');

            return false;
        }
        $numericSenderReference = $modelBrtBordero->numeric_sender_reference;
        $alphanumericSenderReference = $modelBrtBordero->alphanumeric_sender_reference;

        $deleteResponse = (new DeleteLabel($numericSenderReference, $alphanumericSenderReference))->run($id_bordero);
        if (true == $deleteResponse['success']) {
            $this->confirmations[] = $this->module->l('Riga borderò cancellata con successo');
            $result = true;
        } else {
            $this->errors[] = $this->module->l('Impossibile cancellare il borderò: '.$deleteResponse['message']);
            $result = false;
        }

        return $result;
    }

    public function ajaxProcessDeleteLabel($params)
    {
        $numericSenderReference = (int) ($params['numericSenderReference'] ?? 0);
        $alphanumericSenderReference = (string) ($params['alphanumericSenderReference'] ?? '');
        $result = false;
        $message = '';
        $account = null;
        $deleteResponse = (new DeleteLabel($numericSenderReference, $alphanumericSenderReference))->run();

        if (is_array($deleteResponse)) {
            $account = $deleteResponse['response']['account'];
            $deleteResponse = reset($deleteResponse);
            if (isset($deleteResponse['response']['deleteResponse']['executionMessage'])) {
                $executionMessage = ExecutionMessage::fromArray($deleteResponse['response']['deleteResponse']['executionMessage']);
                if (0 == $executionMessage->code) {
                    $result = true;
                } else {
                    $result = false;
                }
                $message = $executionMessage->toMsgError();
            } else {
                $result = false;
                $message = $this->module->l('Impossibile cancellare il segnacollo. Dati non validi.');
            }
        } else {
            $result = false;
            $message = $this->module->l('Impossibile cancellare il segnacollo. Dati non validi.');
        }

        $deleteData = <<<DELETE_DATA
            <div class="alert alert-warning">
                <p>Numeric Sender Reference: <strong>{$numericSenderReference}</strong></p>
                <p>Alphanumeric Sender Reference: <strong>{$alphanumericSenderReference}</strong></p>
                <p>Sender Code: <strong>{$account->userID}</strong></p>
            </div>
        DELETE_DATA;

        $message = $deleteData.$message;

        $this->sendAjaxResponse([
            'success' => $result,
            'message' => $message,
        ]);
    }

    protected function sendAjaxResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function ajaxProcessPrintLabels($data)
    {
        if (!$data || !isset($data['ids'])) {
            return [
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ];
        }

        $ids = array_unique(array_map(function (int $box) {
            return (int) $box;
        }, $data['ids']));

        if (empty($ids)) {
            return [
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ];
        }

        return $this->getPdfLabels($ids);
    }

    public function ajaxProcessPrintAllLabels($data)
    {
        $ids = ModelBrtShipmentBordero::getIdList();

        if (!$ids) {
            return [
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ];
        }

        return $this->getPdfLabels($ids);
    }

    public function ajaxProcessprintBordero($data)
    {
        $db = Db::getInstance();
        $bordero_number = ModelBrtShipmentBordero::getLastPrintedBorderoNumber();
        $bordero_date = date('Y-m-d H:i:s');

        $subQuery = new DbQuery();
        $subQuery->select('id_brt_shipment_response')
            ->from('brt_shipment_bordero')
            ->where('bordero_status = 0')
            ->orderBy('id_brt_shipment_response ASC');
        $subQuery = $subQuery->build();

        $sql = new DbQuery();
        $sql->select('*')
            ->from('brt_shipment_response')
            ->where('id_brt_shipment_response in ('.$subQuery.')')
            ->orderBy('id_brt_shipment_response ASC');
        $sql = $sql->build();

        $result = $db->executeS($sql);
        $pdf = null;
        if ($result) {
            $ids = array_column($result, 'id_brt_shipment_response');
            $borderoPDF = new MpBrtBorderoPdf($ids, $bordero_number, $bordero_date);
            $pdf = $borderoPDF->ajaxRender();
            ModelBrtShipmentBordero::updateBorderoStatus($bordero_number);

            return [
                'success' => true,
                'pdf' => base64_encode($pdf),
            ];
        }

        return [
            'success' => false,
            'message' => 'Nessun segnacollo selezionato',
        ];
    }

    public function ajaxProcessGetHistory()
    {
        $history = ModelBrtShipmentBordero::getHistory();

        return [
            'success' => true,
            'history' => $history,
        ];
    }

    public function getPdfLabels($ids)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('lbl.stream')
            ->from('brt_shipment_response_label', 'lbl')
            ->innerJoin('brt_shipment_response', 'b', 'b.id_brt_shipment_response = lbl.id_brt_shipment_response')
            ->innerJoin('brt_shipment_bordero', 'a', 'a.id_brt_shipment_response = b.id_brt_shipment_response')
            ->where('a.id_brt_shipment_bordero IN ('.implode(',', $ids).')')
            ->orderBy('a.id_brt_shipment_bordero ASC')
            ->orderBy('lbl.id_brt_shipment_response_label ASC');

        $result = $db->executeS($sql);
        $streams = [];
        if ($result) {
            foreach ($result as $r) {
                $streams[] = base64_decode($r['stream']);
            }
        }

        $pdf = ModelBrtShipmentResponseLabel::printMergedPDF($streams);

        return [
            'success' => true,
            'pdf' => base64_encode($pdf),
        ];
    }

    public function renderView()
    {
        $id = (int) Tools::getValue($this->identifier);
        $modelBordero = new ModelBrtShipmentBordero($id);
        if (!Validate::isLoadedObject($modelBordero)) {
            $this->errors[] = $this->module->l('Borderò non trovato!');

            return '';
        }
        $id_response = $modelBordero->id_brt_shipment_response;
        $modelResponse = new ModelBrtShipmentResponse($id_response);
        $fields = $modelResponse->getFields();

        if ($fields) {
            $cleanJson = stripslashes($fields['execution_message']);
            $fields['execution_message'] = json_decode($cleanJson, true);
            $fields['consignee_country_abbreviation_brt'] = '' == $fields['consignee_country_abbreviation_brt'] ? 'IT' : $fields['consignee_country_abbreviation_brt'];
        }

        if (!Validate::isLoadedObject($modelResponse)) {
            $this->errors[] = $this->module->l('Response segnacollo non trovato!');

            return '';
        }
        $folder = _PS_MODULE_DIR_.$this->module->name.'/views/templates/admin/form/';
        $tpl = $this->context->smarty->createTemplate($folder.'FormViewLabel.tpl');
        $tpl->assign('response', $fields);

        return $tpl->fetch();
    }
}
