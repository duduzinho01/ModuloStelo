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

class Stelo_Sub_Block_Checkout_Success extends Mage_Checkout_Block_Onepage_Success
{
    public function __construct() {
        parent::__construct();
        
        $this->setTemplate('sub/checkout/success.phtml');
    }
}