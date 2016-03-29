<?php

namespace Bolt\Extension\Bolt\Payments;

use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\AbstractRequest;

/**
 * Combined Omnipay gateway interface.
 */
interface CombinedGatewayInterface extends GatewayInterface
{
    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function authorize(array $parameters);

    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function completeAuthorize(array $parameters);

    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function capture(array $parameters);

    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function purchase(array $parameters);

    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function completePurchase(array $parameters);

    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function createCard(array $parameters);

    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function updateCard(array $parameters);

    /**
     * @param array $parameters
     *
     * @return AbstractRequest
     */
    public function deleteCard(array $parameters);

    /**
     * @return string
     */
    public function getTestMode();

    /**
     * @param string $value
     */
    public function setTestMode($value);

    /**
     * @return string
     */
    public function getCurrency();

    /**
     * @param string $value
     */
    public function setCurrency($value);

    /**
     * @return boolean
     */
    public function supportsAuthorize();

    /**
     * @return boolean
     */
    public function supportsCompleteAuthorize();

    /**
     * @return boolean
     */
    public function supportsCapture();

    /**
     * @return boolean
     */
    public function supportsPurchase();

    /**
     * @return boolean
     */
    public function supportsCompletePurchase();

    /**
     * @return boolean
     */
    public function supportsRefund();

    /**
     * @return boolean
     */
    public function supportsVoid();

    /**
     * @return boolean
     */
    public function supportsCreateCard();

    /**
     * @return boolean
     */
    public function supportsDeleteCard();

    /**
     * @return boolean
     */
    public function supportsUpdateCard();
}
