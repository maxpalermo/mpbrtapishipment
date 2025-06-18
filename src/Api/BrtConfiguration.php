<?php

namespace MpSoft\MpBrtApiShipment\Api;

final class BrtConfiguration
{
    public function get($key)
    {
        $key = $this->getConfigKey($key);
        $value = \Configuration::get($key);
        try {
            $jsonValue = json_decode($value, true, 512, JSON_THROW_ON_ERROR);

            return $jsonValue;
        } catch (\Throwable $th) {
            return $value;
        }
    }

    public function set($key, $preferences)
    {
        $key = $this->getConfigKey($key);
        $brtConfKey = preg_replace('/\[\]$/', '', $key);

        if (!$brtConfKey) {
            return false;
        }

        if (isset($preferences[$brtConfKey.'[]'])) {
            $value = $preferences[$brtConfKey.'[]'];
        } else {
            $value = $preferences[$brtConfKey] ?? '';
        }

        if (is_array($value)) {
            $value = json_encode($value);
        }

        return \Configuration::updateValue($brtConfKey, $value);
    }

    public function getConfigKey($key)
    {
        $brtConfKey = preg_replace('/\[\]$/', '', $key);

        if (preg_match('/^BRT_/', $brtConfKey)) {
            return strtoupper($brtConfKey);
        }

        return strtoupper("BRT_{$brtConfKey}");
    }

    public function getConfigurationKeys()
    {
        $configKeys = [
            'environment',
            'sandbox_user_id',
            'sandbox_password',
            'sandbox_departure_depot',
            'production_user_id',
            'production_password',
            'production_departure_depot',
            'service_type',
            'network',
            'delivery_freight_type_code',
            'cash_on_delivery_modules',
            'cod_payment_type',
            'is_alert_required',
            'is_label_required',
            'label_output_type',
            'label_offset_x',
            'label_offset_y',
            'label_is_border_required',
            'label_is_logo_required',
            'label_is_barcode_control_row_required',
            'label_format',
            'order_state_change',
            'order_state_show_button',
            'sender_customer_code',
            'pricing_condition_code',
            'sender_parcel_type',
        ];

        return $configKeys;
    }

    public function getSettings()
    {
        $keys = $this->getConfigurationKeys();
        $settings = [];
        foreach ($keys as $key) {
            $settings[$key] = $this->get($key);
            if (is_array($settings[$key])) {
                $settings[$key] = json_encode($settings[$key]);
            }
        }

        return $settings;
    }

    public function updateSettings($settings)
    {
        foreach ($settings as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function getLabelParameters()
    {
        $keys = [
            'label_output_type',
            'label_offset_x',
            'label_offset_y',
            'label_is_border_required',
            'label_is_logo_required',
            'label_is_barcode_control_row_required',
            'label_format',
        ];
        $settings = [];
        foreach ($keys as $key) {
            if (!in_array($key, ['label_format'])) {
                $requestKey = \Tools::toCamelCase(substr($key, 5));
            } else {
                $requestKey = \Tools::toCamelCase($key);
            }
            $settings[$requestKey] = $this->get($key);
        }

        return [
            'isLabelRequired' => 1,
            'labelParameters' => $settings,
        ];
    }

    public function getAccount()
    {
        $accountManager = new BrtAuthManager();
        $accountManager->getAccount();

        return [
            'account' => $accountManager->getAccount()->toArray(),
        ];
    }
}
