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
    /** @var Repository\PaymentAuditEntry */
    protected $paymentAuditEntry;

    /**
     * Constructor.
     *
     * @param Repository\Payment           $payment
     * @param Repository\PaymentAuditEntry $paymentAuditEntry
     */
    public function __construct(Repository\Payment $payment, Repository\PaymentAuditEntry $paymentAuditEntry)
    {
        $this->payment = $payment;
        $this->paymentAuditEntry = $paymentAuditEntry;
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

    /**
     * Save an payment audit entity.
     *
     * @param Entity\PaymentAuditEntry $paymentAuditEntry
     *
     * @return bool
     */
    public function savePaymentAuditEntry(Entity\PaymentAuditEntry $paymentAuditEntry)
    {
        return $this->paymentAuditEntry->save($paymentAuditEntry);
    }

    /**
     * Delete an payment audit entity.
     *
     * @param Entity\PaymentAuditEntry $paymentAuditEntry
     *
     * @return bool
     */
    public function deletePaymentAuditEntry(Entity\PaymentAuditEntry $paymentAuditEntry)
    {
        return $this->paymentAuditEntry->delete($paymentAuditEntry);
    }
}
