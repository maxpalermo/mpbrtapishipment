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

namespace MpSoft\MpBrtApiShipment\Controllers\Admin;

use Doctrine\DBAL\Connection;
use MpSoft\MpBrtApiShipment\Api\BrtConfiguration;
use MpSoft\MpBrtApiShipment\Helpers\BrtApiManager;
use MpSoft\MpBrtApiShipment\Helpers\BrtBorderoPdf;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel;
use MpSoft\MpBrtApiShipment\Repository\Doctrine\BrtShipmentRequestRepository;
use MpSoft\MpBrtApiShipment\Repository\Doctrine\BrtShipmentResponseLabelRepository;
use MpSoft\MpBrtApiShipment\Repository\Doctrine\BrtShipmentResponseRepository;
use MpSoft\MpBrtApiShipment\Services\GetOrderLabelDetails;
use MpSoft\MpBrtApiShipment\Services\GetParcelsMeasures;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminBrtBorderoController extends FrameworkBundleAdminController
{
    /** @var \Module */
    private $module;
    private BrtShipmentRequestRepository $brtShipmentRequestRepository;
    private BrtShipmentResponseRepository $brtShipmentResponseRepository;
    private BrtShipmentResponseLabelRepository $brtShipmentResponseLabelRepository;
    private GetOrderLabelDetails $getOrderLabelDetails;
    private GetParcelsMeasures $getParcelsMeasures;
    private LegacyContext $context;
    private TranslatorInterface $translator;
    private BrtApiManager $brtApiManager;

    public function __construct(
        BrtShipmentRequestRepository $brtShipmentRequestRepository,
        BrtShipmentResponseRepository $brtShipmentResponseRepository,
        BrtShipmentResponseLabelRepository $brtShipmentResponseLabelRepository,
        GetOrderLabelDetails $getOrderLabelDetails,
        GetParcelsMeasures $getParcelsMeasures,
        LegacyContext $context,
        $translator,
    ) {
        parent::__construct();

        $this->module = \Module::getInstanceByName('mpbrtapishipment');
        $this->context = $context;
        $this->translator = $translator;

        $this->brtShipmentRequestRepository = $brtShipmentRequestRepository;
        $this->brtShipmentResponseRepository = $brtShipmentResponseRepository;
        $this->brtShipmentResponseLabelRepository = $brtShipmentResponseLabelRepository;
        $this->getOrderLabelDetails = $getOrderLabelDetails;
        $this->getParcelsMeasures = $getParcelsMeasures;
        $this->brtApiManager = new BrtApiManager(
            $this->brtShipmentRequestRepository,
            $this->brtShipmentResponseRepository,
            $this->brtShipmentResponseLabelRepository,
        );
    }

    public function indexAction()
    {
        return $this->render(
            '@Modules/mpbrtapishipment/views/templates/twig/bordero/bordero.index.html.twig',
            [
                'title' => $this->translator->trans('Borderò', [], 'AdminBrtBordero'),
                'borderoRows' => $this->getBorderoRows(),
                'menu' => json_encode($this->getMenu()),
                'labelPreferences' => $this->getSettings(),
                'labelPermissions' => $this->getLabelPermissions(),
                'orderStates' => \OrderState::getOrderStates($this->context->getContext()->language->id),
                'defaultChangeOrderState' => 0,
            ],
        );
    }

    private function getMenu()
    {
        return [
            [
                'type' => 'dropdown',
                'icon' => 'list_alt',
                'label' => 'Etichette',
                'id' => 'menu1',
                'children' => [
                    [
                        'type' => 'link',
                        'icon' => 'add',
                        'label' => 'Nuova etichetta',
                        'href' => 'javascript:newBrtLabel();',
                    ],
                    [
                        'type' => 'link',
                        'icon' => 'delete',
                        'label' => 'Elimina etichette',
                        'href' => 'javascript:deleteBrtLabels();',
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'submenu',
                        'icon' => 'print',
                        'label' => 'Stampa',
                        'children' => [
                            [
                                'type' => 'link',
                                'icon' => 'print',
                                'label' => 'Stampa etichette',
                                'href' => 'javascript:printAllLabels();',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'type' => 'dropdown',
                'icon' => 'description',
                'label' => 'Bordero',
                'children' => [
                    [
                        'type' => 'link',
                        'icon' => 'tab',
                        'label' => 'Ultimo bordero',
                        'href' => 'javascript:showLastBordero();',
                    ],
                    [
                        'type' => 'link',
                        'icon' => 'history',
                        'label' => 'Storico bordero',
                        'href' => 'javascript:showHistory();',
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'link',
                        'icon' => 'print',
                        'label' => 'Stampa bordero',
                        'href' => 'javascript:printBordero();',
                    ],
                ],
            ],
            [
                'type' => 'dropdown',
                'icon' => 'settings',
                'label' => 'Configurazione',
                'id' => 'menu2',
                'children' => [
                    [
                        'type' => 'link',
                        'icon' => 'settings_applications',
                        'label' => 'Preferenze',
                        'href' => 'javascript:showPreferences();',
                    ],
                    [
                        'type' => 'link',
                        'icon' => 'security',
                        'label' => 'Permessi',
                        'href' => 'javascript:showPermissions();',
                    ],
                ],
            ],
        ];
    }

    private function getBorderoRows($searchTerm = '', $printed = false)
    {
        /** @var Connection */
        $conn = $this->getDoctrine()->getConnection();
        $prefix = _DB_PREFIX_;

        $sql = "
            SELECT
                r.id_brt_shipment_response AS id,
                CONCAT(
                    r.bordero_number,
                    ' del ',
                    DATE_FORMAT(r.bordero_date, '%d/%m/%Y')
                ) AS bordero,
                r.numeric_sender_reference,
                r.alphanumeric_sender_reference,
                r.consignee_company_name,
                r.consignee_address,
                r.consignee_zip_code,
                r.consignee_city,
                r.consignee_province_abbreviation,
                r.consignee_country_abbreviation_iso_alpha_2,
                r.consignee_contact_name,
                r.consignee_telephone,
                r.consignee_mobile_phone_number,
                r.consignee_email,
                r.cash_on_delivery,
                r.number_of_parcels,
                r.weight_kg,
                r.volume_m3,
                r.printed
            FROM {$prefix}brt_shipment_response r
            ";

        if ($printed) {
            $sql .= '
                WHERE r.printed = 1
            ';
        } else {
            $sql .= '
                WHERE r.printed = 0
                OR r.printed IS NULL
            ';
        }

        if ($searchTerm) {
            $sql .= '
                    AND (r.numeric_sender_reference LIKE :searchTerm
                        OR r.alphanumeric_sender_reference LIKE :searchTerm
                        OR r.consignee_company_name LIKE :searchTerm
                        OR r.consignee_address LIKE :searchTerm
                        OR r.consignee_zip_code LIKE :searchTerm
                        OR r.consignee_city LIKE :searchTerm
                        OR r.consignee_province_abbreviation LIKE :searchTerm
                        OR r.consignee_country_abbreviation_iso_alpha_2 LIKE :searchTerm
                        OR r.consignee_contact_name LIKE :searchTerm
                        OR r.consignee_telephone LIKE :searchTerm
                        OR r.consignee_mobile_phone_number LIKE :searchTerm
                        OR r.consignee_email LIKE :searchTerm
                        OR r.cash_on_delivery LIKE :searchTerm
                        OR r.number_of_parcels LIKE :searchTerm
                        OR r.weight_kg LIKE :searchTerm
                        OR r.volume_m3 LIKE :searchTerm)
                ';
        }

        $sql .= '
                ORDER BY r.id_brt_shipment_response ASC
            ';

        /** @var \Doctrine\DBAL\Statement $stmt */
        $stmt = $conn->prepare($sql);
        if ($searchTerm) {
            $stmt->bindValue('searchTerm', '%'.pSQL($searchTerm).'%');
        }
        $result = $stmt->executeQuery();
        $rows = $result->fetchAllAssociative();

        return $rows;
    }

    public function getTableDataAction(Request $request)
    {
        $searchTerm = $request->get('searchTerm');
        $rows = $this->getBorderoRows($searchTerm, false);

        return $this->json([
            'rows' => $rows,
            'title' => $this->translator->trans('Ultimo bordero', [], 'AdminBrtBordero'),
            'icon' => 'list_alt',
        ]);
    }

    public function getHistoryTableDataAction(Request $request)
    {
        $searchTerm = $request->get('searchTerm');
        $rows = $this->getBorderoRows($searchTerm, true);

        return $this->json([
            'rows' => $rows,
            'title' => $this->translator->trans('Storico bordero', [], 'AdminBrtBordero'),
            'icon' => 'history',
        ]);
    }

    public function newLabelAction(Request $request)
    {
        $conf = new BrtConfiguration();
        $content = $request->getContent();
        $data = json_decode($content, true);
        $showOrderId = $data['showOrderId'] ?? false;
        $id_lang = (int) $this->context->getContext()->language->id;

        return $this->json([
            'dialog' => $this->renderView(
                '@Modules/mpbrtapishipment/views/templates/twig/label/label.html.twig',
                [
                    'showOrderIdSearch' => $showOrderId,
                    'cod_currency' => $this->context->getContext()->currency->iso_code,
                    'service_type' => $conf->get('service_type'),
                    'network' => $conf->get('network'),
                    'delivery_freight_type_code' => $conf->get('delivery_freight_type_code'),
                    'cod_payment_type' => $conf->get('cod_payment_type'),
                    'orderStates' => \OrderState::getOrderStates($id_lang),
                    'defaultChangeOrderState' => 0,
                    'brt_environment' => $conf->get('environment'),
                ]
            ),
        ]);
    }

    public function printLabelsAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $ids = $data['ids'] ?? [];
        if (!$ids) {
            return $this->json([
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ]);
        }

        $ids = array_unique(array_map(fn (int $id) => (int) $id, $ids));

        if (empty($ids)) {
            return $this->json([
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ]);
        }

        return $this->json($this->getPdfLabels($ids));
    }

    public function printAllLabelsAction(Request $request)
    {
        $ids = $this->brtShipmentResponseRepository->getBorderoRowsId();

        if (!$ids) {
            return $this->json([
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ]);
        }

        return $this->json($this->getPdfLabels($ids));
    }

    public function printBorderoAction(Request $request)
    {
        $prefix = _DB_PREFIX_;
        $content = $request->getContent();
        $data = json_decode($content, true);
        $number = $data['number'] ?? false;
        $date = $data['date'] ?? false;

        if (!$number || !$date) {
            // Stampo l'ultimo bordero non stampato
            $query = "
                SELECT
                    *
                FROM
                    {$prefix}brt_shipment_response
                WHERE
                    printed = 0
                    OR printed is NULL
                ORDER BY
                    id_brt_shipment_response ASC
            ";
            $result = $this->getDoctrine()->getConnection()->executeQuery($query);
            $list = $result->fetchAllAssociative();
        } else {
            // Stampo il bordero con il numero e la data specificata
            $query = "
                SELECT
                    *
                FROM
                    {$prefix}brt_shipment_response
                WHERE
                    numeric_sender_reference = :number
                    AND DATE(date_add) = :date
                ORDER BY
                    id_brt_shipment_response ASC
            ";
            $result = $this->getDoctrine()->getConnection()->executeQuery($query, [
                'number' => $number,
                'date' => $date,
            ]);
            $list = $result->fetchAllAssociative();
        }

        if (!$list) {
            return $this->json([
                'success' => false,
                'message' => 'Nessun bordero trovato',
            ]);
        }

        $borderoPdf = new BrtBorderoPdf($list);
        $result = $borderoPdf->render();

        return $this->json([
            'success' => $result['success'],
            'pdfBase64' => $result['pdf'],
            'ids' => $result['ids'],
        ]);
    }

    public function updatePrintedAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $ids = $data['ids'] ?? [];
        if (!$ids) {
            return $this->json([
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ]);
        }

        $ids = array_unique(array_map(fn (int $id) => (int) $id, $ids));

        if (empty($ids)) {
            return $this->json([
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ]);
        }

        $result = $this->brtShipmentResponseRepository->updatePrinted($ids);

        return $this->json([
            'success' => $result,
        ]);
    }

    public function getPdfLabels($ids)
    {
        /** @var Connection */
        $conn = $this->getDoctrine()->getConnection();
        $prefix = _DB_PREFIX_;
        $sql = "
            SELECT
                stream
            FROM 
                {$prefix}brt_shipment_response_label
            WHERE
                numeric_sender_reference IN (:ids)
            ORDER BY
                numeric_sender_reference ASC, number ASC
        ";
        $result = $conn->executeQuery($sql, ['ids' => implode(',', $ids)]);
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

    public function fillOrderDetailsAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $orderId = (int) $data['orderId'] ?? false;

        if (!$orderId) {
            return $this->json([
                'success' => false,
                'message' => 'Order ID non valido',
            ]);
        }

        $details = $this->getOrderLabelDetails->run($orderId);

        return $this->json([
            'success' => true,
            'data' => $details,
        ]);
    }

    public function showPreferencesAction()
    {
        $settings = $this->getSettings();
        $id_lang = (int) $this->context->getContext()->language->id;

        $html = $this->renderView(
            '@Modules/mpbrtapishipment/views/templates/twig/preferences/preferences.html.twig',
            [
                'settings' => $settings,
                'orderStates' => \OrderState::getOrderStates($id_lang),
                'cashOnDeliveryModules' => \PaymentModule::getPaymentModules(),
                'autoweightControllerURL' => \Module::getInstanceByName('mpbrtapishipment')->getAutoWeightUrl(),
            ]
        );

        return $this->json([
            'dialog' => $html,
        ]);
    }

    public function showPermissionsAction()
    {
        $permissions = $this->getPermissions();

        return $this->render(
            '@Modules/mpbrtapishipment/views/templates/twig/preferences/permissions.html.twig',
            [
                'permissions' => $permissions,
            ]
        );
    }

    public function readParcelsAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $numericSenderReference = (int) $data['numericSenderReference'] ?? false;

        if (!$numericSenderReference) {
            return $this->json([
                'success' => false,
                'message' => 'Label non valido',
            ]);
        }

        $response = $this->getParcelsMeasures->run($numericSenderReference);

        return $this->json($response);
    }

    protected function getSettings()
    {
        $conf = new BrtConfiguration();

        return $conf->getSettings();
    }

    protected function getPermissions()
    {
        return [
            'employees' => \Employee::getEmployees(),
        ];
    }

    public function saveSettingsAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $preferences = $data['preferences'] ?? [];
        $permissions = $data['permissions'] ?? [];
        $result =
            [
                'success' => true,
                'message' => 'nessuna impostazione da salvare',
            ];

        if ($preferences) {
            $result = $this->savePreferences($preferences);
        }

        if ($permissions) {
            $result = $this->savePermissions($permissions);
        }

        return $this->json($result);
    }

    protected function getLabelPermissions()
    {
        return [
            'create' => $this->isGranted('CREATE_LABEL'),
            'read' => $this->isGranted('READ_LABEL'),
            'update' => $this->isGranted('UPDATE_LABEL'),
            'delete' => $this->isGranted('DELETE_LABEL'),
            'employees' => \Employee::getEmployees(),
        ];
    }

    protected function savePreferences($preferences)
    {
        $conf = new BrtConfiguration();
        $keys = $conf->getConfigurationKeys();
        foreach ($keys as $key) {
            $conf->set($key, $preferences);
        }

        return [
            'success' => true,
            'message' => 'Impostazioni salvate con successo',
        ];
    }

    protected function savePermissions($permissions)
    {
        $conf = new BrtConfiguration();
        $conf->set('environment', $permissions['BRT_ENVIRONMENT'] ?? 'SANDVBOX');
        $conf->set('sandbox_user_id', $permissions['BRT_SANDBOX_USER_ID'] ?? '');
        $conf->set('sandbox_password', $permissions['BRT_SANDBOX_PASSWORD'] ?? '');
        $conf->set('production_user_id', $permissions['BRT_PRODUCTION_USER_ID'] ?? '');
        $conf->set('production_password', $permissions['BRT_PRODUCTION_PASSWORD'] ?? '');
        $conf->set('sandbox_departure_depot', $permissions['BRT_SANDBOX_DEPARTURE_DEPOT'] ?? '');
        $conf->set('production_departure_depot', $permissions['BRT_PRODUCTION_DEPARTURE_DEPOT'] ?? '');
        $conf->set('cod_payment_type', $permissions['BRT_COD_PAYMENT_TYPE'] ?? '');

        return [
            'success' => true,
            'message' => 'Impostazioni salvate con successo',
        ];
    }

    protected function toCamelCaseArray($array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            if ('consignee_zip_code' == $key) {
                $newArray['consigneeZIPCode'] = $value;
            } elseif ('weight_kg' == $key) {
                $newArray['weightKG'] = $value;
            } elseif ('consignee_email' == $key) {
                $newArray['consigneeEMail'] = $value;
            } elseif ('is_cod_mandatory' == $key) {
                $newArray['isCODMandatory'] = $value;
            } elseif ('consignee_country_abbreviation_iso_alpha_2' == $key) {
                $newArray['consigneeCountryAbbreviationISOAlpha2'] = $value;
            } elseif (preg_match('/_/', $key)) {
                $newArray[\Tools::toCamelCase($key)] = $value;
            } else {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }

    /***********************
     *** API BRT MANAGER ***
     ***********************/
    public function createBrtRequestAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $numericSenderReference = $data['numericSenderReference'] ?? false;

        if (!$numericSenderReference) {
            return $this->json([
                'success' => false,
                'message' => 'Label non valido',
            ]);
        }

        $result = $this->brtApiManager->createBrtRequest($numericSenderReference);

        return $this->json($result);
    }

    public function saveBrtRequestAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $numericSenderReference = (int) ($data['labelId'] ?? $data['label']['numeric_sender_reference']);
        $labelDetails = $data['label'] ?? [];
        $packages = $data['packages'] ?? [];

        if (!$numericSenderReference) {
            return $this->json([
                'success' => false,
                'message' => 'Label ID non valido',
            ]);
        }

        if (!$labelDetails) {
            return $this->json([
                'success' => false,
                'message' => 'Dettagli etichetta non validi',
            ]);
        }

        $result = $this->brtApiManager->saveBrtRequest($numericSenderReference, $labelDetails, $packages);

        return $this->json($result);
    }

    public function saveBrtResponseAction(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        $executionMessage = $data['executionMessage'] ?? false;
        $response = $data['response'] ?? false;
        $labels = $data['labels'] ?? [];

        return $this->json($this->brtApiManager->saveBrtResponse($executionMessage, $response, $labels));
    }
}
