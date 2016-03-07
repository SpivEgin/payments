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
     * Fetches all of a customer's payments.
     *
     * @param string $customer
     *
     * @return Entity\Payment[]
     */
    public function getCustomerPayments($customer)
    {
        $query = $this->getCustomerPaymentsQuery($customer);

        return $this->findWith($query);
    }

    public function getCustomerPaymentsQuery($customer)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('customer = :customer')
            ->setParameter('customer', $customer)
        ;

        return $qb;
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
        $query = $this->getCustomerPaymentQuery($customer, $gateway, $transactionId);

        return $this->findOneWith($query);
    }

    public function getCustomerPaymentQuery($customer, $gateway, $transactionId)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('*')
            ->where('customer = :customer')
            ->andWhere('gateway = :gateway')
            ->andWhere('transactionId = :transactionId')
            ->setParameter('customer', $customer)
            ->setParameter('gateway', $gateway)
            ->setParameter('transactionId', $transactionId)
        ;

        return $qb;
    }
}
