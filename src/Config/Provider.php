<?php

namespace Bolt\Extension\Bolt\Payments\Config;

use Bolt\Helpers\Arr;

/**
 * Provider configuration class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Provider
{
    /** @var array */
    protected $authorizenet;
    /** @var array */
    protected $buckaroo;
    /** @var array */
    protected $cardsave;
    /** @var array */
    protected $coinbase;
    /** @var array */
    protected $eway;
    /** @var array */
    protected $firstdata;
    /** @var array */
    protected $gocardless;
    /** @var array */
    protected $migs;
    /** @var array */
    protected $mollie;
    /** @var array */
    protected $multisafepay;
    /** @var array */
    protected $netaxept;
    /** @var array */
    protected $netbanx;
    /** @var array */
    protected $payfast;
    /** @var array */
    protected $payflow;
    /** @var array */
    protected $paymentexpress;
    /** @var array */
    protected $paypal;
    /** @var array */
    protected $pin;
    /** @var array */
    protected $sagepay;
    /** @var array */
    protected $securepay;
    /** @var array */
    protected $stripe;
    /** @var array */
    protected $targetpay;
    /** @var array */
    protected $twocheckout;
    /** @var array */
    protected $worldpay;

    /**
     * Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $config = Arr::mergeRecursiveDistinct($config, $this->getDefaults());

        $this->authorizenet = $config['providers']['authorizenet'];
        $this->buckaroo = $config['providers']['buckaroo'];
        $this->cardsave = $config['providers']['cardsave'];
        $this->coinbase = $config['providers']['coinbase'];
        $this->eway = $config['providers']['eway'];
        $this->firstdata = $config['providers']['firstdata'];
        $this->gocardless = $config['providers']['gocardless'];
        $this->migs = $config['providers']['migs'];
        $this->mollie = $config['providers']['mollie'];
        $this->multisafepay = $config['providers']['multisafepay'];
        $this->netaxept = $config['providers']['netaxept'];
        $this->netbanx = $config['providers']['netbanx'];
        $this->payfast = $config['providers']['payfast'];
        $this->payflow = $config['providers']['payflow'];
        $this->paymentexpress = $config['providers']['paymentexpress'];
        $this->paypal = $config['providers']['paypal'];
        $this->pin = $config['providers']['pin'];
        $this->sagepay = $config['providers']['sagepay'];
        $this->securepay = $config['providers']['securepay'];
        $this->stripe = $config['providers']['stripe'];
        $this->targetpay = $config['providers']['targetpay'];
        $this->twocheckout = $config['providers']['twocheckout'];
        $this->worldpay = $config['providers']['worldpay'];
    }

    /**
     * Generic getter for providers.
     *
     * @param string $provider
     *
     * @throws \RuntimeException
     *
     * @return array
     */
    public function get($provider)
    {
        $provider = strtolower($provider);

        if (property_exists($this, $provider)) {
            return $this->{$provider}['default'];
        }

        if (strpos($provider, '_') === false) {
            throw new \RuntimeException(sprintf('Invalid provider: %s', $provider));
        }

        $parts = explode('_', $provider);
        $getter = 'get' . $parts[0];
        $type = $parts[1];
        if (method_exists($this, $getter)) {
            return $this->{$getter}($type);
        }

        throw new \RuntimeException(sprintf('Invalid provider: %s', $provider));
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getAuthorizenet($type = null)
    {
        $provider = $this->authorizenet['default'];
        if ($type === 'sim') {
            $provider += $this->authorizenet['sim'];
        } elseif ($type === 'dpm') {
            $provider += $this->authorizenet['dpm'];
        }

        return $provider;
    }

    /**
     * @return array
     */
    public function getBuckaroo()
    {
        return $this->buckaroo['default'];
    }

    /**
     * @return array
     */
    public function getCardsave()
    {
        return $this->cardsave['default'];
    }

    /**
     * @return array
     */
    public function getCoinbase()
    {
        return $this->coinbase['default'];
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getEway($type = null)
    {
        $provider = $this->eway['default'];
        if ($type === 'direct') {
            $provider += $this->eway['direct'];
        }

        return $provider;
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getFirstdata($type = null)
    {
        $provider = $this->firstdata['default'];
        if ($type === 'connect') {
            $provider += $this->firstdata['connect'];
        } elseif ($type === 'global') {
            $provider += $this->firstdata['global'];
        } elseif ($type === 'payeezy') {
            $provider += $this->firstdata['payeezy'];
        } elseif ($type === 'webservice') {
            $provider += $this->firstdata['webservice'];
        }

        return $provider;
    }

    /**
     * @return array
     */
    public function getGocardless()
    {
        return $this->gocardless['default'];
    }

    /**
     * @return array
     */
    public function getMigs()
    {
        return $this->migs['default'];
    }

    /**
     * @return array
     */
    public function getMollie()
    {
        return $this->mollie['default'];
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getMultisafepay($type = null)
    {
        $provider = $this->multisafepay['default'];
        if ($type === 'rest') {
            $provider += $this->multisafepay['rest'];
        } elseif ($type === 'xml') {
            $provider += $this->multisafepay['xml'];
        }

        return $provider;
    }

    /**
     * @return array
     */
    public function getNetaxept()
    {
        return $this->netaxept['default'];
    }

    /**
     * @return array
     */
    public function getNetbanx()
    {
        return $this->netbanx['default'];
    }

    /**
     * @return array
     */
    public function getPayfast()
    {
        return $this->payfast['default'];
    }

    /**
     * @return array
     */
    public function getPayflow()
    {
        return $this->payflow['default'];
    }

    /**
     * @return array
     */
    public function getPaymentexpress()
    {
        return $this->paymentexpress['default'];
    }

    /**
     * @param string $type
     *
     * @return array
     */
    public function getPaypal($type = null)
    {
        $provider = $this->paypal['default'];
        if ($type === 'express') {
            $provider += $this->paypal['express'];
        } elseif ($type === 'pro') {
            $provider += $this->paypal['pro'];
        } elseif ($type === 'rest') {
            $provider += $this->paypal['rest'];
        }

        return $provider;
    }

    /**
     * @return array
     */
    public function getPin()
    {
        return $this->pin['default'];
    }

    /**
     * @return array
     */
    public function getSagepay()
    {
        return $this->sagepay['default'];
    }

    /**
     * @return array
     */
    public function getSecurepay()
    {
        return $this->securepay['default'];
    }

    /**
     * @return array
     */
    public function getStripe()
    {
        return $this->stripe['default'];
    }

    /**
     * @return array
     */
    public function getTargetpay()
    {
        return $this->targetpay['default'];
    }

    /**
     * @return array
     */
    public function getTwocheckout()
    {
        return $this->twocheckout['default'];
    }

    /**
     * @return array
     */
    public function getWorldpay()
    {
        return $this->worldpay['default'];
    }

    /**
     * Return default provider data.
     *
     * @return array
     */
    protected function getDefaults()
    {
        return [
            'providers' => [
                'authorizenet' => [
                    'default' => [
                        'apiLoginId'        => null,
                        'transactionKey'    => null,
                        'liveEndpoint'      => 'https://secure.authorize.net/gateway/transact.dll',
                        'developerEndpoint' => 'https://test.authorize.net/gateway/transact.dll',
                        'developerMode'     => true,
                        'testMode'          => true,
                    ],
                    'sim' => [
                        'hashSecret' => null,
                    ],
                    'dpm' => [
                        'hashSecret' => null,
                    ],
                ],
                'buckaroo' => [
                    'default' => [
                        'websiteKey' => null,
                        'secretKey'  => null,
                        'testMode'   => true,
                    ],
                ],
                'cardsave' => [
                    'default' => [
                        'merchantId' => null,
                        'password'   => null,
                    ],
                ],
                'coinbase' => [
                    'default' => [
                        'apiKey'    => null,
                        'secret'    => null,
                        'accountId' => null,
                    ],
                ],
                'eway' => [
                    'default' => [
                        'apiKey'   => null,
                        'password' => null,
                        'testMode' => true,
                    ],
                    'DirectGateway' => [
                        'customerId' => null,
                    ],
                ],
                'firstdata' => [
                    'default' => [
                        'testMode' => true,
                    ],
                    'connect' => [
                        'storeId'      => null,
                        'sharedSecret' => null,
                    ],
                    'global' => [
                        'gatewayid' => null,
                        'password'  => null,
                    ],
                    'payeezy' => [
                        'gatewayid' => null,
                        'password'  => null,
                    ],
                    'webservice' => [
                        'sslCertificate' => null,
                        'sslKey'         => null,
                        'sslKeyPassword' => null,
                        'userName'       => null,
                        'password'       => null,
                    ],
                ],
                'gocardless' => [
                    'default' => [
                        'appId'       => null,
                        'appSecret'   => null,
                        'merchantId'  => null,
                        'accessToken' => null,
                        'testMode'    => true,
                    ],
                ],
                'migs' => [
                    'default' => [
                        'merchantId'         => null,
                        'merchantAccessCode' => null,
                        'secureHash'         => null,
                    ],
                ],
                'mollie' => [
                    'default' => [
                        'apiKey' => null,
                    ],
                ],
                'multisafepay' => [
                    'default' => [
                        'testMode' => true,
                    ],
                    'rest' => [
                        'apiKey' => null,
                        'locale' => 'en',
                    ],
                    'xml' => [
                        'accountId' => null,
                        'siteId'    => null,
                        'siteCode'  => null,
                    ],
                ],
                'netaxept' => [
                    'default' => [
                        'merchantId' => null,
                        'password'   => null,
                        'testMode'   => true,
                    ],
                ],
                'netbanx' => [
                    'default' => [
                        'accountNumber' => null,
                        'storeId'       => null,
                        'storePassword' => null,
                        'testMode'      => true,
                    ],
                ],
                'payfast' => [
                    'default' => [
                        'merchantId'  => null,
                        'merchantKey' => null,
                        'pdtKey'      => null,
                        'testMode'    => true,
                    ],
                ],
                'payflow' => [
                    'default' => [
                        'username' => null,
                        'password' => null,
                        'vendor'   => null,
                        'partner'  => null,
                        'testMode' => true,
                    ],
                ],
                'paymentexpress' => [
                    'default' => [
                        'username' => null,
                        'password' => null,
                    ],
                ],
                'paypal' => [
                    'default' => [
                        'testMode' => true,
                    ],
                    'express' => [
                        'username'       => null,
                        'password'       => null,
                        'signature'      => null,
                        'solutionType'   => ['Sole', 'Mark'],
                        'landingPage'    => ['Billing', 'Login'],
                        'brandName'      => null,
                        'headerImageUrl' => null,
                        'logoImageUrl'   => null,
                        'borderColor'    => null,
                    ],
                    'pro' => [
                        'username'  => null,
                        'password'  => null,
                        'signature' => null,
                    ],
                    'rest' => [
                        'clientId' => null,
                        'secret'   => null,
                        'token'    => null,
                    ],
                ],
                'pin' => [
                    'default' => [
                        'secretKey' => null,
                        'testMode'  => true,
                    ],
                ],
                'sagepay' => [
                    'default' => [
                        'vendor'     => null,
                        'referrerId' => null,
                        'testMode'   => true,
                    ],
                ],
                'securepay' => [
                    'default' => [
                        'merchantId'          => null,
                        'transactionPassword' => null,
                        'testMode'            => true,
                    ],
                ],
                'stripe' => [
                    'default' => [
                        'apiKey' => null,
                    ],
                ],
                'targetpay' => [
                    'default' => [
                        'subAccountId' => null,
                    ],
                ],
                'twocheckout' => [
                    'default' => [
                        'accountNumber' => null,
                        'secretWord'    => null,
                        'testMode'      => true,
                    ],
                ],
                'worldpay' => [
                    'default' => [
                        'installationId'   => null,
                        'accountId'        => null,
                        'secretWord'       => null,
                        'callbackPassword' => null,
                        'testMode'         => true,
                    ],
                ],
            ],
        ];
    }
}
