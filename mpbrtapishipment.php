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
        return parent::install()
            && $this->registerHook([
                'actionOrderGridDefinitionModifier',
                'actionOrderGridQueryBuilderModifier',
                'actionOrderGridPresenterModifier',
                'actionAdminControllerSetMedia',
                'actionGetAdminToolbarButtons',
                'displayAdminOrderTop',
                'displayAdminEndContent',
            ])
            && $this->installTables()
            && $this->installMenu();
    }

    protected function installTables()
    {
        $fileSql = file_get_contents(__DIR__.'/sql/install_brt_shipment_tables.sql');
        $fileSql = str_replace('__PREFIX__', _DB_PREFIX_, $fileSql);
        $queries = explode(';', $fileSql);

        foreach ($queries as $query) {
            if (trim($query)) {
                Db::getInstance()->execute($query);
            }
        }

        return true;
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

    public function uninstall()
    {
        return parent::uninstall()
            && $this->uninstallMenu();
    }

    public function getConfigurationKeys()
    {
        return [
            'BRT_ENVIRONMENT',
            'BRT_REAL_USERID',
            'BRT_REAL_PASSWORD',
            'BRT_SANDBOX_USERID',
            'BRT_SANDBOX_PASSWORD',
            'BRT_DEPARTURE_DEPOT',
            'BRT_EMPLOYEES',
            'BRT_ORDERSTATE_SHOWBTN',
            'BRT_ORDERSTATE_AFTERSEND',
            'BRT_PAYMENT_MODULES_COD',
            'BRT_LABEL_OUTPUT_TYPE',
            'BRT_LABEL_BORDER',
            'BRT_LABEL_BARCODE',
            'BRT_LABEL_LOGO',
            'BRT_LABEL_OFFSET_X',
            'BRT_LABEL_OFFSET_Y',
            'BRT_ALERT_BY_EMAIL',
            'BRT_ALERT_BY_SMS',
            'BRT_PAYMENT_COD',
            'BRT_IS_LABEL_REQUIRED',
            'BRT_PORT',
            'BRT_SERVICE_TYPE',
            'BRT_NETWORK',
        ];
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit_mpbrtapishipment')) {
            // Salva tutti i parametri
            $keys = $this->getConfigurationKeys();
            foreach ($keys as $key) {
                $val = Tools::getValue($key);
                if (is_array($val)) {
                    $val = implode(',', $val);
                }
                Configuration::updateValue($key, $val);
            }
            $output .= $this->displayConfirmation($this->l('Configurazione aggiornata'));
        }

        // Recupera dati per i select/multiselect dinamici
        $employees = $this->getEmployees();
        $orderStates = $this->getOrderStates();
        $paymentModules = $this->getAvailablePaymentModules();

        $fields_form = [
            'form' => [
                'legend' => ['title' => $this->l('Configurazione BRT API')],
                'input' => [
                    // Link CRON
                    [
                        'type' => 'html',
                        'label' => $this->l('Link CRON'),
                        'name' => 'cron_link',
                        'html_content' => $this->getCronLinkHtml(),
                    ],
                    // Ambiente e credenziali
                    [
                        'type' => 'select',
                        'label' => $this->l('Ambiente BRT (default)'),
                        'name' => 'BRT_ENVIRONMENT',
                        'options' => [
                            'query' => [
                                ['id' => 'real', 'name' => 'Produzione'],
                                ['id' => 'sandbox', 'name' => 'Sandbox/Test'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    [
                        'col' => 2,
                        'type' => 'text',
                        'label' => $this->l('UserID Produzione'),
                        'name' => 'BRT_REAL_USERID',
                    ],
                    [
                        'col' => 2,
                        'type' => 'text',
                        'label' => $this->l('Password Produzione'),
                        'name' => 'BRT_REAL_PASSWORD',
                    ],
                    [
                        'col' => 2,
                        'type' => 'text',
                        'label' => $this->l('UserID Sandbox'),
                        'name' => 'BRT_SANDBOX_USERID',
                    ],
                    [
                        'col' => 2,
                        'type' => 'text',
                        'label' => $this->l('Password Sandbox'),
                        'name' => 'BRT_SANDBOX_PASSWORD',
                    ],
                    // Accesso impiegati
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Impiegati abilitati'),
                        'name' => 'BRT_EMPLOYEES',
                        'multiple' => true,
                        'class' => 'select2',
                        'options' => [
                            'query' => $employees,
                            'id' => 'id_employee',
                            'name' => 'name',
                        ],
                    ],
                    // Stati ordine: mostra pulsante
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Mostra pulsante BRT su stato ordine'),
                        'name' => 'BRT_ORDERSTATE_SHOWBTN',
                        'class' => 'select2',
                        'options' => [
                            'query' => $orderStates,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                    ],
                    // Stati ordine: dopo invio
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Stato ordine dopo invio a BRT'),
                        'name' => 'BRT_ORDERSTATE_AFTERSEND',
                        'class' => 'select2',
                        'options' => [
                            'query' => $orderStates,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                    ],
                    // Departure Depot
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Codice Filiale Mittente (departureDepot)'),
                        'name' => 'BRT_DEPARTURE_DEPOT',
                    ],
                    // Porto
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Porto'),
                        'name' => 'BRT_PORT',
                        'class' => 'select2',
                        'options' => [
                            'query' => [
                                ['id' => 'DAP', 'name' => 'FRANCO'],
                                ['id' => 'EXW', 'name' => 'ASSEGNATO'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    // Tipo di servizio
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Tipo di servizio'),
                        'name' => 'BRT_SERVICE_TYPE',
                        'class' => 'select2',
                        'options' => [
                            'query' => [
                                ['id' => '', 'name' => 'DEFAULT'],
                                ['id' => 'E', 'name' => 'PRIORITY'],
                                ['id' => 'H', 'name' => '10:30'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    // Network estero
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Network estero'),
                        'name' => 'BRT_NETWORK',
                        'class' => 'select2',
                        'options' => [
                            'query' => [
                                ['id' => 'D', 'name' => 'DPD'],
                                ['id' => 'E', 'name' => 'EURO EXPRESS'],
                                ['id' => 'S', 'name' => 'FED'],
                            ],
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                    // Moduli pagamento contrassegno
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Tipo di pagamento contrassegno'),
                        'name' => 'BRT_PAYMENT_COD',
                        'class' => 'select2',
                        'options' => [
                            'query' => [
                                ['value' => '',   'label' => 'ACCETTARE CONTANTE'],
                                ['value' => 'BM', 'label' => 'ACCETTARE ASSEGNO BANCARIO INTESTATO ALLA MITTENTE'],
                                ['value' => 'CM', 'label' => 'ACCETTARE ASSEGNO CIRCOLARE INTESTATO ALLA MITTENTE'],
                                ['value' => 'BB', 'label' => 'ACCETTARE ASSEGNO BANCARIO INTESTATO CORRIERE CON MANLEVA'],
                                ['value' => 'OM', 'label' => 'ACCETTARE ASSEGNO INTESTATO AL MITTENTE ORIGINALE'],
                                ['value' => 'OC', 'label' => 'ACCETTARE ASSEGNO CIRCOLARE INTESTATO AL MITTENTE ORIGINALE'],
                            ],
                            'id' => 'value',
                            'name' => 'label',
                        ],
                    ],
                    // Avvisi
                    [
                        'type' => 'switch',
                        'label' => $this->l('Avvisa via Email'),
                        'name' => 'BRT_ALERT_BY_EMAIL',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Si')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Avvisa via SMS'),
                        'name' => 'BRT_ALERT_BY_SMS',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Si')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    // Parametri label
                    [
                        'type' => 'switch',
                        'label' => $this->l('Creazione etichetta?'),
                        'name' => 'BRT_IS_LABEL_REQUIRED',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Si')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Stampa PDF (altrimenti ZPL)'),
                        'name' => 'BRT_LABEL_OUTPUT_TYPE',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 'PDF', 'label' => $this->l('PDF')],
                            ['id' => 'off', 'value' => 'ZPL', 'label' => $this->l('ZPL')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Etichetta con bordo?'),
                        'name' => 'BRT_LABEL_BORDER',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Si')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Etichetta con barcode?'),
                        'name' => 'BRT_LABEL_BARCODE',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Si')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->l('Etichetta con logo?'),
                        'name' => 'BRT_LABEL_LOGO',
                        'is_bool' => true,
                        'values' => [
                            ['id' => 'on', 'value' => 1, 'label' => $this->l('Si')],
                            ['id' => 'off', 'value' => 0, 'label' => $this->l('No')],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Offset X'),
                        'name' => 'BRT_LABEL_OFFSET_X',
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Offset Y'),
                        'name' => 'BRT_LABEL_OFFSET_Y',
                    ],
                ],
                'submit' => ['title' => $this->l('Salva')],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'submit_mpbrtapishipment';
        // Precompila valori
        $keys = $this->getConfigurationKeys();
        foreach ($keys as $key) {
            $value = Configuration::get($key);

            if (in_array($key, ['BRT_EMPLOYEES', 'BRT_PAYMENT_MODULES_COD'])) {
                $helper->fields_value[$key.'[]'] = explode(',', $value);
            } else {
                $helper->fields_value[$key] = $value;
            }
        }

        return $output.$helper->generateForm([$fields_form]);
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
                $jsPath.'admin/MpBrtApiShipment.js',
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

        if ($isAdminOrderController && $id_order) {
            $button = new ActionsBarButton(
                'btn-danger',
                [
                    'href' => 'javascript:deleteBrtOrderLabel();',
                    'icon' => 'delete',
                    'id' => 'btnDeleteBrtOrderLabel',
                    'class' => 'btnDeleteBrtOrderLabel',
                    'data' => [
                        'action' => 'deleteBrtOrderLabel',
                        'id_order' => $id_order,
                    ],
                ],
                $this->l('Elimina etichetta BRT')
            );

            $buttons->add($button);

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
                $this->l('Crea etichetta BRT')
            );
        }

        $buttons->add($button);

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

        if (!$isAdminOrdersController && !$isModuleAdminController) {
            return '';
        }

        if ($isAdminOrdersController && !$id_order) {
            $adminController = $this->context->link->getAdminLink('AdminBrtShippingBordero');
            $getLabelLinkURL = $adminController.'&action=getLabelLink';
            $printLabelLinkURL = $adminController.'&action=printLabelByNumericSenderReference';

            $script = <<<JS
                <script type="text/javascript">
                    const getLabelLinkURL = "{$getLabelLinkURL}";
                    const printLabelLinkURL = "{$printLabelLinkURL}";
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

            return $script;
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
}
