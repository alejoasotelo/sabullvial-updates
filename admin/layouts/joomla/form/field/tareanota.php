<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   string   $class           Classes for the input.
 * @var   string   $description     Description of the field.
 * @var   boolean  $disabled        Is this field disabled?
 * @var   string   $group           Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden          Is this field hidden in the form?
 * @var   string   $hint            Placeholder for the field.
 * @var   string   $id              DOM id of the field.
 * @var   string   $label           Label of the field.
 * @var   string   $labelclass      Classes to apply to the label.
 * @var   boolean  $multiple        Does this field support multiple values?
 * @var   string   $name            Name of the input field.
 * @var   string   $onchange        Onchange attribute for the field.
 * @var   string   $onclick         Onclick attribute for the field.
 * @var   string   $pattern         Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly        Is this field read only?
 * @var   boolean  $repeat          Allows extensions to duplicate elements.
 * @var   boolean  $required        Is this field required?
 * @var   integer  $size            Size attribute of the input.
 * @var   boolean  $spellcheck      Spellcheck state for the form field.
 * @var   string   $validate        Validation rules to apply.
 * @var   string   $value           Value attribute of the field.
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $inputType       Options available for this field.
 * @var   string   $accept          File types that are accepted.
 */

// Including fallback code for HTML5 non supported browsers.
JHtml::_('jquery.framework');
JHtml::_('script', 'system/html5fallback.js', array('version' => 'auto', 'relative' => true, 'conditional' => 'lt IE 9'));

$idBtnAdd = $id . '_btn';
$idWrapper = $id . '_wrapper';
$chatBubbleTemplate = $this->sublayout('chat_bubble', [
    'id' => 'tareanota-nota-{{id}}',
    'author' => '{{author}}',
    'text' => '{{text}}',
    'created' => '{{created}}'
]);

Factory::getDocument()->addScriptOptions('com_sabullvial.tareanota', [
    'url' => JUri::base(),
    'token' => JSession::getFormToken(),
    'itemId' => $itemId,
    'idWrapper' => $idWrapper,
    'idTextarea' => $id,
    'idBtnAdd' => $idBtnAdd,
    'chatBubbleTemplate' => $chatBubbleTemplate
]);

HTMLHelper::script('com_sabullvial/fields/tareanota.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::stylesheet('com_sabullvial/fields/tareanota.css', ['version' => 'auto', 'relative' => true]);

// Initialize some field attributes.
$autocomplete = !$autocomplete ? 'autocomplete="off"' : 'autocomplete="' . $autocomplete . '"';
$autocomplete = $autocomplete == 'autocomplete="on"' ? '' : $autocomplete;

$attributes = array(
    $columns ?: '60',
    $rows ?: '3',
    !empty($class) ? 'class="' . $class . ' span12"' : 'class="span12"',
    strlen($hint) ? 'placeholder="' . htmlspecialchars($hint, ENT_COMPAT, 'UTF-8') . '"' : '',
    $disabled ? 'disabled' : '',
    $readonly ? 'readonly' : '',
    $onchange ? 'onchange="' . $onchange . '"' : '',
    $onclick ? 'onclick="' . $onclick . '"' : '',
    $required ? 'required aria-required="true"' : '',
    $autocomplete,
    $autofocus ? 'autofocus' : '',
    $spellcheck ? '' : 'spellcheck="false"',
    $maxlength ? $maxlength: ''
);
?>
<div id="<?php echo $idWrapper; ?>" class="tareanota-wrapper">
    <div class="tareanota-input-container">
        <textarea name="<?php
        echo $name; ?>" id="<?php
        echo $id; ?>" <?php
        echo implode(' ', $attributes); ?> ><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8'); ?></textarea>

        <button class="btn btn-info pull-right" type="button" id="<?php echo $idBtnAdd; ?>">
            <?php echo Text::_('COM_SABULLVIAL_TAREA_NOTA_AGREGAR'); ?>
        </button>
    </div>

    <div class="tareanota-notas-container">
        <div class="tareanota-notas-title">
            <span class="icon icon-comments-2"></span> <?php echo Text::_('COM_SABULLVIAL_TAREA_NOTA_HISTORIAL'); ?>
        </div>

        <div class="tareanota-notas-chat-bubbles">
            <?php foreach ($notas as $nota): ?>
                <?php echo $this->sublayout('chat_bubble', [
                    'id' => 'tareanota-nota-' . $nota->id,
                    'author' => $nota->author,
                    'text' => nl2br($nota->body),
                    'created' => $nota->created
                ]); ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>