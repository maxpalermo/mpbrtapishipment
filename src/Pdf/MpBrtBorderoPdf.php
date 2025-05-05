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

namespace MpSoft\MpBrtApiShipment\Pdf;

use MpSoft\MpBrtApiShipment\Api\BrtAuthManager;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentBordero;
use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentResponse;

class MpBrtBorderoPdf extends \TCPDF
{
    public const PDF_ORIENTATION_PORTRAIT = 'P';
    public const PDF_ORIENTATION_LANDSCAPE = 'L';
    private $boxes;
    private $bordero_number;
    private $bordero_date;
    private $force;
    private $context;

    public function __construct($boxes, $bordero_number, $bordero_date, $force = null)
    {
        $this->boxes = $boxes;
        $this->bordero_number = $bordero_number;
        $this->bordero_date = $bordero_date;
        $this->force = $force;
        $this->context = \Context::getContext();
        $locale = \Tools::getContextLocale($this->context);
        $this->context->currentLocale = $locale;
        parent::__construct(self::PDF_ORIENTATION_LANDSCAPE, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    }

    public function header()
    {
        parent::header();
        $account = new BrtAuthManager();
        $cust_code = $account->getAccount()->userID;
        $shop_name = \Tools::strtoupper(\Configuration::get('PS_SHOP_NAME'));
        $shop_address = \Configuration::get('PS_SHOP_ADDR1');
        $shop_city = \Configuration::get('PS_SHOP_CITY');
        $shop_zipcode = \Configuration::get('PS_SHOP_POSTCODE');
        $shop_state = \Configuration::get('PS_SHOP_STATE');

        $shop = "[$cust_code] $shop_name - $shop_address - $shop_zipcode $shop_city ($shop_state)";
        $this->setY(8);
        $this->cell(0, 0, $shop, 0, 0, 'C', 0, '', 0, true, 'C', 'M');
    }

    public function footer()
    {
        parent::footer();
        $this->setY(-6);
        $this->cell(0, 0, date('d/m/Y H:i:s'), 0, 0, 'C', 0, '', 0, true, 'C', 'M');
    }

    public function createPdf()
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

        $smarty = $this->context->smarty;
        $module = \Module::getInstanceByName('mpbrtapishipment');
        $path = $module->getLocalPath().'views/templates/bordero/page.tpl';
        $tpl = $smarty->createTemplate($path);
        $tpl->assign(
            [
                'cellstyle' => $this->setCellStyle(),
                'rows' => $this->getRows(),
            ]
        );

        $html = $tpl->fetch();
        $this->writeHTML($html);

        // LAST PAGE
        $this->AddPage();
        $this->SetFont('helvetica', '', 12);
        $path = $module->getLocalPath().'views/templates/bordero/riepilogo.tpl';
        $tpl = $smarty->createTemplate($path);
        $tpl->assign(
            [
                'totali' => $this->getRiepilogo(),
            ]
        );

        $html = $tpl->fetch();
        $this->writeHTML($html);
    }

    public function ajaxRender()
    {
        $this->createPdf();

        $pdf = $this->Output('bordero_'.date('YmdHis').'.pdf', 'S');

        return $pdf;
    }

    public function render()
    {
        $this->createPdf();

        // ---------------------------------------------------------

        $filename = 'bordero_'.date('YmdHis').'.pdf';
        // $file_attachment['content'] = $this->Output($filename, 'S');
        // $file_attachment['name'] = $filename;
        // $file_attachment['mime'] = 'application/pdf';

        $this->Output($filename, 'I');
    }

    private function setCellStyle()
    {
        $cellStyle = $this->getCellStyle();
        foreach ($cellStyle as &$c) {
            $style = [];
            foreach ($c as $key => $value) {
                if ('label1' != $key && 'label2' != $key) {
                    $style[] = $key.': '.$value.';';
                }
            }
            $c['style'] = implode('', $style);
        }

        return $cellStyle;
    }

    private function getCellStyle()
    {
        return [
            'destinatario' => [
                'label1' => 'Destinatario',
                'label2' => '',
                'width' => '6cm',
                'text-align' => 'left',
            ],
            'indirizzo' => [
                'label1' => 'Indirizzo',
                'label2' => 'Cap Città Prov',
                'width' => '6cm',
                'text-align' => 'left',
            ],
            'riferimento' => [
                'label1' => 'Rif. numerico',
                'label2' => 'Riferimento',
                'width' => '3cm',
                'text-align' => 'center',
            ],
            'bolla' => [
                'label1' => 'Cod',
                'label2' => 'Bolla',
                'width' => '1cm',
                'text-align' => 'right',
            ],
            'cass' => [
                'label1' => 'Importo',
                'label2' => 'C/ass',
                'width' => '2cm',
                'text-align' => 'right',
            ],
            'colli' => [
                'label1' => 'Colli',
                'label2' => '',
                'width' => '1cm',
                'text-align' => 'right',
            ],
            'peso' => [
                'label1' => 'Peso',
                'label2' => '',
                'width' => '2cm',
                'text-align' => 'right',
            ],
            'volume' => [
                'label1' => 'Volume',
                'label2' => '',
                'width' => '3cm',
                'text-align' => 'right',
            ],
            'segnacolli' => [
                'label1' => 'Segnacolli',
                'label2' => 'Dal - Al',
                'width' => '3cm',
                'text-align' => 'center',
            ],
        ];
    }

