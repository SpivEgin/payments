<?php

namespace Bolt\Extension\Bolt\Payments;

/**
 * Helper class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Helper
{
    /**
     * Singleton Constructor.
     */
    private function __construct()
    {
    }

    /**
     * Resolve a provider string to the case sensitive version.
     *
     * @param $provider
     *
     * @return string
     */
    public static function resolveGateway($provider)
    {
        $provider = strtolower($provider);
        $map = [
            'authorizenet_aim'         => 'AuthorizeNet_AIM',
            'authorizenet_dpm'         => 'AuthorizeNet_DPM',
            'authorizenet_sim'         => 'AuthorizeNet_SIM',
            'buckaroo_creditcard'      => 'Buckaroo_CreditCard',
            'buckaroo_ideal'           => 'Buckaroo_Ideal',
            'buckaroo_paypal'          => 'Buckaroo_PayPal',
            'cardsave'                 => 'CardSave',
            'coinbase'                 => 'Coinbase',
            'common_'                  => 'Common',
            'dummy'                    => 'Dummy',
            'eway_direct'              => 'Eway_Direct',
            'eway_rapiddirect'         => 'Eway_RapidDirect',
            'eway_rapid'               => 'Eway_Rapid',
            'eway_rapidshared'         => 'Eway_RapidShared',
            'firstdata_connect'        => 'FirstData_Connect',
            'firstdata_global'         => 'FirstData_Global',
            'firstdata_payeezy'        => 'FirstData_Payeezy',
            'firstdata_webservice'     => 'FirstData_Webservice',
            'gocardless'               => 'GoCardless',
            'migs_twoparty'            => 'Migs_TwoParty',
            'migs_threeparty'          => 'Migs_ThreeParty',
            'mollie'                   => 'Mollie',
            'multisafepay'             => 'MultiSafepay',
            'multisafepay_rest'        => 'MultiSafepay_Rest',
            'multisafepay_xml'         => 'MultiSafepay_Xml',
            'netaxept'                 => 'Netaxept',
            'netbanx'                  => 'NetBanx',
            'payfast'                  => 'PayFast',
            'payflow_pro'              => 'Payflow_Pro',
            'paymentexpress_pxpay'     => 'PaymentExpress_PxPay',
            'paymentexpress_pxpost'    => 'PaymentExpress_PxPost',
            'paypal_express'           => 'PayPal_Express',
            'paypal_pro'               => 'PayPal_Pro',
            'paypal_rest'              => 'PayPal_Rest',
            'pin'                      => 'Pin',
            'sagepay_direct'           => 'SagePay_Direct',
            'sagepay_server'           => 'SagePay_Server',
            'securepay_directpost'     => 'SecurePay_DirectPost',
            'stripe'                   => 'Stripe',
            'targetpay_directebanking' => 'TargetPay_Directebanking',
            'targetpay_ideal'          => 'TargetPay_Ideal',
            'targetpay_mrcash'         => 'TargetPay_Mrcash',
            'twocheckout'              => 'TwoCheckout',
            'worldpay'                 => 'WorldPay',
        ];

        if (!isset($map[$provider])) {
            throw new \RuntimeException(sprintf('Invalid provider: %s', $provider));
        }

        return $map[$provider];
    }
}
