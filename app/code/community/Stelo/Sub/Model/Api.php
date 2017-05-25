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

class Stelo_Sub_Model_Api extends Varien_Object
{

    public function SendTemplate($url, $header, $body, $method)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if ($method == "CURLOPT_CUSTOMREQUEST") {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        } else {
            //  curl_setopt($ch, $method, 1);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == "CURLOPT_POST")
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function SendSSO($url, $header, $body)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($body)
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  //linha para ser enviada quando for method Post, mas acho que só usará a classe para metodo POST então não coloquei if
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    public function createNewSteloOrder($order_id, $order, $steloId, $status, $urlSub, $expiryDate, $ccFlag, $nsu, $tid, $cardNumber, $installment)
    {

        $newSteloId = Mage::getModel('sub/subcustom');


        $newSteloId->setMageId($order);

        $newSteloId->setSteloId($steloId);

        $newSteloId->setInstallment($installment);

        $newSteloId->setStatus($status);

        $newSteloId->setExpiryDate($expiryDate);

        $newSteloId->setCcFlag($ccFlag);

        $newSteloId->setCcNumber($cardNumber);

        $newSteloId->setNsu($nsu);

        $newSteloId->setTid($tid);

        $newSteloId->save();

        // $data = array('order_id'=>$order, 'steloid' => $steloId, 'status' => $status, 'cc_flag' => $ccFlag, 'cc_number' => $cardNumber, 'nsu' => $nsu, 'tid' => $tid );
        // $data = serialize($data);
        // $flatorderpay = Mage::getModel('sales/order_payment')->load($flatorder->getEntityId());
        // Mage::log($flatorderpay->getData(), null, "data2.log", 1);
        // $flatorderpay->setAdditionalData($data);
        // Mage::log($flatorderpay->getData(), null, "data2.log", 1);
        // $flatorderpay->save();

    }

    public function cancelOrder($orderId)
    {

        $collection = Mage::getModel('sub/subcustom')->getCollection();
        $collection->addFieldToSelect('id');
        $collection->addFieldToSelect('mage_id');
        $collection->addFieldToSelect('stelo_id');
        $collection->addFieldToSelect('status');
        $collection->addFieldToFilter('mage_id', array('like' => $orderId));

        $clientId = Mage::getStoreConfig('payment/sub/clientId');
        $clientSecret = Mage::getStoreConfig('payment/sub/clientSecret');
        $auth = base64_encode($clientId . ":" . $clientSecret);

        $header = array(
            "Authorization: " . $auth,
            "Content-Type: application/json"
        );

        foreach ($collection as $item) {
            $idTableStelo = $item->getData("id");
            $steloId = $item->getData('stelo_id');
            $mageId = $item->getData('mage_id');


            $url = $url = Mage::helper('sub')->getUrl();
            $url .= "/orders/transactions/" . $steloId;


            $returnRequest = $this->SendTemplate($url, $header, $body, "CURLOPT_CUSTOMREQUEST");

            $returnRequest = json_decode($returnRequest);
        }

        return $returnRequest;
    }

    public function checkInstallment($mageId)
    {
        $collection = Mage::getModel('sub/subcustom')->getCollection();
        $collection->addFieldToSelect('stelo_id');
        $collection->addFieldToSelect('stelo_url');
        $collection->addFieldToSelect('installment');
        $collection->addFieldToSelect('expiry_date');
        $collection->addFieldToSelect('cc_flag');
        $collection->addFieldToSelect('autorization_id');
        $collection->addFieldToSelect('cc_number');
        $collection->addFieldToSelect('nsu');
        $collection->addFieldToSelect('tid');
        $collection->addFieldToFilter('mage_id', array('like' => $mageId));


        foreach ($collection as $item) {

            $stelo["id"] = $item->getData('stelo_id');
            $stelo["installment"] = $item->getData('installment');
            $stelo["url"] = $item->getData('stelo_url');
            $stelo["expiry_date"] = $item->getData('expiry_date');
            $stelo["cc_flag"] = $item->getData('cc_flag');
            $stelo["autorization_id"] = $item->getData('autorization_id');
            $stelo["cc_number"] = $item->getData('cc_number');
            $stelo["nsu"] = $item->getData('nsu');
            $stelo["tid"] = $item->getData('tid');
        }

        return $stelo;
    }

    public function checkStatus($steloId)
    {
        if ($steloId == "") {
            $collection = Mage::getModel('sub/subcustom')->getCollection();
            $collection->addFieldToSelect('id');
            $collection->addFieldToSelect('mage_id');
            $collection->addFieldToSelect('stelo_id');
            $collection->addFieldToSelect('status');
            $collection->addFieldToFilter('status', array('nlike' => 'processing'));
            $collection->addFieldToFilter('status', array('nlike' => 'canceled'));
        } else {
            $collection = Mage::getModel('sub/subcustom')->getCollection();
            $collection->addFieldToSelect('id');
            $collection->addFieldToSelect('mage_id');
            $collection->addFieldToSelect('stelo_id');
            $collection->addFieldToSelect('status');
            $collection->addFieldToFilter('stelo_id', array('like' => $steloId));
        }

        $clientId = Mage::getStoreConfig('payment/sub/clientId');
        $clientSecret = Mage::getStoreConfig('payment/sub/clientSecret');
        $auth = base64_encode($clientId . ":" . $clientSecret);


        $header = array(
            "Authorization: " . $auth,
            "Content-Type: application/json"
        );

        $body = "";
        foreach ($collection as $item) {
            $idTableStelo = $item->getData("id");
            $steloId = $item->getData('stelo_id');
            $mageId = $item->getData('mage_id');
            $stateAct = $item->getData('status');

            $url = Mage::helper('sub/data')->getUrl();
            $url .= "/orders/transactions/" . $steloId;
            $returnRequest = $this->SendTemplate($url, $header, $body, "CURLOPT_GET");
            $returnRequest = json_decode($returnRequest);

            Mage::log($returnRequest, null, "stelo_retorno_consulta.log", true);
            $autorizationid = null;
            $cardnumber = null;
            $statusCode = null;
            if (property_exists($returnRequest, "steloStatus")) {
                if (property_exists($returnRequest, "autorizationId")) {
                    $autorizationid = $returnRequest->autorizationId;
                }
                if (property_exists($returnRequest, "cardNumber")) {
                    $cardnumber = $returnRequest->cardNumber;
                }
                if (property_exists($returnRequest->steloStatus, "statusCode")) {
                    $statusCode = $returnRequest->steloStatus->statusCode;
                }
                if (property_exists($returnRequest, "installment")) {
                    $installment = $returnRequest->installment;
                } else {
                    $installment = 0;
                }
            } else {
                $statusCode = "N";
                $installment = 100;
            }


            $this->changeStatus($mageId, $statusCode, $idTableStelo, $stateAct, $installment, $autorizationid, $cardnumber, $item, $returnRequest);
        }

        $msg = '{"message" :"Alteração de Status realizada com sucesso" }';
        return $msg;
    }

    public function getStatus($steloId)
    {
        $clientId = Mage::helper('sub/data')->getClientId(); //'7970d605396ed534fe4126ca8f707248';
        $clientSecret = Mage::helper('sub/data')->getClientSecret(); //'5edd6aeec23739179fdf73695172fe8a';

        $url = Mage::helper('sub/data')->getStatusUrl() . $steloId;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, "$clientId:$clientSecret");
        $result = curl_exec($ch);
        curl_close($ch);

        return json_decode($result);
    }

