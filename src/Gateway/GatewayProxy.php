<?php

namespace Bolt\Extension\Bolt\Payments\Gateway;

use Omnipay\Common\GatewayInterface;

/**
 * Omnipay gateway proxy.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class GatewayProxy implements CombinedGatewayInterface
{
    /** @var CombinedGatewayInterface */
    protected $gateway;

    /**
     * Constructor.
     *
     * @param GatewayInterface $gateway
     */
    public function __construct(GatewayInterface $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->gateway, $name], $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function __get($name)
    {
        return $this->gateway->{$name};
    }

    /**
     * {@inheritdoc}
     */
    public function __set($name, $value)
    {
        $this->gateway->{$name} = $value;
    }

    public function authorize(array $parameters)
    {
        return $this->gateway->authorize($parameters);
    }

    public function completeAuthorize(array $parameters)
    {
        return $this->gateway->completeAuthorize($parameters);
    }

    public function capture(array $parameters)
    {
        return $this->gateway->capture($parameters);
    }

    public function purchase(array $parameters)
    {
        return $this->gateway->purchase($parameters);
    }

    public function completePurchase(array $parameters)
    {
        return $this->gateway->completePurchase($parameters);
    }

    public function createCard(array $parameters)
    {
        return $this->gateway->createCard($parameters);
    }

    public function updateCard(array $parameters)
    {
        return $this->gateway->updateCard($parameters);
    }

    public function deleteCard(array $parameters)
    {
        return $this->gateway->deleteCard($parameters);
    }

    public function getName()
    {
        return $this->gateway->getName();
    }

    public function getShortName()
    {
        return $this->gateway->getShortName();
    }

    public function getDefaultParameters()
    {
        return $this->gateway->getDefaultParameters();
    }

    public function getParameters()
    {
        return $this->gateway->getParameters();
    }

    public function getTestMode()
    {
        return $this->gateway->getTestMode();
    }

    public function initialize(array $parameters = [])
    {
        return $this->gateway->initialize($parameters);
    }

    public function setTestMode($value)
    {
        return $this->gateway->setTestMode($value);
    }

    public function getCurrency()
    {
        return $this->gateway->getCurrency();
    }

    public function setCurrency($value)
    {
        return $this->gateway->setCurrency($value);
    }

    public function supportsAuthorize()
    {
        return $this->gateway->supportsAuthorize();
    }

    public function supportsCompleteAuthorize()
    {
        return $this->gateway->supportsCompleteAuthorize();
    }

    public function supportsCapture()
    {
        return $this->gateway->supportsCapture();
    }

    public function supportsPurchase()
    {
        return $this->gateway->supportsPurchase();
    }

    public function supportsCompletePurchase()
    {
        return $this->gateway->supportsCompletePurchase();
    }

    public function supportsRefund()
    {
        return $this->gateway->supportsRefund();
    }

    public function supportsVoid()
    {
        return $this->gateway->supportsVoid();
    }

    public function supportsCreateCard()
    {
        return $this->gateway->supportsCreateCard();
    }

    public function supportsDeleteCard()
    {
        return $this->gateway->supportsDeleteCard();
    }

    public function supportsUpdateCard()
    {
        return $this->gateway->supportsUpdateCard();
    }
}
