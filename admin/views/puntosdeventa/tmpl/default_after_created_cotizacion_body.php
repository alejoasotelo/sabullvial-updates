<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

?>
<div class="contentpane component">
	<div class="container-popup">

		<div class="form form-vertical">

			<div class="row-fluid" v-if="afterCreatedCotizacionStep == 1">

				<?php echo Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_AFTER_CREATED_COTIZACION_DESC'); ?>

			</div>

			<div class="row-fluid" v-else-if="afterCreatedCotizacionStep == 2">

				<?php echo Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_DESC'); ?>

				<p>

					<button type="button" class="btn btn-danger" @click="sendToFacturacion(COTIZACION_TIPO_SRL)" :disabled="!quotationCreated.id">
						<?php echo Text::_('JYES'); ?>
					</button>
					<button type="button" class="btn btn-default ml-1" @click="sendToFacturacion(COTIZACION_TIPO_PRUEBA)" :disabled="!quotationCreated.id">
						<?php echo Text::_('JACTION_TEST'); ?>
					</button>

				</p>

			</div>
		</div>
	</div>
</div>