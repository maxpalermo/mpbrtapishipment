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

namespace MpSoft\MpBrtApiShipment\Helpers;

use MpSoft\MpBrtApiShipment\Api\BrtAuthManager;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;

class BrtBorderoPdf extends \TCPDF
{
    public const PDF_ORIENTATION_PORTRAIT = 'P';
    public const PDF_ORIENTATION_LANDSCAPE = 'L';
    private $rows;
    private $force;
    private $tplRows;

    public function __construct($rows, $force = null)
    {
        $this->rows = $rows;
        $this->force = $force;
        parent::__construct(self::PDF_ORIENTATION_LANDSCAPE, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    }

    public function getRows()
    {
        return $this->rows;
    }

    public function header()
    {
        parent::header();
        $account = new BrtAuthManager();
        $cust_code = $account->getAccount()->userID;
        $shopData = $this->getShopInfo();
        $imprendo = "[$cust_code] {$shopData['name']} - {$shopData['address']['street']} {$shopData['address']['city']} {$shopData['address']['zip']} {$shopData['address']['country']}";
        $this->setY(8);
        $this->cell(0, 0, $imprendo, 0, 0, 'C', 0, '', 0, true, 'C', 'M');
    }

    public function getShopInfo()
    {
        $context = \Context::getContext();

        $shopData = [
            'name' => $context->shop->name,
            'email' => \Configuration::get('PS_SHOP_EMAIL'),
            'address' => [
                'street' => \Configuration::get('PS_SHOP_ADDR1'),
                'city' => \Configuration::get('PS_SHOP_CITY'),
                'zip' => \Configuration::get('PS_SHOP_CODE'),
                'country' => \Country::getNameById(
                    $context->language->id,
                    \Configuration::get('PS_SHOP_COUNTRY_ID')
                ),
            ],
            'vat' => \Configuration::get('PS_VAT_NUMBER'),
            'fiscal_code' => \Configuration::get('PS_SHOP_FISCAL_CODE'),
        ];

        return $shopData;
    }

    public function footer()
    {
        parent::footer();
        $lastBordero = ModelBrtShipmentBordero::getLastPrintedBorderoNumber();
        $year = date('Y');
        $date = date('d/m/Y H:i:s');
        $this->setY(-6);
        $this->cell(0, 0, "Borderò {$lastBordero}/{$year} stampato il {$date}", 0, 0, 'C', 0, '', 0, true, 'C', 'M');
    }

    public function render()
    {
        // set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Massimiliano Palermo');
        $this->SetTitle('Bordero del '.date('d/m/Y - H:i:s'));
        $this->SetSubject('Bordero per Bartolini');
        $this->SetKeywords('TCPDF, PDF, bordero, bartolini,'.date('d/m/Y - H:i:s'));

        // set default header data
        // $this->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 027', PDF_HEADER_STRING);

        // set header and footer fonts
        $this->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $this->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);

        // set default monospaced font
        $this->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $this->SetHeaderMargin(PDF_MARGIN_HEADER);
        $this->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $this->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $this->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once dirname(__FILE__).'/lang/eng.php';
            $this->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set a barcode on the page footer
        // $pdf->setBarcode(date('Y-m-d H:i:s'));

        // set font
        $this->SetFont('helvetica', '', 8);

        // add a page
        $this->AddPage();
        $this->SetFont('helvetica', '', 8);

        // define barcode style
        $style = [
            'position' => '',
            'align' => 'C',
            'stretch' => false,
            'fitwidth' => true,
            'cellfitalign' => '',
            'border' => true,
            'hpadding' => 'auto',
            'vpadding' => 'auto',
            'fgcolor' => [0, 0, 0],
            'bgcolor' => false, // array(255,255,255),
            'text' => true,
            'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 4,
        ];

        $rows = $this->getFormattedRows();
        $htmlTable = $this->renderHtmlPage();

        $this->writeHTML($htmlTable);

        // LAST PAGE
        $this->AddPage();
        $this->SetFont('helvetica', '', 12);
        $module = \Module::getInstanceByName('mpbrtapishipment');
        $template = $module->getLocalPath().'views/templates/bordero/riepilogo.tpl';
        $smarty = \Context::getContext()->smarty->createTemplate($template);
        $smarty->assign(
            [
                'totali' => $this->getRiepilogo(),
            ]
        );

        $html = $smarty->fetch();
        $this->writeHTML($html);

        // ---------------------------------------------------------
        $filename = 'bordero_'.date('YmdHis').'.pdf';

        return [
            'success' => true,
            'pdf' => base64_encode($this->Output($filename, 'S')),
            'ids' => array_column($this->rows, 'id_brt_shipment_response'),
        ];
    }

