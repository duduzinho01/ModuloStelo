<?php
/* 
 * Stelo Payment module
 * Developed By Soulmkt  
 * http://www.soulmkt.com.br
 * 
 * Funded by Stelo
 * http://www.stelo.com.br/
 * 
 * License: OSL 3.0
 */

$installer = $this;
$installer->startSetup();
$installer->run(" 
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `installment` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `installment` VARCHAR( 255 ) NOT NULL ;
    

ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `token` VARCHAR( 255 );
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `token` VARCHAR( 255 );
");
$installer->endSetup();