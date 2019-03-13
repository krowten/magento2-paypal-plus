<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * Author Robert Hillebrand - hillebrand@i-ways.de - i-ways sales solutions GmbH
 * Copyright i-ways sales solutions GmbH © 2015. All Rights Reserved.
 * License http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
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
                'comment' => 'PayPal PLUS Reference Number'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_instruction_type',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Instruction Type'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_payment_due_date',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Payment Due Date'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_note',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Note'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_bank_name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Bank Name'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_account_holder_name',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Holder Name'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_international_bank_account_number',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS International Bank Account Number'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_bank_identifier_code',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Bank Identifier Code'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_routing_number',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Routing Number'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_amount',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Amount'
            ]
        );
        $connection->addColumn(
            $table,
            'ppp_currency',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                'length' => 255,
                'nullable' => true,
                'default' => null,
                'comment' => 'PayPal PLUS Currency'
            ]
        );

        $installer->endSetup();
    }
}
