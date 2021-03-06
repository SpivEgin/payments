<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Repository;

use Bolt\Extension\Bolt\Payments\Storage\Entity;
use Bolt\Storage\Repository;

/**
 * Payment repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Payment extends Repository
{
    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = null)
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName());
    }

    /**
     * Fetches all of a customerId's payments.
     *
     * @param string $customerId
     *
     * @return Entity\Payment[]
     */
    public function getCustomerPayments($customerId)
    {
        $query = $this->getCustomerPaymentsQuery($customerId);

        return $this->findWith($query);
    }

    public function getCustomerPaymentsQuery($customerId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('customer_id = :customerId')
            ->setParameter('customerId', $customerId)
        ;

        return $qb;
    }

    /**
     * Fetches a customerId payment.
     *
     * @param string $customerId
     * @param string $gateway
     * @param string $transactionId
     *
     * @return Entity\Payment
     */
    public function getCustomerPayment($customerId, $gateway, $transactionId)
    {
        $query = $this->getCustomerPaymentQuery($customerId, $gateway, $transactionId);

        return $this->findOneWith($query);
    }

    public function getCustomerPaymentQuery($customerId, $gateway, $transactionId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('customer_id = :customerId')
            ->andWhere('gateway = :gateway')
            ->andWhere('transaction_id = :transactionId')
            ->setParameter('customerId', $customerId)
            ->setParameter('gateway', $gateway)
            ->setParameter('transactionId', $transactionId)
        ;

        return $qb;
    }

    /**
     * Fetches a payment by transaction ID.
     *
     * @param string $transactionId
     *
     * @return Entity\Payment
     */
    public function getPaymentByTransactionId($transactionId)
    {
        $query = $this->getPaymentByTransactionIdQuery($transactionId);

        return $this->findOneWith($query);
    }

    public function getPaymentByTransactionIdQuery($transactionId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('transaction_id = :transactionId')
            ->setParameter('transactionId', $transactionId)
        ;

        return $qb;
    }
}
