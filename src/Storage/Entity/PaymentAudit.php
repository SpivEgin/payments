<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Payment auditing entry entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentAudit extends Entity
{
    /** @var \DateTime */
    protected $date;
    /** @var string */
    protected $customer_id;
    /** @var string */
    protected $transaction_id;
    /** @var string */
    protected $transaction_reference;
    /** @var string */
    protected $description;
    /** @var array */
    protected $data;

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
    public function setDate($date)
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

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }
}
