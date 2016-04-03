<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Entity;

use Bolt\Storage\Entity\Entity;

/**
 * Payment auditing entry entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentAuditEntry extends Entity
{
    /** @var \DateTime */
    protected $date;
    /** @var string */
    protected $transactionId;
    /** @var string */
    protected $transactionReference;
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
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @param string $transactionId
     */
    public function setTransactionId($transactionId)
    {
        $this->transactionId = $transactionId;
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
     */
    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;
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
