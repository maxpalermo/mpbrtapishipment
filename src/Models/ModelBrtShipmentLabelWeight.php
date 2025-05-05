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

namespace MpSoft\MpBrtApiShipment\Models;

class ModelBrtShipmentLabelWeight extends \ObjectModel
{
    /** @var string */
    public $barcode; // PECOD
    /** @var float */
    public $weight; // PPESO in Kg
    /** @var float */
    public $volume; // PVOLU in M3
    /** @var float */
    public $x; // Larghezza in mm
    /** @var float */
    public $y; // Lunghezza in mm
    /** @var float */
    public $z; // Altezza in mm
    /** @var int */
    public $id_read; // ID_FISCALE
    /** @var bool */
    public $is_read; // PFLAG
    /** @var bool */
    public $is_envelope; // PFLAG
    /** @var string */
    public $date_add; // PTIMP
    /** @var string */
    public $date_upd;

    /************************
     * !Protected variables
     ************************/
    /** @var int */
    protected $id_lang;
    /** @var \Context */
    protected $context;
    /** @var \ModuleAdminController */
    protected $controller;
    /** @var \Module */
    protected $module;
    /** @var string */
    public $name;

    public static $definition = [
        'table' => 'brt_shipment_label_weight',
        'primary' => 'id_weight',
        'multilang' => false,
        'multishop' => true,
        'fields' => [
            'barcode' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => true,
            ],
            'weight' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFLoat',
                'required' => true,
            ],
            'volume' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default' => '1',
            ],
            'x' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default' => '1',
            ],
            'y' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default' => '1',
            ],
            'z' => [
                'type' => self::TYPE_FLOAT,
                'validate' => 'isFloat',
                'required' => true,
                'default' => '1',
            ],
            'id_read' => [
                'type' => self::TYPE_INT,
                'validate' => 'isInt',
                'required' => false,
            ],
            'is_read' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'is_envelope' => [
                'type' => self::TYPE_BOOL,
                'validate' => 'isBool',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null)
    {
        $this->name = 'ModelMpBrtLabelWeight';
        $this->context = \Context::getContext();
        $this->controller = $this->context->controller;
        $this->module = $this->controller->module;

        parent::__construct($id, $id_lang, $id_shop);
    }

    public static function getByBarcode($barcode): ModelBrtShipmentLabelWeight
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where('barcode = \''.pSQL($barcode).'\'');
        $id = (int) $db->getValue($sql);

        return new self($id);
    }

    public function add($auto_date = true, $null_values = false)
    {
        // Il volume è calcolato in M3 mentre le misure sono in mm
        $this->volume = ($this->x * $this->y * $this->z) / 1000000000;

        return parent::add($auto_date, $null_values);
    }

    public function update($null_values = false)
    {
        $this->volume = ($this->x * $this->y * $this->z) / 1000000000;

        return parent::update($null_values);
    }

    public static function getPackages($numeriSenderReference)
    {
        $pfx = _DB_PREFIX_;
        $table = self::$definition['table'];
        $sql = "SELECT * FROM {$pfx}{$table}".
            " WHERE `barcode` like '{$numeriSenderReference}-%'".
            ' ORDER BY date_add ASC';
        $db = \Db::getInstance();
        $res = $db->executeS($sql);

        return $res ?: [];
    }

    public static function calculate($packages)
    {
        $weight = 0;
        $volume = 0;

        foreach ($packages as $package) {
            $x = $package['x'];
            $y = $package['y'];
            $z = $package['z'];
            $weight += $package['weight'];
            $volume += number_format(($x * $y * $z) / 1000000000, 3);
        }

        return [
            'packages' => count($packages),
            'weight' => $weight,
            'volume' => $volume,
        ];
    }
}
