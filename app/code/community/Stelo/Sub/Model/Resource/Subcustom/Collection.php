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
class Stelo_Sub_Model_Resource_Subcustom_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
    protected function _construct()
    {
        $this->_init('sub/subcustom');
    }
}