    private function getBordero()
    {
        $rows = ModelBrtShipmentBordero::getUnprintedBorderoRows();

        return $rows;
    }

    private function getRows()
    {
        $rows = [];
        $style = $this->setCellStyle();
        $values = $this->getBordero();

        foreach ($values as $vrow) {
            $row = [
                'col1' => [
                    'row1' => \Tools::strtoupper($vrow['consignee_company_name']),
                    'row2' => 'Tipo di servizio: '.\Tools::strtoupper($vrow['service_type']),
                ],
                'col2' => [
                    'row1' => \Tools::strtoupper($vrow['consignee_address']),
                    'row2' => \Tools::strtoupper($vrow['consignee_zip_code'])
                        .' - '.\Tools::strtoupper($vrow['consignee_city'])
                        .' - '.\Tools::strtoupper($vrow['consignee_province_abbreviation']),
                ],
                'col3' => [
                    'row1' => \Tools::strtoupper($vrow['numeric_sender_reference']),
                    'row2' => \Tools::strtoupper($vrow['alphanumeric_sender_reference']),
                ],
                'col4' => [
                    'row1' => '',
                    'row2' => '',
                ],
                'col5' => [
                    'row1' => $this->context->currentLocale->formatPrice($vrow['cash_on_delivery'], $this->context->currency->iso_code),
                    'row2' => '',
                ],
                'col6' => [
                    'row1' => \Tools::strtoupper($vrow['number_of_parcels']),
                    'row2' => '',
                ],
                'col7' => [
                    'row1' => number_format($vrow['weight_kg'], 3, ',', ' '),
                    'row2' => '',
                ],
                'col8' => [
                    'row1' => number_format($vrow['volume_m3'], 3, ',', ' '),
                    'row2' => '',
                ],
                'col9' => [
                    'row1' => \Tools::strtoupper($vrow['parcel_number_from']),
                    'row2' => \Tools::strtoupper($vrow['parcel_number_to']),
                ],
            ];
            $rows[] = $row;
        }

        foreach ($rows as &$row) {
            foreach ($row as $key => &$cell) {
                switch ($key) {
                    case 'col1':
                        $cell['style'] = $style['destinatario']['style'];

                        break;
                    case 'col2':
                        $cell['style'] = $style['indirizzo']['style'];

                        break;
                    case 'col3':
                        $cell['style'] = $style['riferimento']['style'];

                        break;
                    case 'col4':
                        $cell['style'] = $style['bolla']['style'];

                        break;
                    case 'col5':
                        $cell['style'] = $style['cass']['style'];

                        break;
                    case 'col6':
                        $cell['style'] = $style['colli']['style'];

                        break;
                    case 'col7':
                        $cell['style'] = $style['peso']['style'];

                        break;
                    case 'col8':
                        $cell['style'] = $style['volume']['style'];

                        break;
                    case 'col9':
                        $cell['style'] = $style['segnacolli']['style'];

                        break;
                }
            }
        }

        return $rows;
    }

    private function getRiepilogo()
    {
        $db = \Db::getInstance();

        $subQuery = new \DbQuery();
        $subQuery->select(ModelBrtShipmentResponse::$definition['primary'])
            ->from(ModelBrtShipmentBordero::$definition['table'])
            ->where('bordero_status = 0')
            ->orderBy(ModelBrtShipmentBordero::$definition['primary']);
        $subQuery = $subQuery->build();

        $query = new \DbQuery();
        $query->select('*')
            ->from(ModelBrtShipmentResponse::$definition['table'])
            ->where('id_brt_shipment_response in ('.$subQuery.')')
            ->orderBy(ModelBrtShipmentResponse::$definition['primary']);

        $rows = $db->executeS($query);
        $riepilogo = [
            'spedizioni' => 0,
            'colli' => 0,
            'volume' => 0,
            'peso' => 0,
            'totcass' => 0,
            'numcass' => 0,
        ];

        if ($rows) {
            foreach ($rows as $row) {
                ++$riepilogo['spedizioni'];
                $riepilogo['colli'] += $row['number_of_parcels'];
                $riepilogo['volume'] += $row['volume_m3'];
                $riepilogo['peso'] += $row['weight_kg'];
                $riepilogo['totcass'] += $row['cash_on_delivery'];
                $riepilogo['numcass'] += $row['cash_on_delivery'] > 0 ? 1 : 0;
            }
        }

        return $riepilogo;
    }
}
