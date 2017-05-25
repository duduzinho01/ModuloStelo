<?php
/* 
 * Stelo Payment module
 * Developed By Soulmkt  
 * 
 * Funded by Stelo
 * http://www.stelo.com.br/
 * 
 * License: OSL 3.0
 */
class Stelo_Sub_Model_Observer extends Varien_Object {
   
    public function sendSub($observer) {
    /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getEvent()->getOrder();
        
        $order->sendNewOrderEmail();
        
        $payTypeMage = $order->getPayment()->getMethodInstance()->getCode();
        if ($payTypeMage == 'sub' || $payTypeMage == 'subBoleto') {
            
            $sub = Mage::getModel('sub/sub');
            
            $customerData = $sub->getCustomerData($order);
            
            $orderData = $sub->getOrderData();
            
            $orderData['orderId'] = $order->getIncrementId();
            
            $paymentData = $sub->getPaymentData($order);
            
            $order_id = $order->getEntityId();
            $jsonStrutcture = array(
                "orderData" => $orderData,
                "paymentData" => $paymentData,
                "customerData" => $customerData
            );

            

            $url = Mage::helper('sub')->getUrl();
            $url .= "/subacquirer/transactions/";
            $body = json_encode($jsonStrutcture);
            
            Mage::log($body, null, "request.log", true);
            
            $clientId = Mage::getStoreConfig('payment/sub/clientId');
             $clientSecret = Mage::getStoreConfig('payment/sub/clientSecret');
            $auth = base64_encode($clientId . ":" . $clientSecret);
            
        
            $header = array(
                "Authorization: " . $auth,
                "Content-Type: application/json"
            );
           
             $returnRequest = Mage::getModel("sub/api")->SendTemplate($url, $header, $body, "CURLOPT_POST");
            Mage::log($returnRequest , null, "return.log", true);
            $returnRequest = json_decode($returnRequest);
       
            
            $urlSub = "0";
              if($payTypeMage != "sub"){
                
                      $urlSub = $returnRequest->bankSlipURL;
                      Mage::getSingleton('core/session')->setSubUrl($urlSub);
              }
              if (property_exists($returnRequest, 'orderData')) {
                
                $steloId = sprintf('%.0f', $returnRequest->orderData->orderId);
              }
              if (property_exists($returnRequest->orderData, "nsu")) {
                $nsu = $returnRequest->orderData->nsu;
              }
              if (property_exists($returnRequest->orderData, "tid")) {
                $tid = $returnRequest->orderData->tid;
              }
              $month = null;
              $year = null;
              $expiryDate = null;
              $ccFlag = null;
              $cardNumber = null;
              if (property_exists($returnRequest->orderData, "cardNumber")) {
                $cardNumber = $returnRequest->orderData->cardNumber;

                $month = Mage::app()->getRequest()->getParam('payment')['cc_exp_month'];
                $year = Mage::app()->getRequest()->getParam('payment')['cc_exp_year'];
                
                $expiryDate = $year . '-' . $month . '-' . '01';
                
                $ccFlag = Mage::app()->getRequest()->getParam('payment')['cc_flag'];
                if (!$ccFlag || $ccFlag == "") {
                    $ccFlag = "Não Definido";
                }            
              }
            $installment = $paymentData['installment'];
            Mage::getModel('sub/api')->createNewSteloOrder($order_id, $orderData['orderId'], $steloId, "new", $urlSub, $expiryDate, $ccFlag, $nsu, $tid, $cardNumber, $installment);
            if($steloId != ""){
              Mage::getModel('sub/api')->checkStatus($steloId);
            }
            
            $status = Mage::getModel("sub/api")->getStatus($steloId);
            
            Mage::helper('sub')->AddInfoToOrder($order, $status);
            
            if($steloId == ""){

              $errorsStelo = $returnRequest->detail->message;
              $msgError = "";

              foreach ($errorsStelo as $errorStelo) {
                $msgError .= $errorStelo."\n";
              }
              $order->setState("canceled", "canceled", "Ocorreu um erro ao processar o seu pagamento, favor verifique os dados da sua conta e tente novamente.", true);
              $order->save();
              $order->sendOrderUpdateEmail(true, "Ocorreu um erro ao processar o seu pagamento, favor verifique os dados da sua conta e tente novamente.");

              $order->setState("canceled", "canceled", $msgError, false);
              $order->save();

            }
        
        }
    }
    
