<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');
// JHtml::_('formbehavior.chosen', '#jform_catid', null, array('disable_search_threshold' => 0));
// JHtml::_('formbehavior.chosen', '#jform_tags', null, array('placeholder_text_multiple' => JText::_('JGLOBAL_TYPE_OR_SELECT_SOME_TAGS')));
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration("
    Joomla.submitbutton = function(task)
    {
        if (task == 'cliente.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
        {
            Joomla.submitform(task);
        }
    }
");

$params = $this->params;
$input = JFactory::getApplication()->input;
$codigoVendedor = $input->get('codigo_vendedor', '');
$idVendedor = 0;
if (!empty($codigoVendedor)) {
    $db = JFactory::getDbo();
    $query = $db->getQuery(true)
        ->select('item_id userId')
        ->from('#__fields_values')
        ->where('field_id IN (SELECT id FROM `#__fields` where name = ' . $db->quote('codigo-vendedor') . ')')
        ->where('`value` = ' . $db->quote($codigoVendedor));
    $db->setQuery($query);
    $idVendedor = (int)$db->loadResult();
    // if (empty($idVendedor)) {
    // 	$app = JFactory::getApplication();
    // 	$app->enqueueMessage('El código de vendedor no existe', 'error');
    // }
}

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
    'captcha'
];
?>
<div class="edit item-page<?php echo $this->pageclass_sfx; ?>">
    <?php if ($params->get('show_page_heading')) : ?>
    <div class="page-header">
        <h1>
            <?php echo $this->escape($params->get('page_heading')); ?>
        </h1>
    </div>
    <?php endif; ?>

    <form action="<?php echo JRoute::_('index.php?option=com_sabullvial&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate form-vertical">
        <fieldset>
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

                    <?php if ($this->captchaEnabled) : ?>
                        <?php echo $this->form->renderField('captcha'); ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($idVendedor > 0) : ?>
                <input type="hidden" name="jform[created_by]" value="<?php echo $idVendedor; ?>" />
                <input type="hidden" name="jform[created_by_alias]" value="<?php echo JFactory::getUser($idVendedor)->username; ?>" />
            <?php endif; ?>

            <input type="hidden" name="task" value="" />
            <?php echo JHtml::_('form.token'); ?>
        </fieldset>
        <div class="btn-toolbar">
            <div class="btn-group">
                <button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('cliente.save')">
                    <span class="icon-ok"></span><?php echo JText::_('JSAVE') ?>
                </button>
            </div>
        </div>
    </form>
</div>
