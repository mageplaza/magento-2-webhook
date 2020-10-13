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
 * @package     Mageplaza_Blog
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Webhook\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.1', '<')) {
            if (!$installer->tableExists('mageplaza_webhook_cron_schedule')) {
                $table = $installer->getConnection()
                    ->newTable($installer->getTable('mageplaza_webhook_cron_schedule'))
                    ->addColumn('id', Table::TYPE_INTEGER, null, [
                        'identity' => true,
                        'nullable' => false,
                        'primary' => true,
                        'unsigned' => true,
                    ], 'Schedule ID')
                    ->addColumn('hook_type', Table::TYPE_TEXT, 255, [], 'Hook Type')
                    ->addColumn('event_id', Table::TYPE_INTEGER, null, [
                        'unsigned' => true,
                        'nullable' => false
                    ], 'Event ID')
                    ->addColumn('status', Table::TYPE_TEXT, 10, [], 'Status')
                    ->addIndex($installer->getIdxName('mageplaza_webhook_cron_schedule', ['id']), ['id'])
                    ->addIndex(
                        $installer->getIdxName(
                            'mageplaza_webhook_cron_schedule',
                            ['id'],
                            AdapterInterface::INDEX_TYPE_UNIQUE
                        ),
                        ['id'],
                        ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
                    )
                    ->setComment('Cron Schedule Table');

                $installer->getConnection()->createTable($table);
            }
        }

        if (version_compare($context->getVersion(), '1.0.2', '<')) {
            $tableName = $installer->getTable('mageplaza_webhook_hook');
            $installer->getConnection()
                ->addColumn(
                    $tableName,
                    'order_status',
                    [
                        'type' => Table::TYPE_TEXT,
                        'length' => 255,
                        'comment' => 'Order Status',
                        'after' => 'status'
                    ]
                );
        }

        $installer->endSetup();
    }
}
