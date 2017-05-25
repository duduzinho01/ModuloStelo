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


class Stelo_Sub_Block_Adminhtml_Sales_Order extends Mage_Adminhtml_Block_Sales_Order {

    public function  __construct()
    {

        parent::__construct();

        $steloId = "";
        Mage::getModel('sub/api')->checkStatus($steloId);

    }
}