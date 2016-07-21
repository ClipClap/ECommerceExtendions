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
*  @author    CLIPCLAP <sac@clipclap.com>
*  @copyright 2014 CLIPCLAP
*  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*}
<link rel="stylesheet" href="{$css_dir}global.css" type="text/css" media="all">
<link href="{$css|escape:'htmlall':'UTF-8'}clipclap.css" rel="stylesheet" type="text/css">
{if $valid}
	<center>
		<table class="table-response">
			<tr align="center">
				<th colspan="2"><h1 class="md-h1">Transaccion completada</h1></th>
			</tr>
			<tr align="left">
				<td>Estado</td>
				<td>{$estado|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>Orden</td>
				<td>{$reference_code|escape:'htmlall':'UTF-8'}</td>
			</tr>		
			<tr align="left">
				<td>Token Autorizacion</td>
				<td>{$token|escape:'htmlall':'UTF-8'}</td>
			</tr>		
			<tr align="left">
				<td>Numero de Aprovacion</td>
				<td>{$numAprobacion|escape:'htmlall':'UTF-8'}</td>
			</tr>	
			<tr align="left">
				<td>Fecha de aprovacion</td>
				<td>{$fechaTransaccion|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>Total</td>
				<td>${$valor|escape:'htmlall':'UTF-8'}</td>
			</tr>
		</table>
		<p/>
		<h1>{$messageApproved|escape:'htmlall':'UTF-8'}</h1>
	</center>
{else}
	<center>
		<table class="table-response">
			<tr align="center">
				<th colspan="2"><h1 class="md-h1">Transaccion Incompleta</h1></th>
			</tr>
			<tr align="left">
				<td>Estado</td>
				<td>{$estado|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>Orden</td>
				<td>{$reference_code|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>Token Autorizacion</td>
				<td>{$token|escape:'htmlall':'UTF-8'}</td>
			</tr>
			<tr align="left">
				<td>Total</td>
				<td>${$valor|escape:'htmlall':'UTF-8'}</td>
			</tr>
		</table>

		<h4><a href="{$back_link}" class="button warning"> < Volver a la caja</a></h4>
	</center>
{/if}