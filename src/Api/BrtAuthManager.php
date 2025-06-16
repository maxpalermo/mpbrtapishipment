<?php

namespace MpSoft\MpBrtApiShipment\Api;

class BrtAuthManager
{
    protected $conf;
    protected $productionUserId;
    protected $productionPassword;
    protected $productionDepartureDepot;
    protected $sandboxUserId;
    protected $sandboxPassword;
    protected $sandboxDepartureDepot;

    public function __construct()
    {
        // INIZIALIZZA LA CLASSE DI CONFIGURAZIONE
        $this->conf = new BrtConfiguration();

        // Recupera i dati dalla configurazione PrestaShop
        $this->productionUserId = $this->conf->get('production_user_id');
        $this->productionPassword = $this->conf->get('production_password');
        $this->productionDepartureDepot = $this->conf->get('production_departure_depot');
        $this->sandboxUserId = $this->conf->get('sandbox_user_id');
        $this->sandboxPassword = $this->conf->get('sandbox_password');
        $this->sandboxDepartureDepot = $this->conf->get('sandbox_departure_depot');
    }

    /**
     * Restituisce l'oggetto Account corretto in base all'ambiente scelto.
     *
     * @return Account
     */
    public function getAccount()
    {
        $env = 'SANDBOX' == $this->conf->get('environment') ? 'SANDBOX' : 'PRODUCTION';
        if ('SANDBOX' === $env) {
            return new Account($this->sandboxUserId, $this->sandboxPassword, $this->sandboxDepartureDepot);
        }

        return new Account($this->productionUserId, $this->productionPassword, $this->productionDepartureDepot);
    }
}
