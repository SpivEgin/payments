<?php

namespace Bolt\Extension\Bolt\Payments\Storage\Schema\Table;

use Bolt\Storage\Database\Schema\Table\BaseTable;

/**
 * Payment table.
 *
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
class Payment extends BaseTable
{
    /**
     * {@inheritdoc}
     */
    protected function addColumns()
    {
        $this->table->addColumn('id',                    'integer',  ['autoincrement' => true]);
        $this->table->addColumn('date',                  'datetime', []);
        $this->table->addColumn('customer_id',           'guid',     []);
        $this->table->addColumn('gateway',               'string',   ['length' => 64]);
        $this->table->addColumn('transaction_id',        'string',   ['length' => 128]);
        $this->table->addColumn('transaction_reference', 'string',   ['length' => 128]);
        $this->table->addColumn('amount',                'decimal',  ['scale'  => 2,    'precision' => 7]);
        $this->table->addColumn('currency',              'string',   ['length' => 3]);
        $this->table->addColumn('status',                'string',   ['length' => 32]);
        $this->table->addColumn('description',           'string',   ['length' => 1024, 'notnull' => false]);
    }

    /**
     * {@inheritdoc}
     */
    protected function addIndexes()
    {
        $this->table->addUniqueIndex(['customer_id', 'gateway', 'transaction_id']);

        $this->table->addIndex(['date']);
        $this->table->addIndex(['customer_id']);
        $this->table->addIndex(['gateway']);
        $this->table->addIndex(['transaction_id']);
        $this->table->addIndex(['transaction_reference']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setPrimaryKey()
    {
        $this->table->setPrimaryKey(['id']);
    }
}
