<?php
 /**
 * @package     Webjump_Sub
 * @author      Webjump Core Team <desenvolvedores@webjump.com>
 * @copyright   2016 Webjump (http://www.webjump.com.br)
 * @license     http://www.webjump.com.br  Copyright
 * @link        http://www.webjump.com.br
 */

$installer = $this;
$installer->startSetup();

$steloWalletTable = $installer->getTable('stelo_sub');


$installer->getConnection()
    ->addColumn($steloWalletTable, 'expiry_date', array(
        'type' => Varien_Db_Ddl_Table::TYPE_DATE,
        'comment' => 'CC Expiry Date',
        'nullable' => true,
    ));


$installer->getConnection()
    ->addColumn($steloWalletTable, 'cc_flag', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 50,
    'comment' => 'CC Flag',
    'nullable' => true,
));

$installer->getConnection()
    ->addColumn($steloWalletTable, 'autorization_id', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 50,
    'comment' => 'autorizationId',
    'nullable' => true,
));
    
$installer->getConnection()
    ->addColumn($steloWalletTable, 'cc_number', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 50,
    'comment' => 'Numero Cartão',
    'nullable' => true,
));

$installer->getConnection()
    ->addColumn($steloWalletTable, 'nsu', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 50,
    'comment' => 'nsu',
    'nullable' => true,
));

$installer->getConnection()
    ->addColumn($steloWalletTable, 'tid', array(
    'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
    'length'    => 50,
    'comment' => 'tid',
    'nullable' => true,
));

$installer->endSetup();