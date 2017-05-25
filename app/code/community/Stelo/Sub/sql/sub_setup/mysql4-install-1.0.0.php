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

$query = "CREATE TABLE IF NOT EXISTS stelo_sub (id int not null auto_increment, mage_id varchar(15) not null, stelo_url varchar(200) not null, stelo_id varchar(20) not null, installment varchar(200), status VARCHAR(20) not null, PRIMARY KEY (id))";

$installer->run($query);

$installer->endSetup();