  public function addButtonCancel($observer) {

      $block = $observer->getBlock();

      if ($block instanceof Mage_Adminhtml_Block_Sales_Order_View) {

          $_order = $observer->getBlock()->getOrder();
          $orderRealId = $_order->getRealOrderId();
          $order = new Mage_Sales_Model_Order();
          $order->loadByIncrementId($orderRealId);
          $payment_method = $order->getPayment()->getMethodInstance()->getCode();

          //$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . "?orderId=" . $orderRealId;
          $url = Mage::helper("adminhtml")->getUrl("sub/adminhtml_index/cancelorder", array("orderid" => $orderRealId , "simpleid" => $_order->getId()));


          if($payment_method == "sub" || $payment_method == "subadquirencia"){

              $block->addButton('cancelarPedido', array(
                  'label'     => Mage::helper('Sales')->__('Cancelar Pedido Stelo'),
                  'onclick'   => "confirmSetLocation('Deseja solicitar o cancelamento dessa compra na stelo?', '{$url}')",
                  'class'     => 'go'
              ), 0, 100, 'header', 'header');

          }

    }

        if ($block instanceof Mage_Adminhtml_Block_Sales_Order) {

            //$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . "?consultaStelo=true";
            $url = Mage::helper("adminhtml")->getUrl("sub/adminhtml_index/statusorder");

            $block->addButton('checkStatusStelo', array(
                'label'     => Mage::helper('Sales')->__('Consultar Status Stelo'),
                'onclick'   => "setLocation('{$url}')",
                'class'     => 'go'
            ), 0, 100, 'header', 'header');
        }

  }

