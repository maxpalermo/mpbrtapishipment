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

namespace MpSoft\MpBrtApiShipment\Api;

class Delete
{
    private $endpoint = 'https://api.brt.it/rest/v1/shipments/delete';

    /**
     * Cancella una spedizione tramite API BRT.
     *
     * @param int        $numericSenderReference      Riferimento numerico mittente
     * @param string     $alphanumericSenderReference Riferimento alfanumerico mittente (case sensitive)
     * @param string|int $userID                      Codice utente per l'autenticazione
     * @param string     $password                    Password utente
     *
     * @return array Risposta decodificata dell'API
     */
    public function deleteShipment($numericSenderReference, $alphanumericSenderReference = '', $userID = null, $password = null)
    {
        if (!$userID || !$password) {
            $account = new BrtAuthManager();
            $account = $account->getAccount();
            $userID = $account->userID;
            $password = $account->password;
        }
        $body = [
            'account' => [
                'userID' => $userID,
                'password' => $password,
            ],
            'deleteData' => [
                'senderCustomerCode' => $userID,
                'numericSenderReference' => $numericSenderReference,
                'alphanumericSenderReference' => $alphanumericSenderReference,
            ],
        ];

        $ch = curl_init($this->endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return [
                'success' => false,
                'error' => $error,
                'httpCode' => $httpCode,
                'response' => $response,
            ];
        }

        $decoded = json_decode($response, true);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'httpCode' => $httpCode,
            'response' => $decoded,
        ];
    }
}
