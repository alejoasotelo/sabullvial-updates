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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\ListModel;

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select:not(.field-cliente-choices,.no-chosen)');

SabullvialHelper::loadEstadosCotizacionStylesheet();
SabullvialHelper::loadEstadosCotizacionTangoStylesheet();
SabullvialHelper::loadEstadosCotizacionPagoStylesheet();

$fieldsNotToRender = [
    'publish_up',
    'publish_down',
    'created', 'created_time',
    'created_by', 'created_user_id',
    'created_by_alias',
    'modified', 'modified_time',
    'modified_by', 'modified_user_id',
    'version', 'version_note',
    'hits',
    'id',
    'rules',
    'cliente'
];

$app = JFactory::getApplication();
$tabActive = $app->getUserState('com_sabullvial.edit.cotizacion.tab', 'general');
$app->setUserState('com_sabullvial.edit.cotizacion.tab', 'general');
$isUserSuperAdministrador = SabullvialHelper::isUserSuperAdministrador();

$vendedor = SabullvialHelper::getVendedor();
$isBullvialAdmin = $vendedor->get('tipo', '') == 'A';
$verTareas = $vendedor->get('ver.tareas', 0) != SabullvialHelper::VER_NINGUNA;
$canViewTareas = $isBullvialAdmin || $verTareas;

$this->tareas = [];
if ($canViewTareas) {
	$modelTareas = ListModel::getInstance('Tareas', 'SabullvialModel', ['ignore_request' => true]);
	$modelTareas->setState('filter.id_cotizacion', $this->item->id);
	$this->tareas = $modelTareas->getItems();
}
?>

<form action="<?php echo JRoute::_('index.php?option=com_sabullvial&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate"
enctype="multipart/form-data">

	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => $tabActive]); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_SABULLVIAL_COTIZACION_DETAILS')); ?>
		<div class="row-fluid">
			<div class="span10">
				<?php
                    foreach ($this->form->getFieldset('basic') as $field) {
                        if (in_array($field->fieldname, $fieldsNotToRender)) {
                            continue;
                        }

                        echo $field->renderField();
                    }
?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'products', JText::_('COM_SABULLVIAL_COTIZACION_PRODUCTOS')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo $this->form->renderField('products'); ?>
				<?php foreach ($this->form->getFieldset('products') as $field) : ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'purchaseorder', JText::_('COM_SABULLVIAL_COTIZACION_ORDEN_DE_COMPRA')); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span12">
				<?php echo $this->form->renderField('purchaseorder'); ?>
				<?php foreach ($this->form->getFieldset('purchaseorder') as $field) : ?>
					<?php echo $field->renderField(); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php if ($canViewTareas && count($this->tareas)): ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'tareas', JText::_('COM_SABULLVIAL_COTIZACION_TAREAS')); ?>
			<div class="row-fluid form-horizontal-desktop">
				<div class="span12">
					<?php echo $this->loadTemplate('tarea'); ?>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php if ($isUserSuperAdministrador): ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_SABULLVIAL_FIELDSET_PUBLISHING')); ?>
			<div class="row-fluid form-horizontal-desktop">
				<div class="span6">
					<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
					<div class="control-group">
						<div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
						<div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
					</div>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php if ($this->canDo->get('core.admin')) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'rules', JText::_('JCONFIG_PERMISSIONS_LABEL')); ?>
			<?php echo $this->form->getInput('rules'); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>
		
		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>	

	<?php if (SabullvialButtonsHelper::canEnviarAFacturacion($this->item->id_estadocotizacion)) {
	    $data = [
	        'cotizacion' => $this->item,
	        'params' => $this->state->params,
	        'type' => 'item',
	        'show' => 'modal'
	    ];
	    echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.enviar_a_facturacion', $data);
	} ?>

	<input type="hidden" name="task" value="cotizacion.edit" />
	<input type="hidden" name="subtask" value="" />
	<input type="hidden" name="return" value="<?php echo $app->input->get('return', null, 'BASE64'); ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>