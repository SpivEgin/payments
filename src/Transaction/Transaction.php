<?php

namespace Bolt\Extension\Bolt\Payments\Transaction;

use Omnipay\Common\CreditCard;

/**
 * Payment transaction object class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Transaction implements \ArrayAccess
{
    /** @var float */
    protected $amount;
    /** @var string */
    protected $currency;
    /** @var string */
    protected $description;
    /** @var string */
    protected $transactionId;
    /** @var string */
    protected $transactionReference;
    /** @var string */
    protected $cardReference;
    /** @var string */
    protected $returnUrl;
    /** @var string */
    protected $cancelUrl;
    /** @var string */
    protected $notifyUrl;
    /** @var string */
    protected $finalUrl;
    /** @var CreditCard */
    protected $card;
    /** @var string */
    protected $issuer;
    /** @var string */
    protected $clientIp;

    /**
     * Constructor.
     *
     * @param array $params
     */
    public function __construct(array $params = [])
    {
        foreach ($params as $name => $value) {
            if (property_exists($this, $name)) {
                $this->{$name} = $value;
            }
        }
    }

    /**
     * @return string[]
     */
    public function getProperties()
    {
        return get_class_vars(self::class);
    }

    /**
     * @inheritDoc
     */
    public function offsetExists($offset)
    {
        return property_exists($this, $offset);
    }

    /**
     * @inheritDoc
     */
    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    /**
     * @inheritDoc
     */
    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    /**
     * @inheritDoc
     */
    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }

    /**
     * @return CreditCard
     */
    public function getCard()
    {
        return $this->card;
    }

    /**
     * @param CreditCard $card
     *
     * @return Transaction
     */
    public function setCard(CreditCard $card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * @return string
     */
    public function getIssuer()
    {
        return $this->issuer;
    }

    /**
     * @param string $issuer
     *
     * @return Transaction
     */
    public function setIssuer($issuer)
    {
        $this->issuer = $issuer;

        return $this;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     *
     * @return Transaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     *
     * @return Transaction
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return Transaction
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     *
     * @return Transaction
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;

        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    /**
     * @param string $transactionReference
     *
     * @return Transaction
     */
    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;

        return $this;
    }

    /**
     * @return string
     */
    public function getCardReference()
    {
        return $this->cardReference;
    }

    /**
     * @param string $cardReference
     *
     * @return Transaction
     */
    public function setCardReference($cardReference)
    {
        $this->cardReference = $cardReference;

        return $this;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     *
     * @return Transaction
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     *
     * @return Transaction
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getNotifyUrl()
    {
        return $this->notifyUrl;
    }

    /**
     * @param string $notifyUrl
     *
     * @return Transaction
     */
    public function setNotifyUrl($notifyUrl)
    {
        $this->notifyUrl = $notifyUrl;

        return $this;
    }

    /**
     * @return string
     */
    public function getFinalUrl()
    {
        return $this->finalUrl;
    }

    /**
     * @param string $finalUrl
     *
     * @return Transaction
     */
    public function setFinalUrl($finalUrl)
    {
        $this->finalUrl = $finalUrl;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientIp()
    {
        return $this->clientIp;
    }

    /**
     * @param mixed $clientIp
     *
     * @return Transaction
     */
    public function setClientIp($clientIp)
    {
        $this->clientIp = $clientIp;

        return $this;
    }
}
