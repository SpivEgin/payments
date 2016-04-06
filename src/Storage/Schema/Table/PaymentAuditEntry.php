<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Payment table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class PaymentAuditEntry extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',             'integer',    ['autoincrement' => true]);
        $this->table->addColumn('date',           'datetime',   []);
        $this->table->addColumn('customer_id',    'guid',       []);
        $this->table->addColumn('transaction_id', 'string',     ['length' => 128]);
        $this->table->addColumn('description',    'string',     ['length' => 1024, 'notnull' => false]);
        $this->table->addColumn('data',           'json_array', ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['date']);
        $this->table->addIndex(['customer_id']);
        $this->table->addIndex(['transaction_id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }

    /**
     * {@inheritdoc}
     */
    protected function addForeignKeyConstraints()
    {
        $this->table->addForeignKeyConstraint($this->tablePrefix . 'payments', ['customer_id'], ['customer_id'], [], 'guid_payment_meta');
    }
}
