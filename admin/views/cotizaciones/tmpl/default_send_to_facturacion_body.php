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

			<div class="row-fluid">

				<?php if ($this->row->has_custom_products): ?>
					<?php echo Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_HAS_CUSTOM_PRODUCTS_DESC'); ?>
				<?php else: ?>
					<?php echo Text::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_DESC'); ?>

					<div class="center">
						<button type="button" class="btn btn-danger" onclick="return Joomla.listItemTask('cb<?php echo $this->row->i;?>', 'cotizaciones.srl');">
							<?php echo Text::_('JYES'); ?>
						</button>
						<button type="button" class="btn btn-default" onclick="return Joomla.listItemTask('cb<?php echo $this->row->i;?>', 'cotizaciones.prueba');">
							<?php echo Text::_('JACTION_TEST'); ?>
						</button>
					</div>
				<?php endif; ?> 
			</div>
		</div>
	</div>
</div>