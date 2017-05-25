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

class Stelo_Sub_IndexController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        
    }

    //Aguarda uma chamada da Stelo para alterar o status de um pedido
    public function listenerAction() {


        $var = $GLOBALS['HTTP_RAW_POST_DATA'];

        if (!empty($var)) {
            $var = json_decode($var);

            $steloId = $var->steloId;

            $msg = Mage::getModel('sub/api')->checkStatus($steloId);
            echo $msg;
        }
    }

    
}
