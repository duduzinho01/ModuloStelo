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

class Stelo_Sub_Block_Info_StandardBoleto extends Mage_Payment_Block_Info
{
    protected $_paymentSpecificInformation = null;
     
    protected function _construct()
    {
        parent::_construct();
        
        
       
        $this->setTemplate('sub/info/standard.phtml');
    }
    
    public function getInstallment($order){
        
        $_order = $order;
        $incrementid = $_order->getData('increment_id');
        
        $steloData = Mage::getModel("sub/api")->checkInstallment($incrementid);
        
        
        return $steloData;
        
    }
}