  public function subChangedSection($observer){
    $clientId = Mage::getStoreConfig('payment/sub/clientId');
    $clientSecret = Mage::getStoreConfig('payment/sub/clientSecret');
    $clientUrl = Mage::getStoreConfig('payment/sub/url_api');
    //Mage::log($clientUrl, null, "teste2.log",1);
    $baseurl = str_replace("index.php/", "skin/", Mage::getBaseUrl());

    $html = "(function (factory) {\n";

    $html .= "if (typeof define === \"function\" && define.amd) {\n";
    $html .= "    define([\"jquery\"], factory);\n";
    $html .= "} else {\n";
    $html .= "    factory(window.jQuery || window.Zepto);\n";
    $html .= "}\n";
    $html .= "}(function ($) {\n";
    $html .= "    var Register = {\n";
    $html .= "        set: function(obj) {\n";
    $html .= "            return this.get(obj)\n";
    $html .= "                .done(obj.callback)\n";
    $html .= "                .fail(obj.fnError);\n";
    $html .= "        },\n";
    $html .= "        get: function(obj) {\n";
    $html .= "            return jQuery.ajax({\n";
    $html .= "                url: obj.url,\n";
    $html .= "                type: 'post',\n";
    $html .= "                contentType: 'application/json',\n";
    $html .= "                data: JSON.stringify(obj.data),\n";
    $html .= "                beforeSend: function(xhr){\n";
    $html .= "                    xhr.setRequestHeader('clientID', obj.id);\n";
    $html .= "                }\n";
    $html .= "            });\n";
    $html .= "        }\n";
    $html .= "    };\n";
    $html .= "    jQuery.registerCard = function(obj) {\n";
    $html .= "        Register.set(obj);\n";
    $html .= "    };\n";
    $html .= "}));\n";
    $html .= "function generateToken(){\n";
    $html .= "    var mes = document.getElementById('sub_expiration').value;\n";  
    $html .= "    if(mes<10){\n";
    $html .= "        mes = \"0\" + mes;\n";
    $html .= "    }\n";
    $html .= "    var ano = document.getElementById('sub_expiration_yr').value;\n";
    $html .= "    ano = ano.substr(2);\n";
    $html .= "    var cardNumber = document.getElementById('sub_card_num').value;\n";
    $html .= "    var embossing = document.getElementById('sub_cc_owner').value;\n";
    $html .= "    var expiryDate = mes + \"/\" + ano;\n";
    $html .= "    var cvv = document.getElementById('sub_cc_cid').value;\n";
    $html .= "    var data = {\n";
    $html .= "       'number': cardNumber,\n";
    $html .= "       'embossing': embossing,\n";
    $html .= "       'expiryDate': expiryDate,\n";
    $html .= "       'cvv': cvv\n";
    $html .= "    };\n";
    $html .= "    jQuery.registerCard({\n";
    $html .= "        url: '".$clientUrl."',\n";
    $html .= "        data: data,\n";
    $html .= "        id: '".$clientId."',\n";
    $html .= "        callback: function(response) {\n";             
    $html .= "           document.getElementById(\"sub_token\").value = response.token;\n";
    $html .= "            jQuery('.error-server').hide();\n";
    $html .= "            jQuery('.validate-stelo').hide();\n";
    $html .= "var numero = jQuery(\"input#sub_card_num\").val();\n
          numero2 = numero.substring(0, 2);\n
          numero = numero.substring(0, 1);\n
          if(numero=='4'){
            jQuery(\"#bandeiras\").attr(\"src\",\"".$baseurl.'frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloVisa.png'."\");
            jQuery(\".cc-flag\").val(\"Visa\");
          }else if(numero=='5'){
            jQuery(\"#bandeiras\").attr(\"src\",\"".$baseurl.'frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloMaster.png'."\");
            jQuery(\".cc-flag\").val(\"Mastercard\");
          }else if(numero2=='34'||numero2=='37'){
            jQuery(\"#bandeiras\").attr(\"src\",\"".$baseurl.'frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloAmex.png'."\");
            jQuery(\".cc-flag\").val(\"American Express\");
          }else if(numero2=='30'||numero2=='36'||numero2=='38'){
            jQuery(\"#bandeiras\").attr(\"src\",\"".$baseurl.'frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloDinners.png'."\");
            jQuery(\".cc-flag\").val(\"Dinners\");
          }else {
            jQuery(\"#bandeiras\").attr(\"src\",\"".$baseurl.'frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloElo.png'."\");
            jQuery(\".cc-flag\").val(\"Elo\");
          }
          \n";
    $html .= "        },\n";
    $html .= "        fnError: function(response) {\n";
    $html .= "            jQuery('.error-server').show();\n";
    $html .= "            jQuery('.validate-stelo').show();\n";        
    $html .= "        }\n";
    $html .= "     });\n";
    $html .= " }\n";

    
    $url = Mage::getBaseDir('skin')."/frontend/base/default/sub/js/generate_token.js";
    $url = str_replace("index.php/", '', $url);
    $generateToken = fopen($url, 'w') or die("Unable to open file!");
    fwrite($generateToken, $html);
  fclose($generateToken);
  }

  public function listenercron($observer){
    // $var = $GLOBALS['HTTP_RAW_POST_DATA'];

    // $orders = Mage::getModel('sales/order')->getCollection()
    //   ->addAttributeToSelect('*')
    //   ->addAttributeToFilter('status', array('in' => array('pending_payment','processing','holded','payment_review')));
    // foreach ($orders as $order) {
    //       Mage::log($order->getIncrementId(), null, "steloidpedidostatus6.log", 1);
    //   $stelo = Mage::getModel('sub/subcustom')->getCollection();
    //   $stelo->addFieldToSelect('stelo_id');
    //   $stelo->addFieldToSelect('stelo_url');
    //   $stelo->addFieldToSelect('installment');
    //   $stelo->addFieldToFilter('mage_id', array('like' => $order->getIncrementId()));
    //   foreach ($stelo as $item) {
    //     $steloId= $item->getData('stelo_id');
    //     if (!empty($steloId)) {
    //       Mage::getModel('sub/api')->checkStatus($steloId);
    //     }
    //   }
    // }
  }
}