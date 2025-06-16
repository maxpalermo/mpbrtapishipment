<?php
// modules/mpbrtapishipment/controllers/front/ReceiveWeightController.php

class MpbrtapishipmentReceiveWeightModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        // Recupera parametri dalla query string
        $pecod = Tools::getValue('PECOD');
        $ppeso = Tools::getValue('PPESO');
        $pvolu = Tools::getValue('PVOLU');
        $x = Tools::getValue('X');
        $y = Tools::getValue('Y');
        $z = Tools::getValue('Z');
        $id_fiscale = Tools::getValue('ID_FISCALE');
        $pflag = Tools::getValue('PFLAG');
        $ptimp = Tools::getValue('PTIMP');

        // TODO: Validazione e logica personalizzata qui
        // Ad esempio, salva i dati in tabella, aggiorna ordine, ecc.

        // Risposta semplice
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Dati ricevuti',
            'data' => [
                'PECOD' => $pecod,
                'PPESO' => $ppeso,
                'PVOLU' => $pvolu,
                'X' => $x,
                'Y' => $y,
                'Z' => $z,
                'ID_FISCALE' => $id_fiscale,
                'PFLAG' => $pflag,
                'PTIMP' => $ptimp,
            ]
        ]);
        exit;
    }
}
