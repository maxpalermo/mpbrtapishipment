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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__).'/vendor/autoload.php';

use MpSoft\MpBrtApiShipment\Entity\BrtShipmentRequest;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentResponse;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentResponseLabel;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShop\PrestaShop\Core\Action\ActionsBarButton;
use PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction;

class MpBrtApiShipment extends Module
{
    public function __construct()
    {
        $this->name = 'mpbrtapishipment';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.4';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('BRT Stampa segnacolli e Borderò');
        $this->description = $this->l('Invio spedizioni tramite API BRT.');
    }

    public function install()
    {
        $parentInstall = parent::install();

        $this->tableInstall();

        return $parentInstall && $this->registerHook(
            [
                'actionOrderGridDefinitionModifier',
                'actionOrderGridQueryBuilderModifier',
                'actionOrderGridPresenterModifier',
                'actionAdminControllerSetMedia',
                'actionGetAdminToolbarButtons',
                'displayAdminOrderTop',
                'displayAdminEndContent',
            ]
        )
        && $this->installMenu();
    }

    public function uninstall()
    {
        $parentUninstall = parent::uninstall();

        return $parentUninstall && $this->uninstallMenu();
    }

    protected function tableInstall()
    {
        $entities = [
            BrtShipmentRequest::class,
            BrtShipmentResponse::class,
            BrtShipmentResponseLabel::class,
        ];

        foreach ($entities as $entity) {
            $sql = $entity::getSqlCreateStatement();
            Db::getInstance()->execute($sql);
        }
    }

    public function installMenu()
    {
        $tab = new Tab();
        $tab->active = 1;
        $tab->class_name = 'AdminBrtBordero';
        $tab->name = [];
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = 'Borderò BRT';
        }

        $tabRes = SymfonyContainer::getInstance()->get('prestashop.core.admin.tab.repository');
        $tab_id = $tabRes->findOneIdByClassName('AdminParentShipping');

        $tab->id_parent = $tab_id;
        $tab->module = $this->name;
        $tab->icon = 'local_shipping';
        $tab->position = 1;
        $tab->enabled = 1;
        $tab->active = 1;

