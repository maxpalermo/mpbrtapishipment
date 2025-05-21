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

    public function __construct($rows, $force = null)
    {
        $this->rows = $rows;
        $this->force = $force;
        parent::__construct(self::PDF_ORIENTATION_LANDSCAPE, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
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

        $cellStyles = $this->setCellStyle();

        $module = \Module::getInstanceByName('mpbrtapishipment');
        $template = $module->getLocalPath().'views/templates/bordero/page.tpl';
        $smarty = \Context::getContext()->smarty->createTemplate($template);
        $smarty->assign(
            [
                'cellstyle' => $cellStyles,
                'rows' => $this->rows,
            ]
        );

        $html = $smarty->fetch();
        $this->writeHTML($html);

        // LAST PAGE
        $this->AddPage();
        $this->SetFont('helvetica', '', 12);
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
        $file_attachment['content'] = $this->Output($filename, 'S');
        $file_attachment['name'] = $filename;
        $file_attachment['mime'] = 'application/pdf';
        // Close and output PDF document
        if ($this->setPrinted()) {
            $this->Output($filename, 'I');
        } else {
            \Tools::dieObject(
                [
                    'ERRORE' => 'NON È POSSIBILE STAMPARE IL BORDERO',
                    'MESSAGGIO' => 'ERRORE DURANTE IL SALVATAGGIO DEL BORDERO',
                ]
            );
        }
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
            [
                'label1' => 'Destinatario',
                'label2' => '',
                'width' => '6cm',
                'text-align' => 'left',
            ],
            [
                'label1' => 'Indirizzo',
                'label2' => 'Cap Città Prov',
                'width' => '6cm',
                'text-align' => 'left',
            ],
            [
                'label1' => 'Rif. numerico',
                'label2' => 'Riferimento',
                'width' => '3cm',
                'text-align' => 'center',
            ],
            [
                'label1' => 'Cod',
                'label2' => 'Bolla',
                'width' => '1cm',
                'text-align' => 'right',
            ],
            [
                'label1' => 'Importo',
                'label2' => 'C/ass',
                'width' => '2cm',
                'text-align' => 'right',
            ],
            [
                'label1' => 'Colli',
                'label2' => '',
                'width' => '1cm',
                'text-align' => 'right',
            ],
            [
                'label1' => 'Peso',
                'label2' => '',
                'width' => '2cm',
                'text-align' => 'right',
            ],
            [
                'label1' => 'Volume',
                'label2' => '',
                'width' => '3cm',
                'text-align' => 'right',
            ],
            [
                'label1' => 'Segnacolli',
                'label2' => 'Dal - Al',
                'width' => '3cm',
                'text-align' => 'center',
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

            foreach ($row as $cell) {
                $data = $cell['data'];

                if (isset($data['numberOfParcels'])) {
                    $result['colli'] += $data['numberOfParcels'];
                }
                if (isset($data['cashOnDelivery']) && $data['cashOnDelivery'] > 0) {
                    ++$result['numcass'];
                    $result['cashOnDelivery'] += $data['cashOnDelivery'];
                }
                if (isset($data['weightKg'])) {
                    $result['weightKg'] += $data['weightKg'];
                }
                if (isset($data['volumeM3'])) {
                    $result['volumeM3'] += $data['volumeM3'];
                }
            }
        }

        return $result;
    }
}