    private function printPdf()
    {
        $filename = 'bordero_'.date('YmdHis').'.pdf';
        $this->Output($filename, 'I');
    }

    private function getFormattedRows()
    {
        $rows = $this->rows;
        $cellStyle = $this->getCellStyle();
        $output = [];

        foreach ($rows as $row) {
            $tplRow = [];
            foreach ($cellStyle as $key => $value) {
                $tplRow[$key] = [
                    'row1' => [
                        'label' => $value['row1'],
                        'style' => $this->formatStyle($value['style']),
                        'value' => $this->formatCellValue('row1', $value, $row),
                    ],
                    'row2' => [
                        'label' => $value['row2'],
                        'style' => $this->formatStyle($value['style']),
                        'value' => $this->formatCellValue('row2', $value, $row),
                    ],
                ];
            }
            $output[] = $tplRow;
        }

        $this->tplRows = $output;

        return $output;
    }

    private function formatStyle($style)
    {
        $styleString = '';
        foreach ($style as $key => $value) {
            $styleString .= $key.': '.$value.';';
        }

        return $styleString;
    }

    private function formatCellValue($row, $style, $values)
    {
        $fields = $style['fields'][$row];
        $value = '';
        foreach ($fields as $field) {
            $value .= $values[$field].' ';
        }

        return trim($value);
    }

    private function getCellStyle()
    {
        return [
            'col01' => [
                'row1' => 'Destinatario',
                'row2' => '',
                'style' => [
                    'width' => '6cm',
                    'text-align' => 'left',
                ],
                'fields' => [
                    'row1' => [
                        'consignee_company_name',
                    ],
                    'row2' => [],
                ],
            ],
            'col02' => [
                'row1' => 'Indirizzo',
                'row2' => 'Cap Città Prov',
                'style' => [
                    'width' => '6cm',
                    'text-align' => 'left',
                ],
                'fields' => [
                    'row1' => [
                        'consignee_address',
                    ],
                    'row2' => [
                        'consignee_zip_code',
                        'consignee_city',
                        'consignee_province_abbreviation',
                    ],
                ],
            ],
            'col03' => [
                'row1' => 'Rif. numerico',
                'row2' => 'Riferimento',
                'style' => [
                    'width' => '3cm',
                    'text-align' => 'center',
                ],
                'fields' => [
                    'row1' => [
                        'numeric_sender_reference',
                    ],
                    'row2' => [
                        'alphanumeric_sender_reference',
                    ],
                ],
            ],
            'col04' => [
                'row1' => 'Cod',
                'row2' => 'Bolla',
                'style' => [
                    'width' => '1cm',
                    'text-align' => 'right',
                ],
                'fields' => [
                    'row1' => [],
                    'row2' => [],
                ],
            ],
            'col05' => [
                'row1' => 'Importo',
                'row2' => 'C/ass',
                'style' => [
                    'width' => '2cm',
                    'text-align' => 'right',
                ],
                'fields' => [
                    'row1' => [
                        'cash_on_delivery',
                    ],
                    'row2' => [],
                ],
            ],
            'col06' => [
                'row1' => 'Colli',
                'row2' => '',
                'style' => [
                    'width' => '1cm',
                    'text-align' => 'right',
                ],
                'fields' => [
                    'row1' => [
                        'number_of_parcels',
                    ],
                    'row2' => [],
                ],
            ],
            'col07' => [
                'row1' => 'Peso',
                'row2' => 'Volume',
                'style' => [
                    'width' => '3cm',
                    'text-align' => 'right',
                ],
                'fields' => [
                    'row1' => [
                        'weight_kg',
                    ],
                    'row2' => [
                        'volume_m3',
                    ],
                ],
            ],
            'col08' => [
                'row1' => 'Segnacolli',
                'row2' => 'Dal - Al',
                'style' => [
                    'width' => '3cm',
                    'text-align' => 'center',
                ],
                'fields' => [
                    'row1' => [
                        'parcel_number_from',
                    ],
                    'row2' => [
                        'parcel_number_to',
                    ],
                ],
            ],
        ];
    }

