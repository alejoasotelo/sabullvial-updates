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

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task)
	{
		if (task == "estadoremito.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
');

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
    method="post" name="adminForm" id="adminForm">
    <div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => $tabActive]); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_SABULLVIAL_ESTADO_REMITO_DETAILS')); ?>
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
    <input type="hidden" name="task" value="estadoremito.edit" />
	<input type="hidden" name="return" value="<?php echo $input->get('return', null, 'BASE64'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>