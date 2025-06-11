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

use MpSoft\MpBrtApiShipment\Helpers\DeleteByNumericReference;
use MpSoft\MpBrtApiShipment\Helpers\GetByNumericReference;
use setasign\Fpdi\Fpdi;

class ModelBrtShipmentResponseLabel extends \ObjectModel
{
    public $id_brt_shipment_response;
    public $number;
    public $numeric_sender_reference;
    public $alphanumeric_sender_reference;
    public $data_length;
    public $parcel_id;
    public $stream;
    public $stream_digital_label;
    public $parcel_number_geo_post;
    public $tracking_by_parcel_id;
    public $format;

    public static $definition = [
        'table' => 'brt_shipment_response_label',
        'primary' => 'id_brt_shipment_response_label',
        'fields' => [
            'id_brt_shipment_response' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'numeric_sender_reference' => ['type' => self::TYPE_STRING, 'size' => 15, 'validate' => 'isUnsignedInt', 'required' => true],
            'alphanumeric_sender_reference' => ['type' => self::TYPE_STRING, 'size' => 15, 'validate' => 'isAnything', 'required' => true, 'size' => 64],
            'number' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'data_length' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'parcel_id' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => true, 'size' => 64],
            'stream' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => false, 'size' => 999999999],
            'stream_digital_label' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => false, 'size' => 999999999],
            'parcel_number_geo_post' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => false, 'size' => 64],
            'tracking_by_parcel_id' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => false, 'size' => 64],
            'format' => ['type' => self::TYPE_STRING, 'validate' => 'isAnything', 'required' => false, 'size' => 16],
        ],
    ];

    public static function decodeBase64($stream)
    {
        return base64_decode($stream);
    }

    public function decodeStream()
    {
        return base64_decode($this->stream);
    }

    public function decodeStreamDigitalLabel()
    {
        return base64_decode($this->stream_digital_label);
    }

    public static function getByTrackingParcelId($trackingParcelId)
    {
        $db = \Db::getInstance();
        $query = new \DbQuery();
        $query->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where('tracking_by_parcel_id = '.(int) $trackingParcelId);

        $result = $db->executeS($query);
        $labels = [];
        if ($result) {
            foreach ($result as $r) {
                $labels[] = new self($r);
            }

            return $labels;
        }

        return $labels;
    }

    public static function getByNumericSenderReference($numericSenderReference): array
    {
        $result = (new GetByNumericReference($numericSenderReference, self::$definition['table'], self::$definition['primary']))->run(self::class);

        return $result;
    }

    public static function deleteByNumericSenderReference($numericSenderReference): bool
    {
        return (new DeleteByNumericReference($numericSenderReference, self::$definition['table']))->run();
    }

    public static function createLabelPdf($idBrtShipmentResponse)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('stream')
            ->from(self::$definition['table'])
            ->where('id_brt_shipment_response = '.(int) $idBrtShipmentResponse);

        $result = $db->executeS($sql);
        $streams = [];
        if ($result) {
            foreach ($result as $r) {
                $streams[] = base64_decode($r['stream']);
            }
        }

        return self::printMergedPDF($streams);
    }

    public static function printMergedPDF($streams)
    {
        $streamPDF = self::mergePdfStreams($streams);

        return $streamPDF;
    }

    public static function mergePdfStreams(array $streams)
    {
        // Specifica unità 'mm' direttamente
        $pdf = new Fpdi('P', 'mm');
        $brtLabel = [
            'width' => 100,
            'height' => 70,
        ];
        foreach ($streams as $stream) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'pdf');
            file_put_contents($tmpFile, $stream);

            $pageCount = $pdf->setSourceFile($tmpFile);
            for ($pageNo = 1; $pageNo <= $pageCount; ++$pageNo) {
                $tplIdx = $pdf->importPage($pageNo);
                $pdf->AddPage('L', [$brtLabel['width'], $brtLabel['height']]);
                $pdf->useTemplate($tplIdx);
            }

            unlink($tmpFile);
        }

        return $pdf->Output('S');
    }
}
