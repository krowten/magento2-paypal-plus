<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Iways\PayPalPlus\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'training_category_country'
         */
        $table = $installer->getTable('sales_order_payment');
        $connection = $installer->getConnection();
        $connection->addColumn(
            $table,
            'ppp_reference_number',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Reference Number'
            ]);
        $connection->addColumn(
            $table,
            'ppp_instruction_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Instruction Type'
            ]);
        $connection->addColumn(
            $table,
            'ppp_payment_due_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Payment Due Date'
            ]);
        $connection->addColumn(
            $table,
            'ppp_note',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Note'
            ]);
        $connection->addColumn(
            $table,
            'ppp_bank_name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Bank Name'
            ]);
        $connection->addColumn(
            $table,
            'ppp_account_holder_name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Holder Name'
            ]);
        $connection->addColumn(
            $table,
            'ppp_international_bank_account_number',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus International Bank Account Number'
            ]);
        $connection->addColumn(
            $table,
            'ppp_bank_identifier_code',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Bank Identifier Code'
            ]);
        $connection->addColumn(
            $table,
            'ppp_routing_number',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Routing Number'
            ]);
        $connection->addColumn(
            $table,
            'ppp_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Amount'
            ]);
        $connection->addColumn(
            $table,
            'ppp_currency',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal Plus Currency'
            ]);

        $installer->endSetup();
    }
}
