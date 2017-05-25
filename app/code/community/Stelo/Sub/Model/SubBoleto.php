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

class Stelo_Sub_Model_SubBoleto extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'subBoleto';
    protected $_formBlockType = 'sub/form_subBoleto';
    protected $_infoBlockType = 'sub/info_standardBoleto';
    
    
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('sub/onepage/success', array('_secure' => true));
    }
 
}
?>
