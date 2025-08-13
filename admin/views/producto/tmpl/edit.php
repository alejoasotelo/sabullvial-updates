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
<form action="<?php echo JRoute::_('index.php?option=com_sabullvial&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm">
    <div class="form-horizontal">
        <fieldset class="adminform">
            <legend><?php echo JText::_('COM_SABULLVIAL_PRODUCTO_DETAILS'); ?></legend>
            <div class="row-fluid">
                <div class="span6">
                    <?php
                        foreach ($this->form->getFieldset() as $field) {
                            echo $field->renderField();
                        }
?>
                </div>
            </div>
        </fieldset>
    </div>
    <input type="hidden" name="task" value="producto.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>