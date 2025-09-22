<?php

namespace SmartySoft\AdjusterForge\Http\Request;

defined('ABSPATH') || exit; // Prevent direct access

/**
 * Class ValidatorHandler
 *
 * This class extends the Validator class and is responsible for handling
 * validation logic for HTTP requests within the application.
 *
 * @package SmartySoft\AdjusterForge\Http\Request
 */
class ValidatorHandler extends Validator
{
    /**
     * Validate the change password request.
     *
     * @param Request $request
     * @return array
     */
    public static function validateChangePasswordRequest ( Request $request) 
    {
        return $request->validate(
            $request->only(['current_password', 'new_password', 'confirm_new_password']),
            [
                'current_password' => 'required',
                'new_password'     => 'required',
                'confirm_new_password' => 'required|same:new_password',
            ],
            [
                'current_password.required' => __( 'Current password is required.', 'driver-forge' ),
                'new_password.required'     => __( 'New password is required.', 'driver-forge' ),
                'confirm_new_password.required' => __( 'Confirm password is required.', 'driver-forge' ),
                'confirm_new_password.same'     => __( 'New password and confirm password must be same.', 'driver-forge' ),
            ]
        );
    }

    public static function validateGeneralSettings( Request $request)
    {
        return $request->validate(
            $request->only([
                'auth_page_id', 
                'profile_page_id', 
                'driver_subscription_amount', 
                'driver_subscription_id', 
                'driver_yearly_subscription_amount',
                'driver_yearly_subscription_id',
                'standard_membership_amount', 
                'standard_membership_id',
                'standard_yearly_membership_amount',
                'standard_yearly_membership_id',
                'premium_membership_amount',
                'premium_membership_id',
                'premium_yearly_membership_amount',
                'premium_yearly_membership_id',
                'allow_free_verification',
                'selected_currency',]),
            [
                'auth_page_id'                  => 'required',
                'profile_page_id'               => 'required',
                'driver_subscription_amount'    => 'required|numeric',
                'driver_subscription_id'        => 'required',
                'driver_yearly_subscription_amount' => 'required|numeric',
                'driver_yearly_subscription_id' => 'required',
                'standard_membership_amount'    => 'required|numeric',
                'standard_membership_id'        => 'required',
                'standard_yearly_membership_amount' => 'required|numeric',
                'standard_yearly_membership_id' => 'required',
                'premium_membership_amount'     => 'required|numeric',
                'premium_membership_id'         => 'required',
                'premium_yearly_membership_amount' => 'required|numeric',
                'premium_yearly_membership_id'  => 'required',
                'allow_free_verification'       => 'boolean',
                'selected_currency'             => 'required',
            ],
            [
                'auth_page_id.required'               => __( 'Auth page ID is required.', 'driver-forge' ),
                'profile_page_id.required'            => __( 'Profile page ID is required.', 'driver-forge' ),
                'driver_subscription_amount.required' => __( 'Driver Subscription amount is required.', 'driver-forge' ),
                'driver_subscription_amount.numeric'  => __( 'Driver Subscription amount must be a number.', 'driver-forge' ),
                'driver_yearly_subscription_id.required'     => __( 'Driver Yearly Subscription ID is required.', 'driver-forge' ),
                'driver_yearly_subscription_amount.required' => __( 'Driver Yearly Subscription amount is required.', 'driver-forge' ),
                'driver_yearly_subscription_amount.numeric'  => __( 'Driver Yearly Subscription amount must be a number.', 'driver-forge' ),
                'driver_subscription_id.required'    => __( 'Driver Subscription ID is required.', 'driver-forge' ),
                'standard_membership_amount.required' => __( 'Standard Membership amount is required.', 'driver-forge' ),
                'standard_membership_amount.numeric'  => __( 'Standard Membership amount must be a number.', 'driver-forge' ),
                'standard_membership_id.required'     => __( 'Standard Membership ID is required.', 'driver-forge' ),
                'standard_yearly_membership_amount.required' => __( 'Standard Yearly Membership amount is required.', 'driver-forge' ),
                'standard_yearly_membership_amount.numeric'  => __( 'Standard Yearly Membership amount must be a number.', 'driver-forge' ),
                'standard_yearly_membership_id.required'     => __( 'Standard Yearly Membership ID is required.', 'driver-forge' ),
                'premium_membership_amount.required'  => __( 'Premium Membership amount is required.', 'driver-forge' ),
                'premium_membership_amount.numeric'   => __( 'Premium Membership amount must be a number.', 'driver-forge' ),
                'premium_membership_id.required'      => __( 'Premium Membership ID is required.', 'driver-forge' ),
                'premium_yearly_membership_amount.required' => __( 'Premium Yearly Membership amount is required.', 'driver-forge' ),
                'premium_yearly_membership_amount.numeric'  => __( 'Premium Yearly Membership amount must be a number.', 'driver-forge' ),
                'premium_yearly_membership_id.required'     => __( 'Premium Yearly Membership ID is required.', 'driver-forge' ),
                'allow_free_verification.boolean'     => __( 'Allow free verification must be a boolean value.', 'driver-forge' ),
                'selected_currency.required'          => __( 'Currency is required.', 'driver-forge' ),
            ]
        );
    }
}