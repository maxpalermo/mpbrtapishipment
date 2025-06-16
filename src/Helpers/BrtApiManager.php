<?php

namespace MpSoft\MpBrtApiShipment\Helpers;

use MpSoft\MpBrtApiShipment\Api\BrtConfiguration;
use MpSoft\MpBrtApiShipment\BrtApi\CreateRequest;
use MpSoft\MpBrtApiShipment\Entity\BrtShipmentRequest;
use MpSoft\MpBrtApiShipment\Repository\Doctrine\BrtShipmentRequestRepository;
use MpSoft\MpBrtApiShipment\Repository\Doctrine\BrtShipmentResponseLabelRepository;
use MpSoft\MpBrtApiShipment\Repository\Doctrine\BrtShipmentResponseRepository;

class BrtApiManager
{
    private BrtShipmentRequestRepository $brtShipmentRequestRepository;
    private BrtShipmentResponseRepository $brtShipmentResponseRepository;
    private BrtShipmentResponseLabelRepository $brtShipmentResponseLabelRepository;

    public function __construct(
        BrtShipmentRequestRepository $brtShipmentRequestRepository,
        BrtShipmentResponseRepository $brtShipmentResponseRepository,
        BrtShipmentResponseLabelRepository $brtShipmentResponseLabelRepository,
    ) {
        $this->brtShipmentRequestRepository = $brtShipmentRequestRepository;
        $this->brtShipmentResponseRepository = $brtShipmentResponseRepository;
        $this->brtShipmentResponseLabelRepository = $brtShipmentResponseLabelRepository;
    }

    public function createBrtRequest($numericSenderReference)
    {
        /** @var BrtShipmentRequest $entity */
        $entity = $this->brtShipmentRequestRepository->findOneBy(['numericSenderReference' => $numericSenderReference]);
        if (!$entity) {
            return [
                'success' => false,
                'message' => 'Label non valida',
            ];
        }

        $accountData = $entity->getAccountJson();
        $departureDepot = $accountData['departureDepot'] ?? '';
        unset($accountData['departureDepot']);
        $isLabelRequired = $entity->isLabelRequired();
        $labelParameters = $entity->getLabelParametersJson();
        $createData = $entity->getCreateDataJson();
        $orderStateId = $createData['change-order-state'] ?? 0;

        unset($createData['change-order-state']);

        $createData['senderCustomerCode'] = (new BrtConfiguration())->get('sender_customer_code');
        $createData['departureDepot'] = $departureDepot;

        if ('IT' == $createData['consigneeCountryAbbreviationISOAlpha2']) {
            $createData['consigneeCountryAbbreviationISOAlpha2'] = 'IT';
        }

        if (!($createData['consigneeContactName'] ?? false)) {
            unset($createData['consigneeContactName']);
        }

        if (!($createData['consigneeTelephone'] ?? false)) {
            unset($createData['consigneeTelephone']);
        }

        if (!($createData['consigneeMobilePhoneNumber'] ?? false)) {
            unset($createData['consigneeMobilePhoneNumber']);
        }

        if (!($createData['consigneeEMail'] ?? false)) {
            unset($createData['consigneeEMail']);
        }

        if (isset($createData['consigneeTelephone']) || isset($createData['consigneeMobilePhoneNumber']) || isset($createData['consigneeEMail'])) {
            $createData['isAlertRequired'] = 1;
        } else {
            $createData['isAlertRequired'] = 0;
        }

        $cashOnDelivery = $createData['cashOnDelivery'] ?? 0;
        if (0 == $cashOnDelivery) {
            unset($createData['cashOnDelivery']);
            unset($createData['codCurrency']);
            unset($createData['codPaymentType']);
            $createData['isCODMandatory'] = 0;
        }

        if (!($createData['notes'] ?? false)) {
            unset($createData['notes']);
        }

        $pricingConditionCode = null;
        if ('D' == $createData['network']) {
            if (1 == $createData['numberOfParcels']) {
                $pricingConditionCode = '390';
            } elseif ($createData['numberOfParcels'] > 1 && $createData['numberOfParcels'] < 6) {
                $pricingConditionCode = '395';
            }
        } else {
            $pricingConditionCode = '020'; // 010
        }

        if ($pricingConditionCode) {
            $createData['pricingConditionCode'] = '';
        }

        $environment = (new BrtConfiguration())->get('environment');
        if ('SANDBOX' == $environment && isset($createData['pricingConditionCode'])) {
            $createData['pricingConditionCode'] = '';
        }

        if (!($createData['pudoId'] ?? false)) {
            unset($createData['pudoId']);
        }

        // Elimino tutti i valori = DEF e li imposto a una stringa vuota
        foreach ($createData as $key => $value) {
            if ('DEF' == $value) {
                $createData[$key] = '';
            }
            if (is_bool($value)) {
                $createData[$key] = (int) $value;
            }
        }

        $request = [
            'account' => $accountData,
            'createData' => $createData,
        ];

        if ($isLabelRequired) {
            foreach ($labelParameters as $key => $value) {
                if ('DEF' == $value) {
                    $labelParameters[$key] = '';
                }
                if (is_bool($value)) {
                    $labelParameters[$key] = (int) $value;
                }
            }
            $request['isLabelRequired'] = (int) $isLabelRequired;
            $request['labelParameters'] = $labelParameters;
        }

        // $request['account'] = $this->toCamelCaseArray($request['account']);
        // $request['createData'] = $this->toCamelCaseArray($request['createData']);
        // $request['labelParameters'] = $this->toCamelCaseArray($request['labelParameters']);

        // Invio la richiesta a Bartolini
        $request = new CreateRequest($request);
        $result = $request->doRequest();

        if (!$result['success']) {
            return [
                'success' => false,
                'message' => $result['error'],
            ];
        }

        // Se tutto è andato bene, controllo se devo cambiare lo stato d'ordine
        if ($orderStateId) {
            $order = new \Order((int) $createData['numericSenderReference']);
            if (\Validate::isLoadedObject($order)) {
                if ($order->current_state != $orderStateId) {
                    $order->setCurrentState($orderStateId);
                }
            }
        }

        return [
            'success' => true,
            'message' => 'Etichetta creata con successo',
            'response' => $result['response'],
            'executionMessage' => $result['executionMessage'],
            'labels' => $result['labels'],
            'error' => $result['error'],
        ];
    }

