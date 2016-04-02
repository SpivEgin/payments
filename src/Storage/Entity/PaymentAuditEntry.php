<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Entity;

/**
 * Payment auditing entry entity class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentAuditEntry
{
    /** @var \DateTime */
    protected $date;
    /** @var string */
    protected $transactionId;
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
