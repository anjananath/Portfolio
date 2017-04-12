<?php

namespace Voilaah\PointsRedemption\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface {

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /**
         * @table voilaah_merchantvoucher_vouchers
         *
         * Install Vouchers Table
         */
        if (!$setup->tableExists('voilaah_pointsredemption_transactions')) {

            $table = $setup->getConnection()->newTable(
                $setup->getTable('voilaah_pointsredemption_transactions')
            )

                // id
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true
                    ],
                    'Transaction ID'
                )

                // order_id
                ->addColumn(
                    'order_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ],
                    'Order ID'
                )

                // customer_id
                ->addColumn(
                    'customer_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                        'unsigned' => true
                    ],
                    'Customer ID'
                )

                // description
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false
                    ],
                    'Description'
                )

                // type
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    255,
                    [
                        'nullable' => false
                    ],
                    'Type'
                )

                // amount
                ->addColumn(
                    'amount',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'nullable' => false,
                    ],
                    'Transaction Amount'
                )

                // created_at
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    [
                        'nullable' => false,
                        'default' => Table::TIMESTAMP_INIT
                    ],
                    'Transaction Datetime'
                );

            $setup->getConnection()->createTable($table);

        }

        $setup->endSetup();
    }

}