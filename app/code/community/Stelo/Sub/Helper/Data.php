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

class Stelo_Sub_Helper_Data extends Mage_Core_Helper_Abstract
{
  

    public function ConvertState($state){
       
      
      if(strlen($state) > 2){

        $from = utf8_decode("áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ");
        $to = "aaaaeeiooouucAAAAEEIOOOUUC";
        
        $state = strtr($state, $from, $to);

        $state = strtolower($state);

        $state = str_replace(' ', '_', $state);

        $_state_sigla = array(
            'acre' => 'AC',
            'alagoas' => 'AL',
            'amapa' => 'AP',
            'amazonas' => 'AM',
            'bahia' => 'BA',
            'ceara' => 'CE',
            'distrito_federal' => 'DF',
            'espirito_santo' => 'ES',
            'goias' => 'GO',
            'maranhao' => 'MA',
            'mato_grosso' => 'MT',
            'mato_grosso_do_sul' => 'MS',
            'minas_gerais' => 'MG',
            'para' => 'PA',
            'paraiba' => 'PB',
            'parana' => 'PR',
            'pernambuco' => 'PE',
            'piaui' => 'PI',
            'rio_de_janeiro' => 'RJ',
            'rio_grande_do_norte' => 'RN',
            'rio_grande_do_sul' => 'RS',
            'rondonia' => 'RO',
            'roraima' => 'RR',
            'santa_catarina' => 'SC',
            'sao_paulo' => 'SP',
            'sergipe' => 'SE',
            'tocantins' => 'TO'            
        );
		
		

		$stateFinal = $_state_sigla[$state];

       return $stateFinal;

      }else{
          return $state;
      }
       
    }
    
    
    public function getUrl(){
        
          if(Mage::getStoreConfig('payment/sub/ambiente')){
                $url = "http://api.stelo.com.br/";
                $url .= "ec/";
                $url .= "V1";
                return $url;
            }else{
                $url = "https://apic1.hml.stelo.com.br/";
                $url .= "ec/";
                $url .= "V1";
                return $url;
            }
    }
    
    public function AddInfoToOrder($order, $status) {
        $payment = $order->getPayment();
        $payment->setAdditionalInformation((array)$status);
        $payment->save();
    }
    
    public function getInstallmentMax(){
        return Mage::getStoreConfig('payment/subBoleto/installment_max');
    }
    
    public function getInstallmentMinValue(){
         return Mage::getStoreConfig('payment/subBoleto/installment_min');
   }
   
   public function getStatusUrl() {
       return Mage::getStoreConfig('payment/sub/url_status_api');
   }

   public function getClientId() {
       return Mage::getStoreConfig('payment/sub/clientId');
   }

   public function getClientSecret() {
       return Mage::getStoreConfig('payment/sub/clientSecret');
   }


}