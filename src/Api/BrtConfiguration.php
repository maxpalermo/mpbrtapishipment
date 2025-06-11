<?php

namespace MpSoft\MpBrtApiShipment\Api;

final class BrtConfiguration
{
    public function get($key)
    {
        $key = $this->getConfigKey($key);

        return \Configuration::get($key);
    }

    public function set($key, $value)
    {
        $key = $this->getConfigKey($key);

        if (!$key) {
            return null;
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        return \Configuration::updateValue($key, $value);
    }

    protected function getConfigKey($key)
    {
        switch ($key) {
            case 'environment':
                $key = 'BRT_ENVIRONMENT';
                break;
            case 'sandbox_user_id':
                $key = 'BRT_SANDBOX_USER_ID';
                break;
            case 'sandbox_password':
                $key = 'BRT_SANDBOX_PASSWORD';
                break;
            case 'sandbox_departure_depot':
                $key = 'BRT_SANDBOX_DEPARTURE_DEPOT';
                break;
            case 'production_user_id':
                $key = 'BRT_PRODUCTION_USER_ID';
                break;
            case 'production_password':
                $key = 'BRT_PRODUCTION_PASSWORD';
                break;
            case 'production_departure_depot':
                $key = 'BRT_PRODUCTION_DEPARTURE_DEPOT';
                break;
            case 'service_type':
                $key = 'BRT_SERVICE_TYPE';
                break;
            case 'network':
                $key = 'BRT_NETWORK';
                break;
            case 'delivery_freight_type_code':
                $key = 'BRT_DELIVERY_FREIGHT_TYPE_CODE';
                break;
            case 'cod_payment_type':
                $key = 'BRT_COD_PAYMENT_TYPE';
                break;
        }

        return $key;
    }
}
