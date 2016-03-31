<?php

namespace Bolt\Extension\Bolt\Payments;

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
        return $this->$offset;
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
     * Create a new instance.
     *
     * @param array $params
     *
     * @return Transaction
     */
    public static function create(array $params)
    {
        $class = new self();
        $class->setAmount($params['amount']);
        $class->setCurrency($params['currency']);
        $class->setDescription($params['description']);
        $class->setTransactionId($params['transactionId']);
        $class->setTransactionReference($params['transactionReference']);
        $class->setCardReference($params['cardReference']);
        $class->setReturnUrl($params['returnUrl']);
        $class->setCancelUrl($params['cancelUrl']);
        $class->setNotifyUrl($params['notifyUrl']);
        $class->setIssuer($params['issuer']);

        return $class;
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
