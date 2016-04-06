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
    /** @var Repository\PaymentAudit */
    protected $paymentAudit;

    /**
     * Constructor.
     *
     * @param Repository\Payment      $payment
     * @param Repository\PaymentAudit $paymentAudit
     */
    public function __construct(Repository\Payment $payment, Repository\PaymentAudit $paymentAudit)
    {
        $this->payment = $payment;
        $this->paymentAudit = $paymentAudit;
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
     * @param Entity\PaymentAudit $paymentAudit
     *
     * @return bool
     */
    public function savePaymentAudit(Entity\PaymentAudit $paymentAudit)
    {
        return $this->paymentAudit->save($paymentAudit);
    }

    /**
     * Delete an payment audit entity.
     *
     * @param Entity\PaymentAudit $paymentAudit
     *
     * @return bool
     */
    public function deletePaymentAudit(Entity\PaymentAudit $paymentAudit)
    {
        return $this->paymentAudit->delete($paymentAudit);
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
     * @param Authorisation     $authorisation
     * @param Transaction       $transaction
     * @param ResponseInterface $response
     * @param string            $description
     *
     * @return bool
     */
    public function createPaymentAudit(Authorisation $authorisation, Transaction $transaction, ResponseInterface $response, $description)
    {
        $paymentAudit = new Entity\PaymentAudit();
        $paymentAudit->setCustomerId($authorisation->getGuid());
        $paymentAudit->setTransactionId($transaction->getTransactionId());
        $paymentAudit->setTransactionReference($transaction->getTransactionReference());
        $paymentAudit->setDate(Carbon::now());
        $paymentAudit->setData($response->getData());
        $paymentAudit->setDescription($description);

        return $this->paymentAudit->save($paymentAudit);
    }
}
