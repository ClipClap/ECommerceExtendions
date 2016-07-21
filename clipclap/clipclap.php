<?php
/**
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
*  @author    CLIPCLAP <sac@clipclap.com>
*  @copyright 2014 CLIPCLAP
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

if (!defined('_PS_VERSION_'))
	exit;

class ClipClap extends PaymentModule {

private $_postErrors = array();

public function __construct()
{
	$this->name = 'clipclap';
	$this->tab = 'payments_gateways';
	$this->version = '2.1.1';
	$this->author = 'ClipClap';
	$this->need_instance = 0;
	$this->currencies = true;
	$this->currencies_mode = 'checkbox';
	parent::__construct();

	$this->displayName = $this->l('ClipClap pagos con App Billetera');
	$this->description = $this->l('Forma de pago Billetera ClipClap');

	$this->confirmUninstall = $this->l('Are you sure you want to uninstall?');
	/* Backward compatibility */
	if (_PS_VERSION_ < '1.5')
		require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

	$this->checkForUpdates();
}

public function install()
{
	$this->_createStates();

	if (!parent::install()
		|| !$this->registerHook('payment')
		|| !$this->registerHook('paymentReturn'))
		return false;
	return true;
}

public function uninstall()
{
	if (!parent::uninstall()
		|| !Configuration::deleteByName('CLIPCLAP_SECRET_KEY')
		|| !Configuration::deleteByName('CLIPCLAP_BUTTON_THEME')
		|| !Configuration::deleteByName('CLIPCLAP_TIPO_IVA')
		|| !Configuration::deleteByName('CLIPCLAP_TEST')
		|| !Configuration::deleteByName('CLIPCLAP_OS_PENDING')
		|| !Configuration::deleteByName('CLIPCLAP_OS_FAILED')
		|| !Configuration::deleteByName('CLIPCLAP_OS_REJECTED'))
		return false;
	return true;
}

public function getContent()
{
	$html = '';

	if (isset($_POST) && isset($_POST['submitClipClap']))
	{
		$this->_postValidation();
		if (!count($this->_postErrors))
		{
			$this->_saveConfiguration();
			$html .= $this->displayConfirmation($this->l('Settings updated'));
		}
		else
			foreach ($this->_postErrors as $err)
				$html .= $this->displayError($err);
	}
	return $html.$this->_displayAdminTpl();
}

private function _displayAdminTpl()
{
	$this->context->smarty->assign(array(
		'tab' => array(
			'intro' => array(
				'title' => $this->l('How to configure'),
				'content' => $this->_displayHelpTpl(),
				'icon' => '../modules/clipclap/img/info-icon.gif',
				'tab' => 'conf',
				'selected' => (Tools::isSubmit('submitClipClap') ? false : true),
				'style' => 'config_clipclap'
			),
			'credential' => array(
				'title' => $this->l('Credentials'),
				'content' => $this->_displayCredentialTpl(),
				'icon' => '../modules/clipclap/img/credential.png',
				'tab' => 'crendeciales',
				'selected' => (Tools::isSubmit('submitClipClap') ? true : false),
				'style' => 'credentials_clipclap'
			),
		),
		'tracking' => 'http://www.prestashop.com/modules/clipclap.png?url_site='.Tools::safeOutput($_SERVER['SERVER_NAME']).'&id_lang='.
		(int)$this->context->cookie->id_lang,
		'img' => '../modules/clipclap/img/',
		'css' => '../modules/clipclap/css/',
		'lang' => ($this->context->language->iso_code != 'en' || $this->context->language->iso_code != 'es' ? 'en' : $this->context->language->iso_code)
	));

	return $this->display(__FILE__, 'views/templates/admin/admin.tpl');
}

private function _displayHelpTpl()
{
	return $this->display(__FILE__, 'views/templates/admin/help.tpl');
}

