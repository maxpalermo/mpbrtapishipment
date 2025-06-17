<?php

/*
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

class MpBrtApiShipmentAutoWeightModuleFrontController extends ModuleFrontController
{
    protected $name;

    public function __construct()
    {
        $this->auth = false;
        $this->guestAllowed = true;
        $this->maintenance = true;
        $this->ssl = (int) Configuration::get('PS_SSL_ENABLED');
        $this->ajax = Tools::getValue('ajax', 0);

        parent::__construct();

        $this->name = 'AutoWeight';

        $action = Tools::getValue('action');
        if ($action && 'insert' == $action) {
            exit($this->insertMeasure($this->getMeasure()));
        }
    }

    protected function getMeasure()
    {
        $id = Tools::getValue('PECOD');
        $parts = explode('-', $id);
        $numericSenderReference = (int) $parts[0];
        $number = (int) $parts[1];
        $params = [
            'numeric_sender_reference' => $numericSenderReference,
            'number' => $number,
            'weight' => (float) Tools::getValue('PPESO'),
            'volume' => (float) Tools::getValue('PVOLU'),
            'x' => (int) Tools::getValue('X'),
            'y' => (int) Tools::getValue('Y'),
            'z' => (int) Tools::getValue('Z'),
            'fiscal_id' => Tools::getValue('ID_FISCALE'),
            // 'is_read' => Tools::getValue('PFLAG'),
            // 'is_envelope' => Tools::getValue('ENVELOPE'),
            'measure_date' => Tools::getValue('PTIMP', date('Y-m-d H:i:s')),
        ];

        return $params;
    }

    protected function insertMeasure($measure)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_brt_shipment_response_label')
            ->from('brt_shipment_response_label')
            ->where('numeric_sender_reference = '.$measure['numeric_sender_reference'])
            ->where('number = '.$measure['number']);
        $id = $db->getValue($sql);
        if ($id) {
            $result = $db->update('brt_shipment_response_label', $measure, 'id_brt_shipment_response_label = '.$id);
        } else {
            $result = $db->insert('brt_shipment_response_label', $measure, true, false);
        }

        $message = $result ? 'Dati salvati con successo' : 'Errore durante il salvataggio';

        return $this->ajaxRender(json_encode([
            'success' => $result,
            'message' => $message,
            'params' => $measure,
        ]));
    }
}
