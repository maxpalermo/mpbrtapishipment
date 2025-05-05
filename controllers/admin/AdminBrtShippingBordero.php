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
        $this->_where = ' AND a.bordero_status = 0';
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
            'printAllLabels' => [
                'text' => $this->module->l('Stampa tutti i segnacolli'),
                'confirm' => $this->module->l('Vuoi stampare tutti i segnacolli di questo borderò?'),
                'target' => '_blank',
                'href' => 'javascript:void(0);',
                'icon' => 'icon-barcode text-danger',
            ],
            'divider' => [
                'text' => 'divider',
            ],
            'printBordero' => [
                'text' => $this->module->l('Stampa borderò'),
                'confirm' => $this->module->l('Sei sicuro di voler stampare il borderò?'),
                'icon' => 'icon-file text-info',
            ],
        ];
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->page_header_toolbar_btn['delete'] = [
            'href' => 'javascript:showDeleteBrtLabel();',
            'desc' => $this->trans('Elimina Etichetta', [], 'Admin.Actions'),
            'icon' => 'icon-trash',
            'class' => 'delete',
        ];
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $path = _PS_MODULE_DIR_.$this->module->name.'/views/';

        $this->addJS([
            $path.'js/admin/AdminScripts.js',
            $path.'js/swal2/sweetalert2.all.min.js',
            $path.'js/swal2/request/SwalConfirm.js',
            $path.'js/swal2/request/SwalError.js',
            $path.'js/swal2/request/SwalInput.js',
            $path.'js/swal2/request/SwalLoading.js',
            $path.'js/swal2/request/SwalNote.js',
            $path.'js/swal2/request/SwalSuccess.js',
            $path.'js/swal2/request/SwalWarning.js',
        ], true);
        $this->addCSS([
            $path.'js/swal2/sweetalert2.min.css',
        ], 'all', 1001, true);
    }

    public function initContent()
    {
        $controllerURL = $this->context->link->getAdminLink($this->controller_name);
        $script = <<<JS
            <script type="text/javascript">
                var controllerURL = '{$controllerURL}';
            </script>
        JS;

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

        $deleteResponse = (new DeleteLabel($numericSenderReference, $alphanumericSenderReference))->run();
        $executionMessage = ExecutionMessage::fromArray($deleteResponse);
        if (0 == $executionMessage->code) {
            $this->confirmations[] = $this->module->l('Riga borderò cancellata con successo');
            $result = true;
        } else {
            $this->errors[] = $this->module->l('Impossibile cancellare il borderò: '.$executionMessage->message);
            $result = false;
        }

        return $result;
    }

    public function ajaxProcessDeleteLabel()
    {
        $numericSenderReference = (int) Tools::getValue('numericSenderReference');
        $alphanumericSenderReference = (string) Tools::getValue('alphanumericSenderReference');

        $response = (new DeleteLabel($numericSenderReference, $alphanumericSenderReference))->run();
        $this->sendAjaxResponse($response);
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
        $bordero_number = ModelBrtShipmentBordero::getLastUnprintedBorderoNumber();
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
