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
JHtml::_('formbehavior.chosen', 'select:not(.no-chosen)');

$app = Factory::getApplication();
$input = $app->input;

Factory::getDocument()->addScriptDeclaration('
    Joomla.submitbutton = function(task)
    {
        if (task == "tarea.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
        {
            Joomla.submitform(task, document.getElementById("adminForm"));
        }
    };

    jQuery(document).ready(function($) {
        jQuery("select.select2:not(.no-select2)").select2({
            theme: "bootstrap",
            language: "es",
            width: "100%"
        });

        jQuery(".select2usuarios").select2({
            allowClear: true,
            placeholder: "Seleccione un usuario",
            theme: "bootstrap",
            language: "es",
            width: "100%"
        });
    });
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
    'notas',
    'id_estadocotizacion',
    'cotizacionhistorico'
];

$tabActive = Factory::getApplication()->getUserState('com_sabullvial.edit.cotizacion.tab', 'general');
$isUserSuperAdministrador = SabullvialHelper::isUserSuperAdministrador();
$isUserVendedor = SabullvialHelper::isUserVendedor();
?>
<form action="<?php echo JRoute::_('index.php?option=com_sabullvial&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="form-horizontal">
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => $tabActive]); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_SABULLVIAL_TAREA_DETAILS')); ?>
        <div class="row-fluid">
            <div class="span10">
                <?php
                foreach ($this->form->getFieldset('basic') as $field) {
                    if (in_array($field->fieldname, $fieldsNotToRender)) {
                        continue;
                    }

                    if ($field->fieldname == 'usuarios' && $isUserVendedor) {
                        echo str_replace('control-group', 'control-group hidden', $field->renderField());
                        continue;
                    }

                    echo $field->renderField();
                }
                ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'notas', JText::_('COM_SABULLVIAL_TAREA_FIELDSET_NOTAS')); ?>
            <div class="row-fluid form-horizontal-desktop">
                <div class="span10">
                    <?php if ($this->item->id > 0): ?>
                        <?php foreach ($this->form->getFieldset('notas') as $field): ?>
                            <?php echo $field->renderField(); ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="icon-info" aria-hidden="true"></i>
                            <?php echo JText::_('COM_SABULLVIAL_TAREA_NOTAS_INFO'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php if ($this->form->getData()->get('id_cotizacion') > 0): ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'cotizacion', JText::_('COM_SABULLVIAL_TAREA_FIELDSET_COTIZACION')); ?>
                <div class="row-fluid form-horizontal-desktop">
                    <div class="span10">
                        <?php echo $this->loadTemplate('cotizacion'); ?>
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

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>
    <input type="hidden" name="task" value="tarea.edit" />
    <input type="hidden" name="subtask" value="" />
    <input type="hidden" name="return" value="<?php echo $input->get('return', null, 'BASE64'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>