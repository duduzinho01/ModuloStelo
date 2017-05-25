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

class Stelo_Sub_Model_Source_Ambiente
{
    public function toOptionArray()
    {
        return array(
            array('value' => '0', 'label' => Mage::helper('adminhtml')->__('Em Teste')),
            array('value' => '1', 'label' =>  Mage::helper('adminhtml')->__('Em Produção')),
        );
    }   
}