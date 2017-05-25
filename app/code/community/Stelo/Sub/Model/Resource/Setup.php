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
class Stelo_Sub_Model_Resource_Setup extends Mage_Core_Model_Resource_Setup {
    public function startSetup()
    {
        $this->getConnection()->startSetup();
        return $this;
    }

    public function endSetup()
    {
        $this->getConnection()->endSetup();
        return $this;
    }
}