{*
* 2014 CLIPCLAP
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    CLIPCLAP <info@clipclap.com>
*  @copyright 2014 CLIPCLAP
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}

<div class="row">
	<div class="col-xs-12 col-md-6"> 
		<p class="payment_module">
			<!-- <a href="{$module_dir|escape:'htmlall':'UTF-8'}validation.php" class="link-redirect">
			</a> -->
			{l s='Pagar usando la Billetera ClipClap' mod='clipclap'} <br>
		</p><button id="botonClipClap"></button> 
	</div>
</div>

 <form class="md-form" id="clipclap_form" name="clipclap_form" method="post" action="{$response_url}">
	<input type="hidden" name="estado" id="estado"  />
	<input type="hidden" name="codRespuesta" id="codRespuesta"  />
	<input type="hidden" name="paymentRef" id="paymentRef"  />
	<input type="hidden" name="token" id="token"  />
	<input type="hidden" name="numAprobacion" id="numAprobacion" />
	<input type="hidden" name="fechaTransaccion" id="fechaTransaccion" />
</form> 


<script type="text/javascript">
	var tax_value = Math.ceil({($cart->getordertotal(true)/$tipo_iva)*100});
	var total_value = Math.ceil({$cart->getordertotal(true)});
	window._$clipclap = window._$clipclap || {};
	window._$clipclap._setKey = '{$secret_key}';
	window._$clipclap._themeButton = '{$tema_boton}';
	window._$clipclap._Buttons = {
        "#botonClipClap":{
            'paymentRef': '{$paymentRef}',
            'netValue': total_value+'',
            'taxValue': tax_value+'',
            'tipValue': '0',
            'description': '{foreach item=item from=$cart->getProducts()}{$item["name"]}, {/foreach}'
        }
    };
	window._$clipclap.transactionState = function(status, codRespuesta, paymentRef, token, numAprobacion, fechaTransaccion){

        document.getElementById('estado').value = status;
        document.getElementById('codRespuesta').value = (codRespuesta);
        document.getElementById('paymentRef').value = (paymentRef);
        document.getElementById('token').value = (token);
        document.getElementById('numAprobacion').value = (numAprobacion);
        document.getElementById('fechaTransaccion').value = (fechaTransaccion);

        document.getElementById('clipclap_form').submit();
    }
    {if $test_mode == 'Si'}
    	window._$clipclap._debugButton = true; 
    {/if}

    	var cc = document.createElement('script'); cc.type = 'text/javascript'; cc.async = true;
    	cc.src = 'https://clipclap.co/paybutton/js/paybutton.min.js';
        var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(cc, s);

    var evt = document.createEvent('Event');
    evt.initEvent('load',false,false);
    window.dispatchEvent(evt);

</script>