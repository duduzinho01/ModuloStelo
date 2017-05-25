(function (factory) {
if (typeof define === "function" && define.amd) {
    define(["jquery"], factory);
} else {
    factory(window.jQuery || window.Zepto);
}
}(function ($) {
    var Register = {
        set: function(obj) {
            return this.get(obj)
                .done(obj.callback)
                .fail(obj.fnError);
        },
        get: function(obj) {
            return jQuery.ajax({
                url: obj.url,
                type: 'post',
                contentType: 'application/json',
                data: JSON.stringify(obj.data),
                beforeSend: function(xhr){
                    xhr.setRequestHeader('clientID', obj.id);
                }
            });
        }
    };
    jQuery.registerCard = function(obj) {
        Register.set(obj);
    };
}));
function generateToken(){
    var mes = document.getElementById('sub_expiration').value;
    if(mes<10){
        mes = "0" + mes;
    }
    var ano = document.getElementById('sub_expiration_yr').value;
    ano = ano.substr(2);
    var cardNumber = document.getElementById('sub_card_num').value;
    var embossing = document.getElementById('sub_cc_owner').value;
    var expiryDate = mes + "/" + ano;
    var cvv = document.getElementById('sub_cc_cid').value;
    var data = {
       'number': cardNumber,
       'embossing': embossing,
       'expiryDate': expiryDate,
       'cvv': cvv
    };
    jQuery.registerCard({
        url: 'https://apic1.hml.stelo.com.br/security/v1/cards/tokens',
        data: data,
        id: '7970d605396ed534fe4126ca8f707248',
        callback: function(response) {
           document.getElementById("sub_token").value = response.token;
            jQuery('.error-server').hide();
            jQuery('.validate-stelo').hide();
var numero = jQuery("input#sub_card_num").val();

          numero2 = numero.substring(0, 2);

          numero = numero.substring(0, 1);

          if(numero=='4'){
            jQuery("#bandeiras").attr("src","http://stelo.webjumpsolution.com/skin/frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloVisa.png");
            jQuery(".cc-flag").val("Visa");
          }else if(numero=='5'){
            jQuery("#bandeiras").attr("src","http://stelo.webjumpsolution.com/skin/frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloMaster.png");
            jQuery(".cc-flag").val("Mastercard");
          }else if(numero2=='34'||numero2=='37'){
            jQuery("#bandeiras").attr("src","http://stelo.webjumpsolution.com/skin/frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloAmex.png");
            jQuery(".cc-flag").val("American Express");
          }else if(numero2=='30'||numero2=='36'||numero2=='38'){
            jQuery("#bandeiras").attr("src","http://stelo.webjumpsolution.com/skin/frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloDinners.png");
            jQuery(".cc-flag").val("Dinners");
          }else {
            jQuery("#bandeiras").attr("src","http://stelo.webjumpsolution.com/skin/frontend/base/default/sub/images/payment_types/cartaoBandeiraSteloElo.png");
            jQuery(".cc-flag").val("Elo");
          }
          
        },
        fnError: function(response) {
            jQuery('.error-server').show();
            jQuery('.validate-stelo').show();
        }
     });
 }