private function _displayCredentialTpl()
{
	$this->context->smarty->assign(array(
		'formCredential' => './index.php?tab=AdminModules&configure=clipclap&token='.Tools::getAdminTokenLite('AdminModules').
		'&tab_module='.$this->tab.'&module_name=clipclap',
		'credentialTitle' => $this->l('Log in'),
		'credentialInputVar' => array(
			'secret_key' => array(
				'name' => 'secret_key',
				'required' => true,
				'value' => (Tools::getValue('secret_key') ? Tools::safeOutput(Tools::getValue('secret_key')) :
				Tools::safeOutput(Configuration::get('CLIPCLAP_SECRET_KEY'))),
				'type' => 'text',
				'label' => 'Secret key',
				'desc' => 'Clave secreta generada por clipclap',
			),
			'tema_boton' => array(
				'name' => 'tema_boton',
				'required' => true,
				'value' => (Tools::getValue('tema_boton') ? Tools::safeOutput(Tools::getValue('tema_boton')) :
				Tools::safeOutput(Configuration::get('CLIPCLAP_BUTTON_THEME'))),
				'type' => 'select',
				'values' => array( 'blue'=>'Azul', 'black'=>'Negro', 'white'=>'Blanco'),
				'label' => 'Tema del boton',
				'desc' => 'Tema del boton generado <br>',
			),
			'tipo_iva' => array(
				'name' => 'tipo_iva',
				'required' => true,
				'value' => (Tools::getValue('tipo_iva') ? (int)Tools::getValue('tipo_iva') : (int)Configuration::get('CLIPCLAP_TIPO_IVA')),
				'type' => 'select',
				'values' => array( 1 => 'IVA Regular del 16%', 2 => 'IVA Reducido del 5%', 3 => 'IVA Excento del 0%', 4 => 'IVA Excluído del 0%', 5 => 'Consumo Regular 8%', 6 => 'Consumo Reducido 4%', 7 => 'IVA Ampliado 20%'),
				'label' => 'Tipo de iva',
				'desc' => 'Porcentaje del iva de las transacciones',
			),
			'test' => array(
				'name' => 'test',
				'required' => false,
				'value' => (Tools::getValue('test') ? Tools::safeOutput(Tools::getValue('test')) : Tools::safeOutput(Configuration::get('CLIPCLAP_TEST'))),
				'type' => 'radio',
				'values' => array('Si', 'No'),
				'label' => $this->l('Debug mode'),
				'desc' => $this->l(''),
			))));
	return $this->display(__FILE__, 'views/templates/admin/credential.tpl');
}


public function hookPayment($params)
{
	if (!$this->active)
		return;

	if (_PS_VERSION_ < '1.5')
		$response_url = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'modules/clipclap/pages/response.php';
	else
		$response_url = 'http://'.htmlspecialchars($_SERVER['HTTP_HOST'], ENT_COMPAT, 'UTF-8').__PS_BASE_URI__.'index.php?fc=module&module=clipclap&controller=response';

	$cart = Context::getContext()->cart;
	// echo "<pre>";
	// print_r($cart);
	// echo "</pre>";
	switch (Tools::safeOutput(Configuration::get('CLIPCLAP_BUTTON_THEME'))) {
		case 1:
			$tipo_iva = 16;
			break;
		case 2:
			$tipo_iva = 5;
			break;
		case 5:
			$tipo_iva = 8;
			break;
		case 6:
			$tipo_iva = 4;
			break;
		case 7:
			$tipo_iva = 20;
			break;
		case 3:
		case 4:
		default:
			$tipo_iva = 0;
			break;
	}

	$this->context->smarty->assign(array(
		'css' => '../modules/clipclap/css/',
		'module_dir' => _PS_MODULE_DIR_.$this->name.'/',
		'secret_key' => Tools::safeOutput(Configuration::get('CLIPCLAP_SECRET_KEY')),
		'tema_boton' => Tools::safeOutput(Configuration::get('CLIPCLAP_BUTTON_THEME')),
		'test_mode' => Tools::safeOutput(Configuration::get('CLIPCLAP_TEST')),
		'response_url' => $response_url,
		'paymentRef' => Tools::safeOutput((int)$cart->id).'_'.time(),
        'tipo_iva' => $tipo_iva,

	));

	return $this->display(__FILE__, 'views/templates/hook/clipclap_payment.tpl');
}