        return $tab->add();
    }

    public function uninstallMenu()
    {
        $tab = new Tab();
        $tab->class_name = 'AdminBrtShippingBordero';
        $tab->module = $this->name;

        $tab->delete();

        $tab = new Tab();
        $tab->class_name = 'AdminBrtBordero';
        $tab->module = $this->name;

        return $tab->delete();
    }

    protected function getCronLinkHtml()
    {
        $path = _PS_MODULE_DIR_.$this->name.'/views/templates/cron/cronLink.tpl';
        $tpl = $this->context->smarty->createTemplate($path);
        $tpl->assign([
            'cron_link' => $this->context->link->getModuleLink(
                $this->name,
                'AutoWeight',
                [
                    'ajax' => 1,
                    'action' => 'insert',
                    'PECOD' => '123456',
                    'PPESO' => '1',
                    'PVOLU' => '1',
                    'X' => '100',
                    'Y' => '100',
                    'Z' => '100',
                    'ID_FISCALE' => 'ABCDEFG',
                    'PFLAG' => '1',
                    'ENVELOPE' => '0',
                    'PTIMP' => '2025-05-01+15:19:20',
                ]
            ),
        ]);

        return $tpl->fetch();
    }

    public function getAutoWeightUrl()
    {
        $link = Context::getContext()->link;
        $controllerUrl = $link->getModuleLink($this->name, 'AutoWeight', ['ajax' => 1, 'action' => 'insert']);

        return $controllerUrl;
    }

    /**
     * Restituisce elenco impiegati per multiselect.
     */
    private function getEmployees()
    {
        $emps = [];
        foreach (Employee::getEmployees() as $e) {
            $emps[] = [
                'id_employee' => $e['id_employee'],
                'name' => $e['firstname'].' '.$e['lastname'],
            ];
        }

        return $emps;
    }

    /**
     * Restituisce elenco stati ordine.
     */
    private function getOrderStates()
    {
        $states = [];
        foreach (OrderState::getOrderStates((int) Configuration::get('PS_LANG_DEFAULT')) as $s) {
            $states[] = [
                'id_order_state' => $s['id_order_state'],
                'name' => $s['name'],
            ];
        }

        return $states;
    }

    /**
     * Restituisce elenco moduli pagamento installati.
     */
    private function getAvailablePaymentModules()
    {
        $modules = [];
        foreach (PaymentModule::getInstalledPaymentModules() as $m) {
            $module = Module::getInstanceByName($m['name']);
            $modules[] = [
                'name' => $m['name'],
                'displayName' => $module ? $module->displayName : $m['name'],
            ];
        }

        return $modules;
    }

    /**
     * Carica CSS/JS custom nell'admin quando necessario.
     *
     * @param array $params
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        $controller = Tools::getValue('controller');
        $cssPath = $this->getLocalPath().'views/css/';
        $jsPath = $this->getLocalPath().'views/js/';
        $assetsPath = $this->getLocalPath().'views/assets/js/';
        $id_order = (int) Tools::getvalue('id_order');

        if (in_array($controller, ['AdminOrders', 'AdminModules'])) {
            $this->context->controller->addCSS([
                'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&icon_names=barcode',
                'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
                $jsPath.'Select2/select2.min.css',
                $cssPath.'style.css',
            ]);
            $this->context->controller->addJS([
                $jsPath.'Select2/select2.min.js',
                $jsPath.'admin/actionOrderColumn.js',
            ]);
        }

        if (preg_match('/AdminOrders/i', $controller) && $id_order) {
            $this->context->controller->addJs([
                // $jsPath.'admin/showBrtLabelForm.js',
                // $jsPath.'admin/labelFormScript.js',
                // $jsPath.'admin/showPrintLabelButton.js',
                $jsPath.'swal2/sweetalert2.min.js',
                $jsPath.'swal2/request/SwalConfirm.js',
                $jsPath.'swal2/request/SwalError.js',
                $jsPath.'swal2/request/SwalInput.js',
                $jsPath.'swal2/request/SwalLoading.js',
                $jsPath.'swal2/request/SwalNote.js',
                $jsPath.'swal2/request/SwalSuccess.js',
                $jsPath.'swal2/request/SwalWarning.js',
                // $jsPath.'admin/MpBrtApiShipment.js',
                $assetsPath.'admin/AdminOrders.js',
                $assetsPath.'label/LabelManager.js',
            ]);
            $this->context->controller->addCSS($jsPath.'swal2/sweetalert2.min.css');
        }
    }

    /**
     * Mostra contenuto custom sopra la pagina ordine in BO.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderTop($params)
    {
        // Esempio: return $this->display(__FILE__, 'views/templates/admin/order_top.tpl');
        return '';
    }

    public function hookActionGetAdminToolbarButtons(array $params)
    {
        $isAdminOrderController = preg_match('/AdminOrders/i', $params['controller']->controller_name);
        $buttons = $params['toolbar_extra_buttons_collection'];
        $id_order = (int) Tools::getValue('id_order');
        if ($isAdminOrderController && $id_order > 0) {
            $button = new ActionsBarButton(
                'btn-warning',
                [
                    'href' => 'javascript:showBrtLabelForm();',
                    'icon' => 'bookmark',
                    'id' => 'btnShowBrtLabelForm',
                    'class' => 'btnShowBrtLabelForm',
                    'data' => [
                        'action' => 'showBrtLabelForm',
                        'id_order' => $id_order,
                    ],
                ],
                $this->l('Etichetta BRT')
            );

            $buttons->add($button);
        }

        return '';
    }

    /**
     * Modifica la definizione della grid ordini (aggiunta colonne, bulk actions).
     */
    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        return;
        /**
         * @var PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinition
         */
        $gridDefinition = $params['definition'];

        /** @var PrestaShop\PrestaShop\Core\Grid\Column\ColumnCollection */
        $columns = $gridDefinition->getColumns();

        foreach ($columns as $column) {
            // controlla se la colonna è di tipo PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn
            if ($column instanceof PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn) {
                /** @var PrestaShop\PrestaShop\Core\Grid\Column\Type\Common\ActionColumn */
                $actionsColumn = $column;

                /** @var PrestaShop\PrestaShop\Core\Grid\Action\Row\RowActionCollection */
                $currentOptions = $actionsColumn->getOptions()['actions'];

                $currentOptions->add(
                    (new LinkRowAction('print_brt'))
                        ->setName($this->l('Stampa BRT'))
                        ->setIcon('')
                        ->setOptions([
                            'route' => 'mpbrtapishipment_admin_printlabel',
                            'route_param_name' => 'orderId',
                            'route_param_field' => 'id_order',
                            'use_inline_display' => true,
                            'attr' => [
                                'class' => 'grid-print-brt-label-link icon-barcode',
                                'title' => $this->l('Stampa etichetta BRT'),
                            ],
                        ])
                );

                break;
            }
        }
    }

    /**
     * Modifica la query della grid ordini (filtri custom, join, etc).
     *
     * @param array $params
     */
    public function hookActionOrderGridQueryBuilderModifier($params)
    {
        // Esempio: $params['search_query_builder']->addSelect(...)
    }

    /**
     * Modifica la presentazione della grid ordini (customizzazione colonne, etc).
     *
     * @param array $params
     */
    public function hookActionOrderGridPresenterModifier($params)
    {
        // Personalizza la visualizzazione della nuova action (icona, stile, tooltip)
        if (!isset($params['presenter']['actions'])) {
            return;
        }
        foreach ($params['presenter']['actions'] as &$action) {
            if (isset($action['type']) && 'brt_label' === $action['type']) {
                $action['icon'] = 'fa fa-truck';
                $action['class'] = 'btn btn-info';
                $action['tooltip'] = $this->l('Stampa etichetta BRT');
            }
        }
    }

    /**
     * Mostra contenuto custom in fondo alla pagina ordine in BO.
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminEndContent($params)
    {
        $controller = Tools::getValue('controller');
        $isAdminOrdersController = preg_match('/AdminOrders/i', $controller);
        $isModuleAdminController = preg_match('/AdminModules/i', $controller);
        $id_order = (int) Tools::getValue('id_order');

        // service BrtConfiguration
        $conf = $this->get('MpSoft\MpBrtApiShipment\Services\BrtConfiguration');
        $labelPreferences = $conf->getSettings();

        // Ottieni il container Symfony
        $router = SymfonyContainer::getInstance()->get('router');

        // Genera l'URL della route (es: 'mpbrtapishipment_admin_brt_bordero_save_label')
        $url = $router->generate('mpbrtapishipment_admin_brt_bordero_save_brt_request', [
            'parameters' => [],
        ]);

        if (!$isAdminOrdersController && !$isModuleAdminController) {
            return '';
        }

        if ($isAdminOrdersController && !$id_order) {
            $adminController = $this->context->link->getAdminLink('AdminBrtShippingBordero');
            $getLabelLinkURL = $adminController.'&action=getLabelLink';
            $printLabelLinkURL = $adminController.'&action=printLabelByNumericSenderReference';
            $borderoSaveBrtRequestUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_save_settings');
            $borderoCreateBrtRequestUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_create_brt_request');
            $borderoSaveBrtResponseUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_save_brt_response');
            $borderoReadParcelsUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_read_parcels');
            $borderoNewLabelUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_new_label');

            $script = <<<JS
                <script type="text/javascript">
                    const getLabelLinkURL = "{$getLabelLinkURL}";
                    const printLabelLinkURL = "{$printLabelLinkURL}";
                    const borderoSaveBrtRequestUrl = "{$borderoSaveBrtRequestUrl}";
                    const borderoCreateBrtRequestUrl = "{$borderoCreateBrtRequestUrl}";
                    const borderoSaveBrtResponseUrl = "{$borderoSaveBrtResponseUrl}";
                    const borderoReadParcelsUrl = "{$borderoReadParcelsUrl}";
                    const borderoNewLabelUrl = "{$borderoNewLabelUrl}";
                    const orderId = {$id_order};
                </script>
            JS;

            return $script;
        }

        if ($isAdminOrdersController && $id_order > 0) {
            $frontController = $this->context->link->getModuleLink($this->name, 'AjaxLabelForm');

            $script = <<<JS
                <script type="text/javascript">
                    const ajaxLabelFormController = "{$frontController}";
                    const orderID = {$id_order};
                    let ApiShipment = null;
                    
                    function showBrtLabelForm() {
                        ApiShipment.showStaticVariables();
                        ApiShipment.showBrtLabelForm();
                    }

                    async function createLabelRequest() {
                        ApiShipment.formControllerURL = ajaxLabelFormController;
                        ApiShipment.showStaticVariables();
                        await ApiShipment.createLabelRequest();
                    }

                    async function deleteBrtOrderLabel() {
                        ApiShipment.showStaticVariables();
                        await ApiShipment.deleteBrtOrderLabel();
                    }

                    document.addEventListener('DOMContentLoaded', () => {
                        console.log("DOMCONTENT Loaded: BrtApiShipment");

                        ApiShipment = window.MpBrtApiShipment;
                        ApiShipment.formControllerURL = ajaxLabelFormController;
                        ApiShipment.orderID = orderID;
                        ApiShipment.showStaticVariables();
                        ApiShipment.showPrintLabelButton(orderID);
                        
                        $(".select2").select2({
                            language: "it",
                            width: '100%'
                        });
                    });
                </script>
            JS;

            $twig = $this->renderBrtLabelForm($id_order);

            $style = <<<STYLE
                <style>
                    #brt-label-dialog{
                        width: 100% !important;
                        height: 80vh !important;
                        z-index: 10000 !important;
                        background: transparent;
                        /* CENTRA DIV NELLA PAGINA */
                        display: absolute;
                        top: 50%;
                        left: 50%;
                        transform: translate(-50%, -50%);
                    }
                    #brt-label-dialog .card {
                        width: 95% !important;
                        max-width: 95% !important;
                        margin: 0 auto !important;
                    }
                </style>
            STYLE;

            $adminController = $this->context->link->getAdminLink('AdminBrtShippingBordero');
            $getLabelLinkURL = $adminController.'&action=getLabelLink';
            $printLabelLinkURL = $adminController.'&action=printLabelByNumericSenderReference';
            $borderoSaveBrtRequestUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_save_brt_request');
            $borderoCreateBrtRequestUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_create_brt_request');
            $borderoSaveBrtResponseUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_save_brt_response');
            $borderoReadParcelsUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_read_parcels');
            $borderoNewLabelUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_new_label');
            $borderoFillOrderDetailsUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_fill_order_details');
            $borderoPrintLabelsUrl = $router->generate('mpbrtapishipment_admin_brt_bordero_print_labels');
            $labelPrefs = json_encode($labelPreferences, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            $script = <<<JS
                <script type="text/javascript">
                    const borderoPrintLabelsUrl = "{$borderoPrintLabelsUrl}";
                    const brtLabelUrls = {
                        getLabelLinkURL: "{$getLabelLinkURL}",
                        printLabelLinkURL: "{$printLabelLinkURL}",
                        borderoSaveBrtRequestUrl: "{$borderoSaveBrtRequestUrl}",
                        borderoCreateBrtRequestUrl: "{$borderoCreateBrtRequestUrl}",
                        borderoSaveBrtResponseUrl: "{$borderoSaveBrtResponseUrl}",
                        borderoReadParcelsUrl: "{$borderoReadParcelsUrl}",
                        borderoNewLabelUrl: "{$borderoNewLabelUrl}",
                        borderoFillOrderDetailsUrl: "{$borderoFillOrderDetailsUrl}",
                    };
                    const orderId = {$id_order};
                    const labelPreferences = {$labelPrefs};
                </script>
            JS;

            return $style.$script.$twig;
        }

        if ($isModuleAdminController) {
            $script = <<<JS
                <script type="text/javascript">
                    document.addEventListener('DOMContentLoaded', () => {
                        console.log("DOMCONTENT Loaded: Applying Select2");
                        $(".select2").select2({
                            language: "it",
                            width: '100%'
                        });
                    });
                </script>
            JS;

            return $script;
        }

        return '';
    }

    public function renderBrtLabelForm($orderId)
    {
        if (!$orderId) {
            return '';
        }

        /** @var MpSoft\MpBrtApiShipment\Services\GetOrderLabelDetails $service */
        $service = $this->get('MpSoft\MpBrtApiShipment\Services\GetOrderLabelDetails');

        /** @var MpSoft\MpBrtApiShipment\Services\BrtConfiguration $conf */
        $conf = $this->get('MpSoft\MpBrtApiShipment\Services\BrtConfiguration');

        $id_lang = (int) $this->context->getContext()->language->id;

        $twigParams = $service->run($orderId);
        $otherParams = [
            'showOrderIdSearch' => false,
            'cod_currency' => $this->context->getContext()->currency->iso_code,
            'service_type' => $conf->get('service_type'),
            'network' => $conf->get('network'),
            'delivery_freight_type_code' => $conf->get('delivery_freight_type_code'),
            'cod_payment_type' => $conf->get('cod_payment_type'),
            'orderStates' => OrderState::getOrderStates($id_lang),
            'defaultChangeOrderState' => $conf->get('order_state_change'),
            'brt_environment' => $conf->get('environment'),
        ];
        $twigParams = array_merge($twigParams, $otherParams);

        /** @var Twig\Environment $twig */
        $twig = $this->get('twig');
        $template = '@Modules/mpbrtapishipment/views/templates/twig/label/label.html.twig';

        return $twig->render($template, $twigParams);
    }
}
