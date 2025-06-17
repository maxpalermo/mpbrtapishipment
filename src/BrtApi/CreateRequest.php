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

namespace MpSoft\MpBrtApiShipment\BrtApi;

class CreateRequest
{
    protected $account;
    protected $createData;
    protected $labelParameters;
    protected $isLabelRequired;

    public function __construct($params)
    {
        $this->account = $params['account'] ?? null;
        $this->createData = $params['createData'] ?? null;
        $this->labelParameters = $params['labelParameters'] ?? null;
        $this->isLabelRequired = $params['isLabelRequired'] ?? 0;

        if (!$this->account) {
            throw new \Exception('Account is required');
        }

        if (!$this->createData) {
            throw new \Exception('Create data is required');
        }

        if (!$this->labelParameters) {
            throw new \Exception('Label parameters is required');
        }
    }

    public function doRequest()
    {
        $url = 'https://api.brt.it/rest/v1/shipments/shipment';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'account' => $this->account,
            'createData' => $this->createData,
            'isLabelRequired' => $this->isLabelRequired,
            'labelParameters' => $this->labelParameters,
        ]));
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if (200 == $httpcode && $result) {
            $error = '';
            $executionMessage = [];
            $labels = [];
            $response = json_decode($result, true);
            if (isset($response['createResponse']['executionMessage'])) {
                $executionMessage = $response['createResponse']['executionMessage'];
            }
            if (isset($response['createResponse']['labels']['label'])) {
                $labels = $response['createResponse']['labels']['label'];
            }
            $response = $response['createResponse'];
            try {
                unset($response['executionMessage']);
                unset($response['labels']);
            } catch (\Throwable $th) {
                $error = $th->getMessage();
            }

            return [
                'success' => true,
                'response' => $response,
                'executionMessage' => $executionMessage,
                'labels' => $labels,
                'error' => $error,
            ];
        } else {
            if (is_array($result) || is_object($result) && empty($error)) {
                $error = json_encode($result);
            }
            if (empty($error) && is_string($result)) {
                $error = $result;
            }

            return [
                'success' => false,
                'error' => $error,
                'httpcode' => $httpcode,
                'response' => $result ? json_decode($result, true) : [],
            ];
        }
    }

    public function getCreateData()
    {
        return $this->createData;
    }

    public function getLabelParameters()
    {
        return $this->labelParameters;
    }

    public function isLabelRequired()
    {
        return $this->isLabelRequired;
    }

    public function getAccount()
    {
        return $this->account;
    }
}
