<?php
/**
* 2015 CLIPCLAP
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
*  @copyright 2015 CLIPCLAP
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/
class ClipClapResponseModuleFrontController extends ModuleFrontController
{
	public function initContent()
    {   
		parent::initContent();

        $this->context = Context::getContext();     
     
		$clipclap = new ClipClap();
		
		$state = 'CLIPCLAP_OS_PENDING';

		// print_r($_REQUEST);

			$estado = $_REQUEST['estado'];
			$codRespuesta = $_REQUEST['codRespuesta'];
			$paymentRef = $_REQUEST['paymentRef'];
			$token = $_REQUEST['token'];
			$numAprobacion = $_REQUEST['numAprobacion'];
			$fechaTransaccion = $_REQUEST['fechaTransaccion'];
			
		$messageApproved = '';
		$valid = false;
		if ($estado=='Aprobado'&&$codRespuesta=='3001'&&(!empty($numAprobacion)&&$numAprobacion!='undefined')) {
			$estado_tx = 'Transaccion aprovada';
			$messageApproved = 'Gracias por su compra!';
			$valid = true;
			$state = 'PS_OS_PAYMENT';
		} else if ($estado=='Rechazado') {
			if ($codRespuesta=='1000') {
				$estado_tx = 'Se rechazo la transaccion en la aplicacion.';
				$state = 'CLIPCLAP_OS_REJECTED';
			} else if($codRespuesta=='1002') {
				$estado_tx = 'No se concreto la transacción en la aplicacion.';
				$state = 'CLIPCLAP_OS_FAILED';
			}
		}
		 else {
			$estado_tx = 'La transaccion no se concreto';
		}
		
		$reference_code = reset(split('_', $paymentRef));
		echo "order code=".$reference_code;

		$cart = new Cart((int)$reference_code);
		// echo "<pre>";
		// print_r($cart);
		// echo "</pre>";
		$valor = $cart->getordertotal(true);

	 
		if ($valid)
		{
			if ($cart->orderExists())
			{
				$order = new Order((int)Order::getOrderByCartId($cart->id));
				
				if (_PS_VERSION_ < '1.5')
				{
					echo "v1.5";
					$current_state = $order->getCurrentState();
					if ($current_state != Configuration::get('PS_OS_PAYMENT'))
					{
						$history = new OrderHistory();
						$history->id_order = (int)$order->id;
						$history->changeIdOrderState((int)Configuration::get($state), $order->id);
						$history->addWithemail(true);
					}
				}
				else
				{
					echo "v 2";
					$current_state = $order->current_state;
					echo "state".$current_state;
					if ($current_state != Configuration::get('PS_OS_PAYMENT'))
					{
						$history = new OrderHistory();
						$history->id_order = (int)$order->id;
						$history->changeIdOrderState((int)Configuration::get($state), $order, true);
						$history->addWithemail(true);
					}
				}
			}
			else
			{
				echo "no existe";
				$customer = new Customer((int)$cart->id_customer);
				Context::getContext()->customer = $customer;
				Context::getContext()->currency = $currency_cart;

				$clipclap->validateOrder((int)$cart->id, (int)Configuration::get($state), (float)$cart->getordertotal(true), 'ClipClap', null, array(), (int)$currency_cart->id, false, $customer->secure_key);
				Configuration::updateValue('CLIPCLAP_CONFIGURATION_OK', true);
				$order = new Order((int)Order::getOrderByCartId($cart->id));
				if (_PS_VERSION_ < '1.5')
				{
					echo "v1.5";
					$current_state = $order->getCurrentState();
					if ($current_state != Configuration::get('PS_OS_PAYMENT'))
					{
						$history = new OrderHistory();
						$history->id_order = (int)$order->id;
						$history->changeIdOrderState((int)Configuration::get($state), $order->id);
						$history->addWithemail(true);
					}
				}
				else
				{
					echo "v 2";
					$current_state = $order->current_state;
					echo "state".$current_state;
					if ($current_state != Configuration::get('PS_OS_PAYMENT'))
					{
						$history = new OrderHistory();
						$history->id_order = (int)$order->id;
						$history->changeIdOrderState((int)Configuration::get($state), $order, true);
						$history->addWithemail(true);
					}
				}
			}
			
			$this->context->smarty->assign(
				array(
					'estado' => $estado_tx,
					'codRespuesta' => $codRespuesta,
					'paymentRef' => $paymentRef,
					'reference_code' => $reference_code,
					'token' => $token,
					'numAprobacion' => $numAprobacion,
					'fechaTransaccion' => $fechaTransaccion,
					'messageApproved' => $messageApproved,
					'valor' => $valor,
					'valid' => $valid,
					'css' => '../modules/clipclap/css/'
				)
			);

		}
		else
		{
			$this->context->smarty->assign(
				array(
					'estado' => $estado_tx,
					'codRespuesta' => $codRespuesta,
					'paymentRef' => $paymentRef,
					'reference_code' => $reference_code,
					'token' => $token,
					'valor' => $valor,
					'valid' => $valid,
					'back_link'=> $_SERVER['HTTP_REFERER'],
					'css' => '../modules/clipclap/css/'
				)
			);
		}/**/

        $this->setTemplate('response.tpl');
    }
}
?>