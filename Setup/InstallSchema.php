<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Webhook
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

/**
 * Class InstallSchema
 * @package Mageplaza\Webhook\Setup
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $connection = $installer->getConnection();

        if (!$installer->tableExists('mageplaza_webhook_hook')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_webhook_hook'))
                ->addColumn(
                    'hook_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Feed Id'
                )
                ->addColumn('name', Table::TYPE_TEXT, 255, ['nullable' => false], 'Name')
                ->addColumn('status', Table::TYPE_INTEGER, 1, ['nullable' => false], 'Hook Status')
                ->addColumn('store_ids', Table::TYPE_TEXT, 64, ['nullable' => false], 'Stores')
                ->addColumn('hook_type', Table::TYPE_TEXT, 64, ['nullable' => false], 'Hook Type')
                ->addColumn('priority', Table::TYPE_INTEGER, 11, [], 'Priority')
                ->addColumn('payload_url', Table::TYPE_TEXT, 512, ['nullable' => false], 'Payload URL')
                ->addColumn('method', Table::TYPE_TEXT, 64, [], 'Method')
                ->addColumn('authentication', Table::TYPE_TEXT, 64, [], 'Authentication')
                ->addColumn('username', Table::TYPE_TEXT, 255, [], 'Username')
                ->addColumn('realm', Table::TYPE_TEXT, 512, [], 'Realm')
                ->addColumn('password', Table::TYPE_TEXT, 255, [], 'Password')
                ->addColumn('nonce', Table::TYPE_TEXT, 255, [], 'Nonce')
                ->addColumn('algorithm', Table::TYPE_TEXT, 255, [], 'Algorithm')
                ->addColumn('qop', Table::TYPE_TEXT, 255, [], 'qop')
                ->addColumn('nonce_count', Table::TYPE_TEXT, 255, [], 'Nonce Count')
                ->addColumn('client_nonce', Table::TYPE_TEXT, 255, [], 'Client Nonce')
                ->addColumn('opaque', Table::TYPE_TEXT, 255, [], 'Opaque')
                ->addColumn('headers', Table::TYPE_TEXT, '2M', [], 'Header')
                ->addColumn('content_type', Table::TYPE_TEXT, 64, [], 'Content-type')
                ->addColumn('body', Table::TYPE_TEXT, '2M', [], 'Header')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Update At'
                )
                ->setComment('Hook Table');

            $connection->createTable($table);
        }

        if (!$installer->tableExists('mageplaza_webhook_history')) {
            $table = $installer->getConnection()
                ->newTable($installer->getTable('mageplaza_webhook_history'))
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    ['identity' => true, 'nullable' => false, 'primary' => true, 'unsigned' => true],
                    'Log Id'
                )
                ->addColumn('hook_id', Table::TYPE_INTEGER, null, ['nullable' => false, 'unsigned' => true], 'Hook Id')
                ->addIndex($installer->getIdxName('mageplaza_webhook_history', ['hook_id']), ['hook_id'])
                ->addForeignKey(
                    $installer->getFkName('mageplaza_webhook_history', 'hook_id', 'mageplaza_webhook_hook', 'hook_id'),
                    'hook_id',
                    $installer->getTable('mageplaza_webhook_hook'),
                    'hook_id',
                    Table::ACTION_CASCADE
                )
                ->addColumn('hook_name', Table::TYPE_TEXT, 255, [], 'Hook Name')
                ->addColumn('status', Table::TYPE_TEXT, 64, [], 'Log Status')
                ->addColumn('store_ids', Table::TYPE_TEXT, 64, ['nullable' => false], 'Stores')
                ->addColumn('hook_type', Table::TYPE_TEXT, 64, ['nullable' => false], 'Hook Type')
                ->addColumn('response', Table::TYPE_TEXT, '2M', [], 'Response')
                ->addColumn('priority', Table::TYPE_INTEGER, 11, [], 'Priority')
                ->addColumn('payload_url', Table::TYPE_TEXT, 512, ['nullable' => false], 'Payload URL')
                ->addColumn('message', Table::TYPE_TEXT, 512, [], 'Message')
                ->addColumn('body', Table::TYPE_TEXT, '2M', [], 'Body')
                ->addColumn(
                    'created_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Created At'
                )
                ->addColumn(
                    'updated_at',
                    Table::TYPE_TIMESTAMP,
                    null,
                    ['default' => Table::TIMESTAMP_INIT],
                    'Update At'
                )
                ->setComment('Product Feed Table');

            $connection->createTable($table);
        }
        $installer->endSetup();
    }
}
