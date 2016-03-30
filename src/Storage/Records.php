<?php

namespace Bolt\Extension\Bolt\Payments\Storage;

/**
 * Records management class.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Records
{
    /** @var Repository\Payment */
    protected $payment;

    /**
     * Constructor.
     *
     * @param Repository\Payment $payment
     */
    public function __construct(Repository\Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get all payment entities.
     *
     * @param string $customer
     *
     * @return Entity\Payment
     */
    public function getPayments($customer)
    {
        return $this->payment->getCustomerPayments($customer);
    }

    /**
     * Save an payment entity.
     *
     * @param Entity\Payment $payment
     *
     * @return bool
     */
    public function savePayment(Entity\Payment $payment)
    {
        return $this->payment->save($payment);
    }

    /**
     * Delete an payment entity.
     *
     * @param Entity\Payment $payment
     *
     * @return bool
     */
    public function deletePayment(Entity\Payment $payment)
    {
        return $this->payment->delete($payment);
    }
}
