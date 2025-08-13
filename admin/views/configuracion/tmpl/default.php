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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

?>
<form action="<?php echo JRoute::_('index.php?option=com_sabullvial&layout=default'); ?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
		<div class="form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_SABULLVIAL_CONFIGURACION_DETAILS'); ?></legend>
				<div class="row-fluid">
					<div class="span12">
						<?php
                        foreach ($this->form->getFieldset() as $field) {
                            echo $field->renderField();
                        }
?>
					</div>
				</div>
			</fieldset>
		</div>
		<input type="hidden" name="task" value="configuracion.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>