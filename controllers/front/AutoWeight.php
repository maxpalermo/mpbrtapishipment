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

use MpSoft\MpBrtApiShipment\Models\ModelBrtShipmentLabelWeight;

class MpBrtApiShipmentAutoWeightModuleFrontController extends ModuleFrontController
{
    protected $name;

    public function __construct()
    {
        $this->auth = false;
        $this->guestAllowed = false;
        $this->maintenance = false;
        $this->ssl = (int) Configuration::get('PS_SSL_ENABLED');
        $this->ajax = Tools::getValue('ajax', 0);

        parent::__construct();

        $this->name = 'AutoWeight';
    }

    protected function response($value)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($value));
    }

    protected function getPostRequest()
    {
        return [
            'barcode' => Tools::getValue('PECOD'),
            'weight' => (float) Tools::getValue('PPESO'),
            'volume' => (float) Tools::getValue('PVOLU'),
            'x' => (float) Tools::getValue('X'),
            'y' => (float) Tools::getValue('Y'),
            'z' => (float) Tools::getValue('Z'),
            'id_read' => Tools::getValue('ID_FISCALE'),
            'is_read' => (bool) Tools::getValue('PFLAG'),
            'is_envelope' => (bool) Tools::getValue('ENVELOPE', false),
            'date_add' => Tools::getValue('PTIMP', date('Y-m-d H:i:s')),
            'date_upd' => date('Y-m-d H:i:s'),
        ];
    }

    protected function responseError($error)
    {
        $request = $this->getPostRequest();

        $this->response([
            'result' => false,
            'error' => $error,
            'request' => $request,
        ]);
    }

    protected function responseOk()
    {
        $request = $this->getPostRequest();

        $this->response([
            'result' => true,
            'error' => '',
            'request' => $request,
        ]);
    }

    public function display()
    {
        if (Tools::isSubmit('action') && !Tools::isSubmit('ajax')) {
            $action = 'display'.Tools::ucfirst(Tools::getValue('action'));
            if (method_exists($this, $action)) {
                return $this->$action();
            }
        }

        exit('NOT ALLOWED');
    }

    /**
     * Display result of getting tracking: used with cron.
     *
     * @return void
     */
    public function displayAjaxInsert()
    {
        $barcode = Tools::getValue('PECOD', '');
        $weight = (float) Tools::getValue('PPESO', 1);
        $volume = (float) Tools::getValue('PVOLU', 1);
        $x = (float) Tools::getValue('X', 1);
        $y = (float) Tools::getValue('Y', 1);
        $z = (float) Tools::getValue('Z', 1);
        $id_read = Tools::getValue('ID_FISCALE', '');
        $is_read = (bool) Tools::getValue('PFLAG', false);
        $is_envelope = (bool) Tools::getValue('ENVELOPE', false);
        $date_add = Tools::getValue('PTIMP', date('Y-m-d H:i:s'));
        $date_upd = date('Y-m-d H:i:s');

        if (!$barcode) {
            $this->responseError($this->module->l('BARCODE non valido', $this->name));
        }

        if (!$weight) {
            $this->responseError($this->module->l('PESO non valido', $this->name));
        }

        if (!$x) {
            $this->responseError($this->module->l('X non valido', $this->name));
        }

        if (!$y) {
            $this->responseError($this->module->l('Y non valido', $this->name));
        }

        if (!$z) {
            $this->responseError($this->module->l('Z non valido', $this->name));
        }

        if (!$volume) {
            $volume = ($x * $y * $z) / 1000000000;
        }

        if (!$id_read) {
            $this->responseError($this->module->l('ID FISCALE non valido', $this->name));
        }

        $model = ModelBrtShipmentLabelWeight::getByBarcode($barcode);

        $model->barcode = $barcode;
        $model->weight = number_format($weight, 3);
        $model->volume = number_format($volume, 3);
        $model->x = $x;
        $model->y = $y;
        $model->z = $z;
        $model->is_envelope = $is_envelope;
        $model->id_read = $id_read;
        $model->is_read = $is_read;
        $model->date_add = $date_add;
        $model->date_upd = $date_upd;

        try {
            if ($model->id) {
                $res = $model->update();
            } else {
                $res = $model->add();
            }
        } catch (Throwable $th) {
            $this->responseError($th->getMessage());
        }

        if ($res) {
            $this->responseOk();
        } else {
            $this->responseError(Db::getInstance()->getMsgError());
        }
    }

    public function displayGetMeasures()
    {
        $id_order = Tools::getValue('id_order', '');
        $pfx = _DB_PREFIX_;
        $table = ModelBrtShipmentLabelWeight::$definition['table'];
        $sql = "SELECT barcode,weight,volume,is_envelope FROM {$pfx}{$table} ".
            "WHERE `barcode` like '{$id_order}%' ".
            'ORDER BY date_add ASC';
        $db = Db::getInstance();
        $res = $db->executeS($sql);
        $out = [
            'peso' => 0,
            'volume' => 0,
            'colli' => 0,
        ];
        $rows = [];
        if ($res) {
            foreach ($res as $row) {
                $id = $row['barcode'];
                $rows[$id] = $row;
            }
        }
        if ($rows) {
            foreach ($rows as $row) {
                ++$out['colli'];
                $out['peso'] += $row['weight'];
                if (!$row['is_envelope']) {
                    $out['volume'] += $row['volume'];
                }
            }
        } else {
            return [
                'colli' => 0,
                'peso' => 0,
                'volume' => 0,
            ];
        }

        return $out;
    }

    /**
     * Crea dei pesi casuali per gli ultimi 300 ordini .
     */
    public function displayAjaxRandomGenerateMeasures()
    {
        $pfx = _DB_PREFIX_;
        $table = ModelBrtShipmentLabelWeight::$definition['table'];
        // Svuoto la tabella
        $sql = "TRUNCATE TABLE {$pfx}{$table}";
        $db = Db::getInstance();
        $db->execute($sql);

        // Prelevo gli id degli ultimi 300 ordini
        $sql = "SELECT id_order FROM {$pfx}orders ORDER BY id_order DESC LIMIT 300";
        $res = $db->executeS($sql);
        if ($res) {
            $ids = array_column($res, 'id_order');
        }

        // Creo 300 pesi casuali
        $sql = "INSERT INTO {$pfx}{$table} (barcode,weight,volume,x,y,z,id_read,is_read,is_envelope,date_add,date_upd) VALUES ";
        foreach ($ids as $id_order) {
            // creo da 1 a 5 etichette per ogni ordine
            $labels = rand(1, 5);
            $step = 0;
            do {
                ++$step;
                $PECOD = "{$id_order}-{$step}";
                $PPESO = number_format(rand(1000, 10000) / 1000, 3);
                $X = rand(300, 800);
                $Y = rand(300, 800);
                $Z = rand(300, 800);
                $PVOLU = number_format(($X * $Y * $Z) / 1000000000, 3);
                // Genera un ID fiscale di 16 cifre usando i caratteri della stringa
                $ID_FISCALE = '0123456789';
                $id_length = 8;
                $id_read = '';
                $is_read = rand(0, 1);
                $is_envelope = rand(0, 1);
                $date_add = date('Y-m-d H:i:s');
                $date_upd = $date_add;
                for ($j = 0; $j < $id_length; ++$j) {
                    $id_read .= $ID_FISCALE[rand(0, strlen($ID_FISCALE) - 1)];
                }
                $sql .= "\n('{$PECOD}',{$PPESO},{$PVOLU},{$X},{$Y},{$Z},{$id_read},{$is_read},{$is_envelope},'{$date_add}','{$date_upd}'),";
            } while ($labels--);
        }
        $sql = rtrim($sql, ',').';';
        $this->ajaxRender("<pre>{$sql}</pre>");
        $db->execute($sql);
    }

    public function displayAjaxTestCron()
    {
        $link = $this->context->link->getModuleLink(
            'mpbrtapishipment',
            'AutoWeight',
            [
                'ajax' => 1,
                'action' => 'insert',
                'PECOD' => ':PECOD',
                'PPESO' => ':PPESO',
                'PVOLU' => ':PVOLU',
                'X' => ':X',
                'Y' => ':Y',
                'Z' => ':Z',
                'ID_FISCALE' => ':ID_FISCALE',
                'PFLAG' => ':PFLAG',
                'ENVELOPE' => ':ENVELOPE',
                'PTIMP' => ':PTIMP',
            ]
        );

        $times = rand(10, 100);
        $labels = rand(1, 5);
        do {
            for ($i = 1; $i < $labels; ++$i) {
                $labelCode = rand(130000, 140000);
                $PECOD = "{$labelCode}-{$i}";
                $PPESO = number_format(rand(1000, 10000) / 1000, 3);
                $X = rand(300, 800);
                $Y = rand(300, 800);
                $Z = rand(300, 800);
                $PVOLU = number_format(($X * $Y * $Z) / 1000000000, 3);
                $ID_FISCALE = rand(123456, 999999);
                $PFLAG = rand(0, 1);
                $ENVELOPE = rand(0, 1);
                $PTIMP = date('Y-m-d+H:i:s');
                $linkUrl = str_replace(
                    [
                        '%3APECOD',
                        '%3APPESO',
                        '%3APVOLU',
                        '%3AX',
                        '%3AY',
                        '%3AZ',
                        '%3AID_FISCALE',
                        '%3APFLAG',
                        '%3AENVELOPE',
                        '%3APTIMP',
                    ],
                    [
                        $PECOD,
                        $PPESO,
                        $PVOLU,
                        $X,
                        $Y,
                        $Z,
                        $ID_FISCALE,
                        $PFLAG,
                        $ENVELOPE,
                        $PTIMP,
                    ],
                    $link
                );
                echo "<pre>{$linkUrl}</pre>";
            }
        } while ($times--);
    }
}