    public function changeStatus($mageId, $steloStatus, $idTableStelo, $stateAct, $installment, $autorizationid, $cardnumber, $stelo_sub = '', $returnRequest = '')
    {
        switch ($steloStatus) {
            case "I":
                $state = "pending_payment";
                $status = "pending_payment";
                $comment = "Pedido enviado para análise";
                break;
            case "E":
                $state = "payment_review";
                $status = "payment_review";
                $comment = "O pagamento encontra-se em análise";
                break;
            case "A":
                $state = "processing";
                $status = "processing";
                $comment = "O Pagamento foi aprovado";
                break;
            case "C":
                $state = "canceled";
                $status = "canceled";
                $comment = "O pagamento foi cancelado";
                break;
            case "N":
                $state = "canceled";
                $status = "canceled";
                $comment = "O Pagamento foi negado";
                break;
            case "NI":
                $state = "canceled";
                $status = "canceled";
                $comment = "O Pagamento foi recusado pela instituição financeira";
                break;
            case "NE":
                $state = "canceled";
                $status = "canceled";
                $comment = "O Pagamento foi recusado pela instituição financeira";
                break;
            case "S":
                $state = "payment_review";
                $status = "payment_review";
                $comment = "O pagamento encontra-se em análise";
                break;
            case "SP":
                $state = "payment_review";
                $status = "payment_review";
                $comment = "O pagamento encontra-se em análise";
                break;
            default:
                $state = "canceled";
                $status = "canceled";
                $comment = "canceled";
        }

        $order = Mage::getModel('sales/order')->loadByIncrementId($mageId);

        if ($status != $stateAct) {

            $order->setState($state, $status, $comment, true);
            $payment = $order->getPayment();


            $payment->setAdditionalInformation((array)$status);
            $payment->save();

            $stelo_sub->getCcFlag();
            $payment = $order->getPayment();

            $additionalData = array();
            $dataPayment = $payment->getAdditionalData();

            if (!empty($dataPayment)) {
                $additionalData = unserialize($dataPayment);
            }
            $additionalInformation = array();
            $dataPaymentAdditionalInformation = $payment->getAdditionalInformation();

            
            if (!empty($dataPaymentAdditionalInformation) && !is_array($dataPaymentAdditionalInformation)) {
                $additionalInformation = unserialize($payment->getAdditionalInformation());
            }


            $additionalData = serialize(
                array_merge(
                    $additionalData, 
                    array(
                        'cc_flag' => serialize($stelo_sub->getCcFlag()), 
                        'returnRequest' => serialize($returnRequest),
                        'order_createdat'=> $order->getCreatedAt(),
                        'order_updatedat'=>$order->getUpdatedAt(),
                        )
                    )
                );

           

            $payment->setAdditionalData($additionalData);
            $additionalInformation = serialize(
                array_merge(
                    $additionalInformation, 
                    array(
                        'cc_flag' => serialize($stelo_sub->getCcFlag()), 
                        'returnRequest' => serialize($returnRequest),
                        'order_createdat'=> $order->getCreatedAt(),
                        'order_updatedat'=>$order->getUpdatedAt(),
                        )
                    )
                );

            $payment->setAdditionalInformation($additionalInformation);
            $payment->save();

            $order->save();
            $order->sendOrderUpdateEmail(true, $comment);

            $steloOrder = Mage::getModel('sub/subcustom');
            $steloOrder = $steloOrder->load($idTableStelo);
            $steloOrder->setData("status", $status)->save();
            if ($installment != 0) {
                $steloOrder->setData("installment", $installment)->save();
            }
            $steloOrder->setAutorizationId($autorizationid)->save();
            $steloOrder->setCcNumber($cardnumber)->save();
        }
    }

    public function getSubUrl()
    {

        $urlPortal = Mage::getSingleton('core/session')->getSubUrl();
        Mage::getSingleton('core/session')->unsSubUrl();

        return $urlPortal;
    }

}