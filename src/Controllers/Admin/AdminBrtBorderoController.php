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
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponseLabel;
use MpSoft\MpBrtApiShipment\Services\GetOrderLabelDetails;
use PrestaShop\PrestaShop\Adapter\LegacyContext;
use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

class AdminBrtBorderoController extends FrameworkBundleAdminController
{
    /** @var \Module */
    private $module;

    /** @var GetOrderLabelDetails */
    private $getOrderLabelDetails;
    /** @var LegacyContext */
    private $context;
    /** @var TranslatorInterface */
    private $translator;

    public function __construct(GetOrderLabelDetails $getOrderLabelDetails, LegacyContext $context, TranslatorInterface $translator)
    {
        parent::__construct();
        $this->module = \Module::getInstanceByName('mpbrtapishipment');
        $this->getOrderLabelDetails = $getOrderLabelDetails;
        $this->context = $context;
        $this->translator = $translator;
    }

    public function indexAction()
    {
        return $this->render(
            '@Modules/mpbrtapishipment/views/templates/twig/bordero/bordero.index.html.twig',
            [
                'title' => $this->translator->trans('Borderò', [], 'AdminBrtBordero'),
                'borderoRows' => $this->getBorderoRows(),
                'menu' => json_encode($this->getMenu()),
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
                                'label' => 'Stampa bordero',
                                'href' => 'javascript:printBordero();',
                            ],
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
                        'icon' => 'print',
                        'label' => 'Ultimo bordero',
                        'href' => 'javascript:showLastBordero();',
                    ],
                    [
                        'type' => 'link',
                        'icon' => 'history',
                        'label' => 'Storico bordero',
                        'href' => 'javascript:showHistory();',
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
                b.id_brt_shipment_bordero AS id,
                CONCAT(
                    b.bordero_number,
                    ' del ',
                    DATE_FORMAT(b.bordero_date, '%d/%m/%Y')
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
                b.printed_date AS print_date
            FROM {$prefix}brt_shipment_response r
            LEFT JOIN {$prefix}brt_shipment_bordero b
                ON r.id_brt_shipment_response = b.id_brt_shipment_response
                WHERE b.printed = :printed
            ";

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
                ORDER BY b.id_brt_shipment_bordero DESC
            ';

        /** @var \Doctrine\DBAL\Statement $stmt */
        $stmt = $conn->prepare($sql);
        if ($searchTerm) {
            $stmt->bindValue('searchTerm', '%'.pSQL($searchTerm).'%');
        }
        $stmt->bindValue('printed', (int) $printed);

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
        $content = $request->getContent();
        $data = json_decode($content, true);
        $showOrderId = $data['showOrderId'] ?? false;

        return $this->json([
            'dialog' => $this->renderView(
                '@Modules/mpbrtapishipment/views/templates/twig/label/label.html.twig',
                [
                    'showOrderIdSearch' => $showOrderId,
                    'cod_currency' => $this->context->getContext()->currency->iso_code,
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
        $ids = ModelBrtShipmentBordero::getIdList();

        if (!$ids) {
            return $this->json([
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ]);
        }

        return $this->json($this->getPdfLabels($ids));
    }

    public function getPdfLabels($ids)
    {
        /** @var Connection */
        $conn = $this->getDoctrine()->getConnection();
        $prefix = _DB_PREFIX_;
        $sql = "
            SELECT
                lbl.stream
            FROM {$prefix}brt_shipment_response_label lbl
            INNER JOIN {$prefix}brt_shipment_response b ON b.id_brt_shipment_response = lbl.id_brt_shipment_response
            INNER JOIN {$prefix}brt_shipment_bordero a ON a.id_brt_shipment_response = b.id_brt_shipment_response
            WHERE a.id_brt_shipment_bordero IN (:ids)
            ORDER BY a.id_brt_shipment_bordero ASC
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

        $html = $this->renderView(
            '@Modules/mpbrtapishipment/views/templates/twig/preferences/preferences.html.twig',
            [
                'settings' => $settings,
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

    protected function getSettings()
    {
        $conf = new BrtConfiguration();

        return [
            'BRT_ENVIRONMENT' => $conf->get('environment'),
            'BRT_SANDBOX_USER_ID' => $conf->get('sandbox_user_id'),
            'BRT_SANDBOX_PASSWORD' => $conf->get('sandbox_password'),
            'BRT_SANDBOX_DEPARTURE_DEPOT' => $conf->get('sandbox_departure_depot'),
            'BRT_PRODUCTION_USER_ID' => $conf->get('production_user_id'),
            'BRT_PRODUCTION_PASSWORD' => $conf->get('production_password'),
            'BRT_PRODUCTION_DEPARTURE_DEPOT' => $conf->get('production_departure_depot'),
            'BRT_SERVICE_TYPE' => $conf->get('service_type'),
            'BRT_NETWORK' => $conf->get('network'),
            'BRT_DELIVERY_FREIGHT_TYPE_CODE' => $conf->get('delivery_freight_type_code'),
            'BRT_COD_PAYMENT_TYPE' => $conf->get('cod_payment_type'),
            'BRT_IS_ALERT_REQUIRED' => $conf->get('is_alert_required'),
            'BRT_IS_LABEL_PRINTED' => $conf->get('is_label_printed'),
            'BRT_LABEL_TYPE' => $conf->get('label_type'),
            'BRT_IS_BORDER_PRINTED' => $conf->get('is_border_printed'),
            'BRT_IS_BARCODE_PRINTED' => $conf->get('is_barcode_printed'),
            'BRT_IS_LOGO_PRINTED' => $conf->get('is_logo_printed'),
            'BRT_LABEL_OFFSET_X' => $conf->get('label_offset_x'),
            'BRT_LABEL_OFFSET_Y' => $conf->get('label_offset_y'),
        ];
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

        if ($preferences) {
            $result = $this->savePreferences($preferences);
        }

        if ($permissions) {
            $result = $this->savePermissions($permissions);
        }

        return $this->json($result);
    }

    protected function savePreferences($preferences)
    {
        $conf = new BrtConfiguration();
        $conf->set('environment', $preferences['BRT_ENVIRONMENT'] ?? 'SANDVBOX');
        $conf->set('sandbox_user_id', $preferences['BRT_SANDBOX_USER_ID'] ?? '');
        $conf->set('sandbox_password', $preferences['BRT_SANDBOX_PASSWORD'] ?? '');
        $conf->set('sandbox_departure_depot', $preferences['BRT_SANDBOX_DEPARTURE_DEPOT'] ?? '');
        $conf->set('production_user_id', $preferences['BRT_PRODUCTION_USER_ID'] ?? '');
        $conf->set('production_password', $preferences['BRT_PRODUCTION_PASSWORD'] ?? '');
        $conf->set('production_departure_depot', $preferences['BRT_PRODUCTION_DEPARTURE_DEPOT'] ?? '');
        $conf->set('service_type', $preferences['BRT_SERVICE_TYPE'] ?? '');
        $conf->set('network', $preferences['BRT_NETWORK'] ?? '');
        $conf->set('delivery_freight_type_code', $preferences['BRT_DELIVERY_FREIGHT_TYPE_CODE'] ?? '');
        $conf->set('cod_payment_type', $preferences['BRT_COD_PAYMENT_TYPE'] ?? '');
        $conf->set('is_alert_required', $preferences['BRT_IS_ALERT_REQUIRED'] ?? '');
        $conf->set('is_label_printed', $preferences['BRT_IS_LABEL_PRINTED'] ?? '');
        $conf->set('label_type', $preferences['BRT_LABEL_TYPE'] ?? '');
        $conf->set('is_border_printed', $preferences['BRT_IS_BORDER_PRINTED'] ?? '');
        $conf->set('is_barcode_printed', $preferences['BRT_IS_BARCODE_PRINTED'] ?? '');
        $conf->set('is_logo_printed', $preferences['BRT_IS_LOGO_PRINTED'] ?? '');
        $conf->set('label_offset_x', $preferences['BRT_LABEL_OFFSET_X'] ?? '');
        $conf->set('label_offset_y', $preferences['BRT_LABEL_OFFSET_Y'] ?? '');

        return [
            'success' => true,
            'message' => 'Impostazioni salvate con successo',
        ];
    }

    protected function savePermissions($permissions)
    {
        $conf = new BrtConfiguration();
        $conf->set('environment', $permissions['BRT_ENVIRONMENT']);
        $conf->set('sandbox_user_id', $permissions['BRT_SANDBOX_USER_ID']);
        $conf->set('sandbox_password', $permissions['BRT_SANDBOX_PASSWORD']);
        $conf->set('production_user_id', $permissions['BRT_PRODUCTION_USER_ID']);
        $conf->set('production_password', $permissions['BRT_PRODUCTION_PASSWORD']);
        $conf->set('sandbox_departure_depot', $permissions['BRT_SANDBOX_DEPARTURE_DEPOT']);
        $conf->set('production_departure_depot', $permissions['BRT_PRODUCTION_DEPARTURE_DEPOT']);
        $conf->set('cod_payment_type', $permissions['BRT_COD_PAYMENT_TYPE']);

        return [
            'success' => true,
            'message' => 'Impostazioni salvate con successo',
        ];
    }
}
