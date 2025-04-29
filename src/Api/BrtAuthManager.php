<?php

namespace MpSoft\MpBrtApiShipment\Api;

class BrtAuthManager
{
    const ENV_REAL = 'real';
    const ENV_SANDBOX = 'sandbox';

    protected $realUserID;
    protected $realPassword;
    protected $sandboxUserID;
    protected $sandboxPassword;

    public function __construct()
    {
        // Recupera i dati dalla configurazione PrestaShop
        $this->realUserID = \Configuration::get('BRT_REAL_USERID');
        $this->realPassword = \Configuration::get('BRT_REAL_PASSWORD');
        $this->sandboxUserID = \Configuration::get('BRT_SANDBOX_USERID');
        $this->sandboxPassword = \Configuration::get('BRT_SANDBOX_PASSWORD');
    }

    /**
     * Restituisce l'oggetto Account corretto in base all'ambiente scelto
     *
     * @return Account
     */
    public function getAccount()
    {
        $env = \Configuration::get('BRT_ENVIRONMENT') ?: self::ENV_SANDBOX;
        if ($env === self::ENV_SANDBOX) {
            return new Account($this->sandboxUserID, $this->sandboxPassword);
        }

        return new Account($this->realUserID, $this->realPassword);
    }
}
