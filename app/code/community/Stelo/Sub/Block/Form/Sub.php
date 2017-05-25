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

class Stelo_Sub_Block_Form_Sub extends Mage_Payment_Block_Form
{
    protected function _construct(){
        
        parent::_construct();
        $this->setTemplate('sub/form/sub.phtml');
    
        
    }
    
    public function getOrderAmount(){
        
        $totals = Mage::getSingleton('checkout/cart')->getQuote()->getTotals();
        $grandTotal = $totals["grand_total"]->getValue();
        
        return $grandTotal;
        
    }
}
