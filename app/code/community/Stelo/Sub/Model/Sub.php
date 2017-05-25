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

class Stelo_Sub_Model_Sub extends Mage_Payment_Model_Method_Abstract {

    protected $_code = 'sub';
    protected $_formBlockType = 'sub/form_sub';
    protected $_infoBlockType = 'sub/info_standard';
    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = false;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;

    public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $additional = array('PaymentMethod' => 'stelo_sub', 'method' => $data->getMethod(), 'token' => $data->getToken(), 'installment' => $data->getInstallment());
        $additional = serialize($additional);

        $info->setInstallment($data->getInstallment())
                ->setAdditionalData($additional)
                ->setToken($data->getToken());
        return $this;
    }

    public function validate() {
        parent::validate();

        $info = $this->getInfoInstance();

        $no = $info->getInstallment();
        if (empty($no)) {
            $errorCode = 'invalid_data';
            $errorMsg = $this->_getHelper()->__('Installment é um campo obrigatório');
        }

        if (isset($errorMsg)) {
            Mage::throwException($errorMsg);
        }
        return $this;
    }

    //Dados do comprador
    public function getCustomerData($order) {

        $data = $order->getBillingAddress()->getData();
        //$order = $order->getQuote();

        $billingAddress["street"] = substr($order->getBillingAddress()->getStreet1(), 0, 60);
        $billingAddress["number"] = substr($order->getBillingAddress()->getStreet2(), 0, 10);
        if (!isset($billingAddress["number"]))
            $billingAddress["number"] = substr($order->getBillingAddress()->getNumero(), 0, 10);
        $billingAddress["complement"] = substr($order->getBillingAddress()->getStreet3(), 0, 40);
        $billingAddress["neighborhood"] = substr($order->getBillingAddress()->getStreet4(), 0, 60);
        if (!isset($billingAddress["neighborhood"]))
            $billingAddress["neighborhood"] = substr($order->getBillingAddress()->getBairro(), 0, 60);
        $billingAddress["zipCode"] = preg_replace("/[^0-9]/", "", $order->getBillingAddress()->getPostcode());
        $billingAddress["city"] = substr($order->getBillingAddress()->getCity(), 0, 60);
        $billingAddress["state"] = $order->getBillingAddress()->getRegion();
        $billingAddress["state"] = Mage::helper('sub')->ConvertState(utf8_decode($billingAddress["state"]));
        $billingAddress["country"] = "BR";

        $shippingAddress["street"] = substr($order->getShippingAddress()->getStreet1(), 0, 60);
        $shippingAddress["number"] = substr($order->getShippingAddress()->getStreet2(), 0, 10);
        if (!isset($shippingAddress["number"]))
            $shippingAddress["number"] = substr($order->getShippingAddress()->getNumero(), 0, 10);
        $shippingAddress["complement"] = substr($order->getShippingAddress()->getStreet3(), 0, 40);
        $shippingAddress["neighborhood"] = substr($order->getShippingAddress()->getStreet4(), 0, 60);
        if (!isset($shippingAddress["neighborhood"]))
            $shippingAddress["neighborhood"] = substr($order->getShippingAddress()->getBairro(), 0, 60);
        $shippingAddress["zipCode"] = preg_replace("/[^0-9]/", "", $order->getShippingAddress()->getPostcode());
        $shippingAddress["city"] = substr($order->getShippingAddress()->getCity(), 0, 60);
        $shippingAddress["state"] = $order->getShippingAddress()->getRegion();
        $shippingAddress["state"] = Mage::helper('sub')->ConvertState(utf8_decode($shippingAddress["state"]));
        $shippingAddress["country"] = "BR";
        $num = $order->getBillingAddress()->getFax();
        if (empty($num)) {
            $num = $order->getBillingAddress()->getCelular();
        }
        if (empty($num)) {
            $num = $order->getCustomerCelular();
        }

        if (strlen(preg_replace('/[^0-9]/', '', $num)) == 0) {
            $num = '';
        }
        
        // if (empty($billingAddress["neighborhood"])) {
        //     Mage::log('Endereço incompleto', null, "endereco.log", 1);
        //     Mage::getSingleton('core/session')->addError($this->__('Favor verificar todos os campos obrigatórios do endereço de entrega.'));
        //     $this->_redirect('customer/account/edit/id/'.$data['customer_address_id'].'/');
        //     return false;
        //     //Mage::throwException("Favor verificar os dados do endereço de entrega.");
        // }

        $telefone = $order->getBillingAddress()->getTelephone();
        $telefone = preg_replace('/\D/', '', $telefone);
        $dddTel = substr($telefone, 0, 2);
        $telefone = substr($telefone, 2);
        if (empty($num)) {
            $phoneData = array(
                array(
                    'type' => 'LANDLINE', //phoneType
                    'areaCode' => $dddTel,
                    'number' => $telefone
                ),
                array(
                    'type' => 'CELL', //phoneType
                    'areaCode' => $dddTel,
                    'number' => $telefone
                ),
            );
        } else {
            $cel = $num;
            $cel = preg_replace('/\D/', '', $cel);
            $dddCel = substr($cel, 0, 2);
            $cel = substr($cel, 2);

            $phoneData = array(
                array(
                    'type' => 'LANDLINE', //phoneType
                    'areaCode' => $dddTel,
                    'number' => $telefone
                ),
                array(
                    'type' => 'CELL', //phoneType
                    'areaCode' => $dddCel,
                    'number' => $cel
                ),
            );
        }
        

        $customerData["customerIdentity"] = preg_replace("/[^0-9]/", "", $order->getCustomerTaxvat());
        if (empty($customerData["customerIdentity"]))
            $customerData["customerIdentity"] = preg_replace("/[^0-9]/", "", $order->getCustomerCpf());

        if (empty($customerData["customerIdentity"]))
            $customerData["customerIdentity"] = preg_replace("/[^0-9]/", "", $order->getCustomer()->getCpfcnpj());

        if (empty($customerData["customerIdentity"]))
            $customerData["customerIdentity"] = preg_replace("/[^0-9]/", "", $order->getBillingAddress()->getCpfcnpj());
        $name = $order->getCustomerFirstname() . " " . $order->getCustomerLastname();
        $name = substr($name, 0, 60); 
        $customerData["customerName"] = $name;

        $customerData["customerEmail"] = $order->getCustomerEmail();
        if (array_key_exists("dob", $order->getCustomer()->_data)) {
            $birthDate = substr($order->getCustomer()->_data["dob"], 0, 10);
        }
        if (!empty($birthDate) && $birthDate != "null") {
            $customerData["birthDate"] = $birthDate;
        }
        $customerData["billingAddress"] = $billingAddress;
        $customerData["shippingAddress"] = $shippingAddress;
        $customerData["phoneData"] = $phoneData;


        return $customerData;
    }

    //Captura dados da ordem
    public function getOrderData() {

        $orderData = array();
        $orderData['plataformId'] = "1";
        $orderData['shippingBehavior'] = "default";
        $orderData["secureCode"] = Mage::getSingleton('core/session')->getSecureCode();
        return $orderData;
    }

    public function getPaymentData($order) {
        
        $grandTotal = $order->getGrandTotal();
        
        $discount = $order->getDiscountAmount();
        
        //Mage::log($order->getPayment()->debug(), null, "cartao.log", true);
        //Pegando tipo de pagamento
        if ($order->getPayment()->getMethodInstance()->getCode() != "subBoleto") {
            $payType = "credit";
            $installment = $order->getPayment()->getInstallment();
            $card["token"] = $order->getPayment()->getToken();
           
        } else {
            $payType = "bankSlip";
            $installment = "100";
        }


        $paymentData["paymentType"] = $payType;
        $paymentData["amount"] = number_format($grandTotal, 2, '.', '');
        $paymentData["discountAmount"] = number_format($discount, 2, '.', '');
        $paymentData["freight"] = number_format($order->getShippingAmount(), 2, '.', '');
        $paymentData["currency"] = "BRL";
        $paymentData["installment"] = $installment;

        $cartItems = $order->getAllVisibleItems();
        $cont = 0;
        
        foreach ($cartItems as $item) {

            $productId = $item->getProductId();

            $itemsData["productName"] = $item->getName();
            $itemsData["productPrice"] = Mage::helper('tax')->getPrice($item->getProduct(), $item->getPriceInclTax(), true);
            $itemsData["productQuantity"] = $item->getQtyOrdered();
            $itemsData["productSku"] = substr(preg_replace("/[^a-zA-Z0-9_]/", "", $item->getSku()), 0, 8);
            
            $itemsCollection[$cont] = $itemsData;

            $cont++;
        }
        
        if (isset($card)) {
            $paymentData["cardData"] = array ('token' => $card["token"]);
        }
        $paymentData["cartData"] = $itemsCollection;
        
        return $paymentData;
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('sub/onepage/success', array('_secure' => true));
    }

}

?>
