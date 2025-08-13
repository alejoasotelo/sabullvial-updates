<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Language\Text;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$maxLenghtDescripcion = $this->state->params->get('cotizacion_productos_modificar_descripcion_maxlength');

JFactory::getDocument()->addScriptDeclaration("
	jQuery(document).ready(function() {
		// Regenero los tooltips de la modal de la cotización
		jQuery('#previewCotizacion').on('shown.bs.modal', function(){
			jQuery(this).find('.hasTooltip').tooltip();
		}).on('hide.bs.modal', function() {			
			jQuery(this).find('.hasTooltip').tooltip('destroy');
		});

		jQuery('#previewCotizacion .hasTooltip').tooltip('destroy');
	});
");

$vendedor = SabullvialHelper::getVendedor();
$isRevendedor = $vendedor->get('esRevendedor', false);

JText::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_IIBB');
?>
<div class="contentpane component">
	<div class="container-popup">

		<div class="form form-vertical">

			<div class="row-fluid">
				<div class="span4">

					<div class="control-group">
						<div class="control-label">
							<label>Cliente</label>
						</div>
						<div class="controls">
							<div v-if="quotation.cliente">
                                <b>{{quotation.cliente.razon_social}}</b>
                                <br class="chzn-on-title-hide"/>
                                <small class="chzn-on-title-hide">{{quotation.cliente.cod_client}}</small> | 
                                <small class="chzn-on-title-hide">{{quotation.cliente.cuit}}</small> | 
                                <small class="chzn-on-title-hide">{{numberFormat(quotation.cliente.saldo, 2, '.', ',')}}</small> | 
                                <small class="chzn-on-title-hide text-info">
									<span>Prom. </span> 
									<span>{{quotation.cliente.PROMEDIO_ULT_REC}}</span>
                                </small>
							</div>
							<div v-else class="">
								<b>{{quotation.consumidorFinal}}</b>
								<br/>
								<span class="small">
									{{quotation.documentoTipo == 80 ? 'Cuit': 'DNI'}}: {{quotation.documentoNumero}}
								</span>
							</div>
						</div>
					</div>
				</div>

				<div class="span4">
					<div class="control-group" :class="{error: !isCartEmailValid}">
						<div class="control-label">
							<label for="cotizacion_email">Email cliente</label>
						</div>
						<div class="controls">
							<input id="cotizacion_email" type="email" class="span12" v-model="cart.email" :disabled="busy" placeholder="Opcional"/>
							<span class="help-inline small" :class="{hidden: isCartEmailValid}">Ingrese un email válido</span>
						</div>
					</div>
				</div>

				<div class="span4">
					<div class="control-group <?php echo $isRevendedor ? 'hidden' : ''; ?>">
						<div class="control-label">
							<label for="filter_id_condicionventa_fake" class="width-auto">Condición de Venta</label>
							<select class="width-auto pull-right no-chosen select-mini" :disabled="busy" v-model="quotation.id_condicionventa" @change="onChangeCondicionVenta(quotation.id_condicionventa)">
								<option v-for="item of condicionesVentaReales" :key="item.id" :value="item.id">{{item.dias}}</option>
							</select>
						</div>
						<div class="controls">
							<chosen-select id="filter_id_condicionventa_fake" :disabled="busy" v-model="quotation.id_condicionventa_fake" @change="onChangeCondicionVentaFake" class="span12" data-placeholder="- Selecciona una condición de venta -">
								<option v-for="item of condicionesVenta" :value="item.value">{{item.text}}</option>
							</chosen-select>
						</div>
					</div>
				</div>
			</div>

			<div class="row-fluid">
				<div class="span4">
					<div class="control-group">
						<div class="control-label">
							<label for="cotizacion_solicitante">Solicitante</label>
						</div>
						<div class="controls">
							<input id="cotizacion_solicitante" type="text" class="span12" v-model="cart.solicitante" max="255" :disabled="busy" placeholder="Opcional" />
						</div>
					</div>
				</div>
				<div class="span4">

					<div class="control-group">
						<div class="control-label">
							<label for="cotizacion_delivery_term">Plazo de entrega</label>
						</div>
						<div class="controls">
							<input id="cotizacion_delivery_term" type="text" class="span12" v-model="cart.delivery_term" :disabled="busy" placeholder="Inmediato" />
						</div>
					</div>
				</div>
				<div class="span4">

					<div class="control-group">
						<div class="control-label">
							<label for="cotizacion_observations">Observaciones</label>
						</div>
						<div class="controls">
							<textarea id="cotizacion_observations" rows="2" v-model="cart.observations" :disabled="busy" class="span12"></textarea>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="table-responsive">
			<table id="table" class="table table-striped table-hover">
				<thead>
					<tr>
						<th style="min-width:100px" class="nowrap">
							<?php echo JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_NOMBRE'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_PRECIO_POR_UNIDAD'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_CANTIDAD'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_DESCUENTO'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<?php echo JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_SUBTOTAL'); ?>
						</th>
						<th width="1%" class="nowrap center">
							<button type="button" class="btn btn-mini button btn-success hasTooltip" @click="addProductoPersonalizado" title="Agregar producto personalizado">
								<span class="icon icon-plus" aria-hidden="true"></span>
							</button>
						</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="(producto, index) in cart.productos" :key="producto.id">
						<td class="nowrap">
							<input v-if="producto.custom" type="text" v-model="producto.nombre" class="input-xxlarge" placeholder="<?php echo Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_PRODUCTO_NOMBRE_FIELD'); ?>"/>
							<template v-else-if="vendedor.modificar.descripcion" >
								<input type="text" v-model="producto.nombre" class="input-xxlarge input-producto-nombre" placeholder="<?php echo Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_PRODUCTO_NOMBRE_FIELD'); ?>" maxlength="<?php echo (int)$maxLenghtDescripcion; ?>"/>
								<div v-if="producto.nombre.length > <?php echo (int)$maxLenghtDescripcion; ?>">
									<small class="text-error">({{producto.nombre.length}})<?php echo JText::sprintf('COM_SABULLVIAL_PUNTOS_DE_VENTA_PRODUCTO_NOMBRE_MAX_LENGTH_ERROR', (int)$maxLenghtDescripcion); ?></small>
								</div>
							</template>
							<span v-else>
								{{producto.nombre}}
							</span>
						</td>
						<td>
							<input type="number" v-model="producto.precioFinal" inputmode="numeric" step="0.01" min="0" :disabled="busy" :readonly="vendedor.tipo == 'V' && !vendedor.modificar.precios" class="input-small" />
						</td>
						<td>
							<input type="number" v-model="producto.cantidad" inputmode="numeric" min="0" :disabled="busy" class="input-mini" />
						</td>
						<td>
							<div class="input-append">
								<input type="number" v-model="producto.descuento" inputmode="numeric" min="0" max="100" :disabled="busy" class="input-mini" step="1" @keyup="limitDescuento($event, producto)" />
								<span class="add-on">
									<span aria-hidden="true" class="icon">%</span>
								</span>
							</div>
						</td>
						<td class="nowrap">
							${{numberFormat(producto.cantidad * producto.precioFinal * (1 - (producto.descuento/100)), 2, ',', '.')}}
						</td>
						<td width="1%">
							<button type="button" @click="toggleProducto(producto)" class="btn btn-micro hasTooltip" title="Eliminar producto">
								<span class="icon-trash" aria-hidden="true"></span>
							</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="row-fluid">

			<div class="span8">
				<div class="control-group">
					<div class="control-label">
						<label id="filter_ordendecompra_file-lbl" for="filter_ordendecompra_file" class="hasPopover" 
							title="<?php echo JText::_('COM_SABULLVIAL_COTIZACION_ORDEN_DE_COMPRA_FILE_LABEL');?>" 
							data-content="<?php echo JText::_('COM_SABULLVIAL_COTIZACION_ORDEN_DE_COMPRA_FILE_DESC');?>">
							<?php echo JText::_('COM_SABULLVIAL_COTIZACION_ORDEN_DE_COMPRA_FILE_LABEL');?>
						</label>
					</div>
					<div class="controls">
						<input type="file" size="40" class="inputbox" @change="onChangeOrdenCompraFile"><br/>
						
						<input type="text" class="inputbox" v-model="cart.ordendecompra_numero" placeholder="Número orden de compra">					
					</div>
				</div>
				<div class="control-group">
					<div class="control-label">
						<label>Mensaje Interno</label>
					</div>
					<div class="controls">
						<textarea v-model="cart.note" rows="4"></textarea>					
					</div>
				</div>

			</div>
			<div class="span4">
				<table class="table-totales table">
					<tr>
						<td>
							<label class="text-right"><?php echo JText::_('COM_SABULLVIAL_SUBTOTAL') ?>:</label>
						</td>
						<td>${{numberFormat(subtotal, 2, ',', '.')}}</td>
					</tr>
					<tr>
						<td><label class="text-right"><?php echo JText::_('COM_SABULLVIAL_IVA_21') ?>:</label></td>
						<td>${{numberFormat(iva, 2, ',', '.')}}</td>
					</tr>
					<tr :class="{disabled: !direccion_porc_ib}">
						<td><label class="text-right">{{JText('COM_SABULLVIAL_PUNTOS_DE_VENTA_IIBB', numberFormat(direccion_porc_ib ? direccion_porc_ib : 0, 2, ',', '.'))}}%:</label></td>
						<td>${{numberFormat(iibb, 2, ',', '.')}}</td>
					</tr>
					<tr>
						<td><label class="text-right"><b><?php echo JText::_('COM_SABULLVIAL_TOTAL_CON_IVA') ?>:</b></label></td>
						<td class="alert-success"><b>${{numberFormat(total, 2, ',', '.')}}</b></td>
					</tr>
				</table>

				<button-yesno v-model="cart.esperar_pagos" name="filter[esperar_pagos]" :class="{disabled: busy}" class="btn-esperar-pagos">
					<?php echo JText::_('COM_SABULLVIAL_COTIZACION_ESPERAR_PAGOS'); ?>
				</button-yesno>
			</div>

		</div>
	</div>
</div>