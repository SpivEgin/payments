<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Payment entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Payment extends Entity
{
    /** @var \DateTime */
    protected $date;
    /** @var string */
    protected $customer_id;
    /** @var string */
    protected $gateway;
    /** @var string */
    protected $transaction_id;
    /** @var string */
    protected $transaction_reference;
    /** @var float */
    protected $amount;
    /** @var string */
    protected $currency;
    /** @var string */
    protected $status;
    /** @var string */
    protected $description;

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * @param string $customerId
     */
    public function setCustomerId($customerId)
    {
        $this->customer_id = $customerId;
    }

    /**
     * @return string
     */
    public function getGateway()
    {
        return $this->gateway;
    }

    /**
     * @param string $gateway
     */
    public function setGateway($gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transaction_id;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transaction_id = $transactionId;
    }

    /**
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->transaction_reference;
    }

    /**
     * @param string $transactionReference
     */
    public function setTransactionReference($transactionReference)
    {
        $this->transaction_reference = $transactionReference;
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
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
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
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