    public function saveBrtRequest($numericSenderReference, $details, $packages)
    {
        $conf = new BrtConfiguration();
        if ($numericSenderReference && $details && $packages) {
            foreach ($packages as $key => $package) {
                $this->savePackage($numericSenderReference, $package, $key);
            }
        }

        $account = $conf->getAccount();
        $labelParameters = $conf->getLabelParameters();
        $orderId = null;
        if (isset($details['order-id'])) {
            $orderId = $details['order-id'];
            unset($details['order-id']);
        }
        if (isset($details['sender_parcel_type']) && 'FALSE' == $details['sender_parcel_type']) {
            unset($details['sender_parcel_type']);
        }

        try {
            // Doctrine: cerca se esiste già una richiesta con lo stesso numericSenderReference
            $criteria = ['numericSenderReference' => $numericSenderReference];
            $existing = $this->brtShipmentRequestRepository->findOneBy($criteria);
            if ($existing) {
                $entity = $existing;
            } else {
                $entity = new BrtShipmentRequest();
                $entity->setDateAdd(new \DateTime());
            }
            $entity->setOrderId((int) $orderId);
            $entity->setNumericSenderReference($numericSenderReference);
            $entity->setAlphanumericSenderReference($details['alphanumeric_sender_reference']);
            $entity->setAccountJson($account['account']);
            $entity->setCreateDataJson($this->toCamelCaseArray($details));
            $entity->setIsLabelRequired($labelParameters['isLabelRequired']);
            $entity->setLabelParametersJson($labelParameters['labelParameters']);
            $entity->setDateUpd(new \DateTime());
            $this->brtShipmentRequestRepository->save($entity);
            $result = true;
            $message = 'Etichetta salvata con successo';
        } catch (\Throwable $th) {
            $result = false;
            $message = $th->getMessage();
        }

        return [
            'success' => $result,
            'message' => $message,
            'id' => $numericSenderReference,
            'details' => $details,
            'packages' => $packages,
            'label' => $labelParameters,
            'account' => $account,
            'numericSenderReference' => $numericSenderReference,
        ];
    }

    public function saveBrtResponse($executionMessage, $response, $labels)
    {
        $numericSenderReference = $response['numericSenderReference'] ?? null;

        if (!$numericSenderReference) {
            return [
                'success' => false,
                'message' => 'Nessun segnacollo selezionato',
            ];
        }

        foreach ($labels as &$label) {
            $label['numericSenderReference'] = $numericSenderReference;
            $label['alphanumericSenderReference'] = $response['alphanumericSenderReference'];
        }

        $result = $this->brtShipmentResponseRepository->execute($numericSenderReference, $executionMessage, $response);
        $result = $this->brtShipmentResponseLabelRepository->save($numericSenderReference, $labels);

        return $result;
    }

    public function savePackage($numericSenderReference, $package, $number)
    {
        // Prepara array dati secondo la struttura attesa dal service Doctrine
        $labelData = [
            'numeric_sender_reference' => $numericSenderReference,
            'number' => $number,
            'weight' => isset($package['package-weight']) ? (float) $package['package-weight'] : null,
            'volume' => isset($package['package-volume']) ? (float) $package['package-volume'] : null,
            'x' => isset($package['package-x']) ? (int) $package['package-x'] : null,
            'y' => isset($package['package-y']) ? (int) $package['package-y'] : null,
            'z' => isset($package['package-z']) ? (int) $package['package-z'] : null,
            // altri campi opzionali se disponibili
        ];
        // Salva o aggiorna la label tramite il service Doctrine
        $this->brtShipmentResponseLabelRepository->saveMeasures($labelData);
    }

    public function toCamelCaseArray($array)
    {
        $newArray = [];
        foreach ($array as $key => $value) {
            if ('consignee_zip_code' == $key) {
                $newArray['consigneeZIPCode'] = $value;
            } elseif ('weight_kg' == $key) {
                $newArray['weightKG'] = $value;
            } elseif ('consignee_email' == $key) {
                $newArray['consigneeEMail'] = $value;
            } elseif ('is_cod_mandatory' == $key) {
                $newArray['isCODMandatory'] = $value;
            } elseif ('consignee_country_abbreviation_iso_alpha_2' == $key) {
                $newArray['consigneeCountryAbbreviationISOAlpha2'] = $value;
            } elseif (preg_match('/_/', $key)) {
                $newArray[\Tools::toCamelCase($key)] = $value;
            } else {
                $newArray[$key] = $value;
            }
        }

        return $newArray;
    }
}