private function _postValidation()
{
	if (!Validate::isCleanHtml(Tools::getValue('secret_key'))
		|| !Validate::isGenericName(Tools::getValue('secret_key')))
		$this->_postErrors[] = $this->l('Debe indicar su llave secreta');

	if (!Validate::isCleanHtml(Tools::getValue('tema_boton'))
		|| !Validate::isGenericName(Tools::getValue('tema_boton')))
		$this->_postErrors[] = $this->l('Debe indicar el tema del boton');

	if (!Validate::isCleanHtml(Tools::getValue('tipo_iva'))
		|| !Validate::isGenericName(Tools::getValue('tipo_iva')))
		$this->_postErrors[] = $this->l('Debe indicar el tipo de iva');

	if (!Validate::isCleanHtml(Tools::getValue('test'))
		|| !Validate::isGenericName(Tools::getValue('test')))
		$this->_postErrors[] = $this->l('You must indicate if the transaction mode is test or not');

}

private function _saveConfiguration()
{
	Configuration::updateValue('CLIPCLAP_SECRET_KEY', (string)Tools::getValue('secret_key'));
	Configuration::updateValue('CLIPCLAP_BUTTON_THEME', (string)Tools::getValue('tema_boton'));
	Configuration::updateValue('CLIPCLAP_TIPO_IVA', (string)Tools::getValue('tipo_iva'));
	Configuration::updateValue('CLIPCLAP_TEST', Tools::getValue('test'));
}

private function _createStates()
{
	if (!Configuration::get('CLIPCLAP_OS_PENDING'))
	{
		$order_state = new OrderState();
		$order_state->name = array();
		foreach (Language::getLanguages() as $language)
			$order_state->name[$language['id_lang']] = 'Pending';

		$order_state->send_email = false;
		$order_state->color = '#FEFF64';
		$order_state->hidden = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;

		if ($order_state->add())
		{
			$source = dirname(__FILE__).'/img/logo.jpg';
			$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
			copy($source, $destination);
		}
		Configuration::updateValue('CLIPCLAP_OS_PENDING', (int)$order_state->id);
	}

	if (!Configuration::get('CLIPCLAP_OS_FAILED'))
	{
		$order_state = new OrderState();
		$order_state->name = array();
		foreach (Language::getLanguages() as $language)
			$order_state->name[$language['id_lang']] = 'Failed Payment';

		$order_state->send_email = false;
		$order_state->color = '#8F0621';
		$order_state->hidden = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;

		if ($order_state->add())
		{
			$source = dirname(__FILE__).'/img/logo.jpg';
			$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
			copy($source, $destination);
		}
		Configuration::updateValue('CLIPCLAP_OS_FAILED', (int)$order_state->id);
	}

	if (!Configuration::get('CLIPCLAP_OS_REJECTED'))
	{
		$order_state = new OrderState();
		$order_state->name = array();
		foreach (Language::getLanguages() as $language)
			$order_state->name[$language['id_lang']] = 'Rejected Payment';

		$order_state->send_email = false;
		$order_state->color = '#8F0621';
		$order_state->hidden = false;
		$order_state->delivery = false;
		$order_state->logable = false;
		$order_state->invoice = false;

		if ($order_state->add())
		{
			$source = dirname(__FILE__).'/img/logo.jpg';
			$destination = dirname(__FILE__).'/../../img/os/'.(int)$order_state->id.'.gif';
			copy($source, $destination);
		}
		Configuration::updateValue('CLIPCLAP_OS_REJECTED', (int)$order_state->id);
	}
}

private function checkForUpdates()
{
	// Used by PrestaShop 1.3 & 1.4
	if (version_compare(_PS_VERSION_, '1.5', '<') && self::isInstalled($this->name))
		foreach (array('2.0') as $version)
		{
			$file = dirname(__FILE__).'/upgrade/upgrade-'.$version.'.php';
			if (Configuration::get('CLIPCLAP') < $version && file_exists($file))
			{
				include_once($file);
				call_user_func('upgrade_module_'.str_replace('.', '_', $version), $this);
			}
		}
}
}
?>
