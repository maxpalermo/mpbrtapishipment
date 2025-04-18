<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
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

require_once dirname(__FILE__) . '/vendor/autoload.php';

class MpBrtApiShipment extends Module
{
    public function __construct()
    {
        $this->name = 'mpbrtapishipment';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('BRT API Shipment');
        $this->description = $this->l('Invio spedizioni tramite API BRT.');
    }

    public function install()
    {
        return parent::install()
            && $this->registerHook([
                'actionOrdersGridDefinitionModifier',
                'actionOrdersGridQueryBuilderModifier',
                'actionOrdersGridPresenterModifier',
                'actionAdminControllerSetMedia',
                'displayAdminOrderTop',
                'displayAdminEndContent',
            ]);
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function getContent()
    {
        $output = '';
        if (Tools::isSubmit('submit_mpbrtapishipment')) {
            // Salva tutti i parametri
            $keys = [
                'BRT_ENVIRONMENT', 'BRT_REAL_USERID', 'BRT_REAL_PASSWORD', 'BRT_SANDBOX_USERID', 'BRT_SANDBOX_PASSWORD',
                'BRT_DEPARTURE_DEPOT', 'BRT_SENDER_CUSTOMER_CODE',
                'BRT_EMPLOYEES', 'BRT_ORDERSTATE_SHOWBTN', 'BRT_ORDERSTATE_AFTERSEND',
                'BRT_PAYMENT_MODULES_COD',
                'BRT_LABEL_OUTPUT_TYPE', 'BRT_LABEL_BORDER', 'BRT_LABEL_BARCODE', 'BRT_LABEL_LOGO',
                'BRT_LABEL_OFFSET_X', 'BRT_LABEL_OFFSET_Y',
            ];
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
                    [
                        'type' => 'text',
                        'label' => $this->l('UserID Sandbox'),
                        'name' => 'BRT_SANDBOX_USERID',
                    ],
                    [
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
                    // Departure Depot e codice cliente
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Codice Filiale Mittente (departureDepot)'),
                        'name' => 'BRT_DEPARTURE_DEPOT',
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Codice Cliente Mittente (senderCustomerCode)'),
                        'name' => 'BRT_SENDER_CUSTOMER_CODE',
                    ],
                    // Moduli pagamento contrassegno
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Moduli pagamento per contrassegno'),
                        'name' => 'BRT_PAYMENT_MODULES_COD',
                        'multiple' => true,
                        'class' => 'select2',
                        'options' => [
                            'query' => $paymentModules,
                            'id' => 'name',
                            'name' => 'displayName',
                        ],
                    ],
                    // Parametri label
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
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;
        $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
        $helper->title = $this->displayName;
        $helper->show_toolbar = false;
        $helper->submit_action = 'submit_mpbrtapishipment';
        // Precompila valori
        foreach ([
            'BRT_ENVIRONMENT', 'BRT_REAL_USERID', 'BRT_REAL_PASSWORD', 'BRT_SANDBOX_USERID', 'BRT_SANDBOX_PASSWORD',
            'BRT_DEPARTURE_DEPOT', 'BRT_SENDER_CUSTOMER_CODE',
            'BRT_EMPLOYEES', 'BRT_ORDERSTATE_SHOWBTN', 'BRT_ORDERSTATE_AFTERSEND',
            'BRT_PAYMENT_MODULES_COD',
            'BRT_LABEL_OUTPUT_TYPE', 'BRT_LABEL_BORDER', 'BRT_LABEL_BARCODE', 'BRT_LABEL_LOGO',
            'BRT_LABEL_OFFSET_X', 'BRT_LABEL_OFFSET_Y',
        ] as $key) {
            $value = Configuration::get($key);

            if (in_array($key, ['BRT_EMPLOYEES', 'BRT_PAYMENT_MODULES_COD'])) {
                $helper->fields_value[$key . '[]'] = explode(',', $value);
            } else {
                $helper->fields_value[$key] = $value;
            }
        }

        return $output . $helper->generateForm([$fields_form]);
    }

    /**
     * Restituisce elenco impiegati per multiselect
     */
    private function getEmployees()
    {
        $emps = [];
        foreach (\Employee::getEmployees() as $e) {
            $emps[] = [
                'id_employee' => $e['id_employee'],
                'name' => $e['firstname'] . ' ' . $e['lastname'],
            ];
        }

        return $emps;
    }

    /**
     * Restituisce elenco stati ordine
     */
    private function getOrderStates()
    {
        $states = [];
        foreach (\OrderState::getOrderStates((int) Configuration::get('PS_LANG_DEFAULT')) as $s) {
            $states[] = [
                'id_order_state' => $s['id_order_state'],
                'name' => $s['name'],
            ];
        }

        return $states;
    }

    /**
     * Restituisce elenco moduli pagamento installati
     */
    private function getAvailablePaymentModules()
    {
        $modules = [];
        foreach (\PaymentModule::getInstalledPaymentModules() as $m) {
            $module = \Module::getInstanceByName($m['name']);
            $modules[] = [
                'name' => $m['name'],
                'displayName' => $module ? $module->displayName : $m['name'],
            ];
        }

        return $modules;
    }

    /**
     * Carica CSS/JS custom nell'admin quando necessario
     *
     * @param array $params
     */
    public function hookActionAdminControllerSetMedia($params)
    {
        $controller = Tools::getValue('controller');
        $cssPath = $this->getLocalPath() . 'views/css/';
        $jsPath = $this->getLocalPath() . 'views/js/';

        if (in_array($controller, ['AdminOrders', 'AdminModules'])) {
            $this->context->controller->addCSS($jsPath . 'Select2/select2.min.css');
            $this->context->controller->addJS($jsPath . 'Select2/select2.min.js');
        }
    }

    /**
     * Mostra contenuto custom sopra la pagina ordine in BO
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

    /**
     * Modifica la definizione della grid ordini (aggiunta colonne, bulk actions)
     *
     * @param array $params
     */
    public function hookActionOrdersGridDefinitionModifier($params)
    {
        // Aggiungi una nuova action nella colonna actions della grid ordini
        if (!isset($params['definition'])) {
            return;
        }
        $definition = $params['definition'];
        if (method_exists($definition, 'getRowActions')) {
            $actions = $definition->getRowActions();
        } elseif (method_exists($definition, 'getActions')) {
            $actions = $definition->getActions();
        } else {
            return;
        }

        // Usa la classe LinkRowAction di PrestaShop
        if (class_exists('PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction')) {
            $linkAction = new PrestaShop\PrestaShop\Core\Grid\Action\Row\Type\LinkRowAction('brt_label');
            $linkAction->setName($this->l('Stampa BRT'))
                ->setIcon('fa fa-truck')
                ->setOptions([
                    'route' => 'admin_mpbrtapishipment_printlabel',
                    'route_params' => [
                        'orderId' => '{id}',
                    ],
                    'tooltip' => $this->l('Stampa etichetta BRT'),
                ]);
            $actions->add($linkAction);
        }
    }

    /**
     * Modifica la query della grid ordini (filtri custom, join, etc)
     *
     * @param array $params
     */
    public function hookActionOrdersGridQueryBuilderModifier($params)
    {
        // Esempio: $params['search_query_builder']->addSelect(...)
    }

    /**
     * Modifica la presentazione della grid ordini (customizzazione colonne, etc)
     *
     * @param array $params
     */
    public function hookActionOrdersGridPresenterModifier($params)
    {
        // Personalizza la visualizzazione della nuova action (icona, stile, tooltip)
        if (!isset($params['presenter']['actions'])) {
            return;
        }
        foreach ($params['presenter']['actions'] as &$action) {
            if (isset($action['type']) && $action['type'] === 'brt_label') {
                $action['icon'] = 'fa fa-truck';
                $action['class'] = 'btn btn-info';
                $action['tooltip'] = $this->l('Stampa etichetta BRT');
                // Puoi aggiungere altre customizzazioni qui
            }
        }
    }

    /**
     * Mostra contenuto custom in fondo alla pagina ordine in BO
     *
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminEndContent($params)
    {
        $script = <<<JS
            <script type="text/javascript">
                document.addEventListener('DOMContentLoaded', () => {
                    console.log("DOMCONTENT Loaded: BrtApiShipment");
                    $(".select2").select2({
                        language: "it",
                        width: '100%'
                    });
                });
            </script>
        JS;

        return $script;
    }
}