    private function setPrinted()
    {
        $db = \Db::getInstance();
        $printed_date = date('Y-m-d H:i:s');
        $bordero_number = ModelBrtShipmentBordero::getLatestBorderoNumber();
        ++$bordero_number;
        $id_employee = (int) \Context::getContext()->employee->id;

        $result = $db->update(
            ModelBrtShipmentBordero::$definition['table'],
            [
                'bordero_number' => $bordero_number,
                'bordero_status' => 1,
                'printed' => 1,
                'printed_date' => $printed_date,
                'id_employee' => $id_employee,
            ],
            'bordero_number = 0 && printed = 0'
        );

        return $result;
    }

    private function getRiepilogo()
    {
        $result = [
            'spedizioni' => 0,
            'colli' => 0,
            'numcass' => 0,
            'cashOnDelivery' => 0,
            'weightKg' => 0,
            'volumeM3' => 0,
        ];
        foreach ($this->rows as $row) {
            ++$result['spedizioni'];

            if (isset($row['number_of_parcels'])) {
                $result['colli'] += $row['number_of_parcels'];
            }
            if (isset($row['cash_on_delivery']) && $row['cash_on_delivery'] > 0) {
                ++$result['numcass'];
                $result['cashOnDelivery'] += $row['cash_on_delivery'];
            }
            if (isset($row['weight_kg'])) {
                $result['weightKg'] += $row['weight_kg'];
            }
            if (isset($row['volume_m3'])) {
                $result['volumeM3'] += $row['volume_m3'];
            }
        }

        return $result;
    }

    private function renderHtmlPage()
    {
        $cols = count($this->tplRows[0] ?? []);
        $html = <<<HTML
            <table cellborder="0" cellspacing="0">
                <thead>
                    {$this->getTableHeader()}
                    <tr>
                        <th colspan="{$cols}">
                            <hr>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    {$this->getTableBody()}
                </tbody>
            </table>
        HTML;

        return $html;
    }

    private function getTableHeader()
    {
        $html = '';
        $row = $this->tplRows[0] ?? [];
        $row1 = [];
        $row2 = [];
        foreach ($row as $column) {
            $row1[] = $column['row1'];
            $row2[] = $column['row2'];
        }
        $html .= "<tr>\n";
        foreach ($row1 as $cell) {
            $html .= "<th style=\"font-weight: bold; {$cell['style']}\">{$cell['label']}</th>\n";
        }
        $html .= "</tr>\n";
        $html .= "<tr>\n";
        foreach ($row2 as $cell) {
            $html .= "<th style=\"font-weight: bold; {$cell['style']}\">{$cell['label']}</th>\n";
        }
        $html .= "</tr>\n";

        return $html;
    }

    private function getTableBody()
    {
        $html = '';
        $rows = $this->tplRows ?? [];
        foreach ($rows as $cols) {
            $row1 = [];
            $row2 = [];
            foreach ($cols as $cell) {
                $row1[] = $cell['row1'];
                $row2[] = $cell['row2'];
            }
            $html .= "<tr>\n";
            foreach ($row1 as $cell) {
                $label = $cell['label'];
                $value = $cell['value'];
                if (strlen($value) > 30) {
                    $value = substr($value, 0, 30);
                }

                switch (strtolower($label)) {
                    case 'importo':
                        $value = (float) $value;
                        if ($value) {
                            $value = number_format($value, 2, ',', '.');
                        } else {
                            $value = '--';
                        }
                        break;
                    case 'peso':
                        $peso = (float) $value;
                        if ($peso) {
                            $value = number_format($peso, 1, ',', '.').' Kg';
                        }
                        break;
                    case 'volume':
                        $volume = (float) $value;
                        if ($volume) {
                            $value = number_format($volume, 3, ',', '.').' m3';
                        }
                        break;
                    default:
                        break;
                }

                $html .= "<td style=\"{$cell['style']}\">{$value}</td>\n";
            }
            $html .= "</tr>\n";
            $html .= "<tr>\n";
            foreach ($row2 as $cell) {
                $label = $cell['label'];

                $value = $cell['value'];
                if (strlen($value) > 30) {
                    $value = substr($value, 0, 30);
                }

                switch (strtolower($label)) {
                    case 'volume':
                        $volume = (float) $value;
                        if ($volume) {
                            $value = number_format($volume, 3, ',', '.').' m3';
                        }
                        break;
                    default:
                        break;
                }

                $html .= "<td style=\"font-weight: bold; {$cell['style']}\">{$value}</td>\n";
            }
            $html .= "</tr>\n";
        }

        return $html;
    }
}
