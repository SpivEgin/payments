<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Repository;

use Bolt\Storage\Repository;

/**
 * Payment audit entry repository.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentAudit extends Repository
{
    /**
     * {@inheritdoc}
     */
    public function createQueryBuilder($alias = null)
    {
        return $this->em->createQueryBuilder()
            ->from($this->getTableName());
    }
}
