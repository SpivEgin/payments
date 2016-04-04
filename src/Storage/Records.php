<?php

namespace Bolt\Extension\Bolt\Payments\Storage;

use Bolt\Extension\Bolt\Members\AccessControl\Authorisation;
use Bolt\Extension\Bolt\Payments\Transaction\Transaction;
use Carbon\Carbon;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Message\ResponseInterface;

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
     * Fetches a customer payment.
     *
     * @param string $customer
     * @param string $gateway
     * @param string $transactionId
     *
     * @return Entity\Payment
     */
    public function getCustomerPayment($customer, $gateway, $transactionId)
    {
        return $this->payment->getCustomerPayment($customer, $gateway, $transactionId);
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

    /**
     * @param Authorisation   $authorisation
     * @param AbstractGateway $gateway
     * @param Transaction     $transaction
     *
     * @return bool
     */
    public function createPayment(Authorisation $authorisation, AbstractGateway $gateway, Transaction $transaction)
    {
        $payment = new Entity\Payment();

        $payment->setDate(Carbon::now());
        $payment->setCustomerId($authorisation->getGuid());
        $payment->setGateway($gateway->getShortName());
        $payment->setTransactionId($transaction->getTransactionId());
        $payment->setTransactionReference($transaction->getTransactionReference());
        $payment->setAmount($transaction->getAmount());
        $payment->setCurrency($transaction->getCurrency());
        $payment->setStatus('new');
        $payment->setDescription(null);

        return $this->payment->save($payment);
    }

    /**
     * @param Transaction       $transaction
     * @param ResponseInterface $response
     * @param string            $description
     *
     * @return bool
     */
    public function createPaymentAuditEntry(Transaction $transaction, ResponseInterface $response, $description)
    {
        $paymentAuditEntry = new Entity\PaymentAuditEntry();
        $paymentAuditEntry->setTransactionId($transaction->getTransactionId());
        $paymentAuditEntry->setTransactionReference($transaction->getTransactionReference());
        $paymentAuditEntry->setDate(Carbon::now());
        $paymentAuditEntry->setData($response->getData());
        $paymentAuditEntry->setDescription($description);

        return $this->paymentAuditEntry->save($paymentAuditEntry);
    }
}
