<?php

namespace Bolt\Extension\Bolt\Payments;

use Bolt\Extension\Bolt\Payments\Config\Config;
use Omnipay\Common\GatewayFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Gateway manager.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GatewayManager
{
    /** @var Config */
    protected $config;
    /** @var SessionInterface */
    protected $session;
    /** @var  CombinedGatewayInterface[] */
    protected $gateways;

    public function __construct(Config $config, SessionInterface $session)
    {
        $this->config = $config;
        $this->session = $session;
    }

    /**
     * Return an initialised gateway.
     *
     * @param string $name
     *
     * @return CombinedGatewayInterface[]
     */
    public function getGateway($name)
    {
        return $this->gateways[$name];
    }

    /**
     * Return a session value to a gateway.
     *
     * @param string $name
     * @param string $type
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSessionValue($name, $type, $default = null)
    {
        $sessionName = self::getSessionName(strtolower($name), $type);

        return $this->session->get($sessionName, $default);
    }

    /**
     * Set a session value to a gateway.
     *
     * @param string $name
     * @param string $type
     * @param string $value
     *
     * @return GatewayManager
     */
    public function setSessionValue($name, $type, $value)
    {
        $sessionName = self::getSessionName(strtolower($name), $type);
        $this->session->set($sessionName, $value);

        return $this;
    }

    /**
     * Initialise a gateway from session data.
     *
     * @param string $name
     *
     * @return CombinedGatewayInterface
     */
    public function initializeSessionGateway($name)
    {
        $gateway = $this->getGatewayInterface($name);

        // Get data
        $shortName = $gateway->getShortName();
        $sessionName = self::getSessionPrefix($shortName);
        $data = (array) $this->session->get($sessionName);

        // Initialize gateway with parameters
        $gateway->initialize($data);

        return $this->gateways[$name] = $gateway;
    }

    /**
     * Initialise a gateway from request data.
     *
     * @param string  $name
     * @param Request $request
     *
     * @return CombinedGatewayInterface
     */
    public function initializeRequestGateway($name, Request $request)
    {
        $gateway = $this->getGatewayInterface($name);

        // Get data
        $data = (array) $request->request->get('gateway');

        // Initialize gateway with parameters
        $gateway->initialize($data);

        return $this->gateways[$name] = $gateway;
    }

    /**
     * Get a configured gateway object.
     *
     * @param string $name
     *
     * @return CombinedGatewayInterface
     */
    private function getGatewayInterface($name)
    {
        $providerConfig = $this->config->getProviders()->get($name);
        $name = Helper::resolveGateway($name);
        $gateway = (new GatewayFactory())
            ->create($name)
            ->initialize($providerConfig)
        ;

        return $gateway;
    }

    /**
     * Return the valid name for session keys.
     *
     * @param string $gatewayName
     * @param string $type
     *
     * @return string
     */
    public static function getSessionName($gatewayName, $type)
    {
        return static::getSessionPrefix($gatewayName) . '.' . $type;
    }


    /**
     * Return the valid prefix for session keys.
     *
     * @param string $gatewayName
     *
     * @return string
     */
    public static function getSessionPrefix($gatewayName)
    {
        return 'payments.' . $gatewayName;
    }
}
