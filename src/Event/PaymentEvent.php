<?php

namespace Bolt\Extension\Bolt\Payments\Event;

use Bolt\Extension\Bolt\Payments\Transaction\Transaction;
use Symfony\Component\EventDispatcher\Event;

/**
 * Payment event class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentEvent extends Event
{
    /** @var Transaction */
    protected $transaction;

    /**
     * Constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @return Transaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
