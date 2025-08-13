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

use Joomla\CMS\Factory;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$document = Factory::getDocument();

$document->addScriptDeclaration("
	Joomla.submitbutton = function(task)
	{
		if (task == 'cliente.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
		{
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
");

SabullvialHelper::loadEstadosClienteStylesheet();

$fieldsNotToRender = [
    'published',
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
];

$tabActive = Factory::getApplication()->getUserState('com_sabullvial.edit.cotizacion.tab', 'general');
$isUserSuperAdministrador = SabullvialHelper::isUserSuperAdministrador();
?>
<form action="<?php echo JRoute::_('index.php?option=com_sabullvial&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">
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

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="cliente.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>