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
        $this->table->addColumn('id',            'integer',    ['autoincrement' => true]);
        $this->table->addColumn('date',          'datetime',   []);
        $this->table->addColumn('transactionid', 'string',     ['length' => 128]);
        $this->table->addColumn('description',   'string',     ['length' => 1024, 'notnull' => false]);
        $this->table->addColumn('data',          'json_array', ['notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addIndex(['date']);
        $this->table->addIndex(['transactionId']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }
}
