<?php

namespace SmartySoft\AdjusterForge\Http\Controller;

defined('ABSPATH') || exit; // Prevent direct access

class SettingsController extends BaseController
{
    public function get_membership_data()
    {
        if ( is_user_logged_in() ) {
            // Get the user ID
            $user_id = get_current_user_id();

            $af_general_settings = self::getOption('adjuster_forge_general_settings', []);
            $currency           = isset($af_general_settings['selected_currency']) ? $af_general_settings['selected_currency'] : 'GBP';
            $currency_symbol    = self::getAllowedCurrencySymble();
            $selected_symbol    = isset($currency_symbol[$currency]) ? $currency_symbol[$currency] : '£';

            $data = [
                'currency' => $currency,
                'currency_symbol' => $selected_symbol,
            ];

            return $this->response([
                'data' => $data,
                'status' => 'success',
            ], 200);
        } else {
            return $this->response('You must need to logged in', 400);
        }
    }
}
