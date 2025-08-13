<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\HTML\HTMLHelper;

extract($displayData);

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
 *
 * @var   string   $clienteName        The cliente name
 * @var   mixed    $groups          The filtering groups (null means no filtering)
 * @var   mixed    $excluded        The clientes to exclude from the list of clientes
 */

if (!$readonly) {
    HTMLHelper::script('com_sabullvial/fieldclienteortext.js', ['version' => 'auto', 'relative' => true]);
}

JText::script('COM_SABULLVIAL_CONSUMIDOR_FINAL');

JFactory::getDocument()->addStyleDeclaration('.field-clienteortext-desc{margin-top: 8px;}');

$uri = new JUri('index.php?option=com_sabullvial&view=clientestango&layout=modal&tmpl=component&required=0&field={field-clienteortext-id}&ismoo=0');

if ($required) {
    $uri->setVar('required', 1);
}

if (!empty($groups)) {
    $uri->setVar('groups', base64_encode(json_encode($groups)));
}

if (!empty($excluded)) {
    $uri->setVar('excluded', base64_encode(json_encode($excluded)));
}

// Invalidate the input value if no cliente selected
if ($this->escape($clienteName) === JText::_('COM_SABULLVIAL_COTIZACION_FIELD_SELECCIONAR_CLIENTE')) {
    $clienteName = '';
}

$inputAttributes = [
    'type' => 'text',
    'id' => $id,
    'name' => $fieldCliente,
    'class' => 'field-clienteortext-input-name',
    'value' => isset($fieldClienteValue) && !empty($fieldClienteValue) ? $this->escape($fieldClienteValue) : $this->escape($clienteName)
];

if ($class) {
    $inputAttributes['class'] .= ' ' . $class;
}

if ($size) {
    $inputAttributes['size'] = (int) $size;
}

if ($required) {
    $inputAttributes['required'] = 'required';
}

if (!$readonly) {
    $inputAttributes['placeholder'] = JText::_('COM_SABULLVIAL_COTIZACION_FIELD_SELECCIONAR_CLIENTE');
}

?>
<div class="field-clienteortext-wrapper"
	 data-url="<?php echo (string) $uri; ?>"
	 data-modal=".modal"
	 data-modal-width="100%"
	 data-modal-height="400px"
	 data-input=".field-clienteortext-input"
	 data-input-name=".field-clienteortext-input-name"
	 data-cuit=".field-clienteortext-cuit"
	 data-codigo_vendedor=".field-clienteortext-codigo_vendedor"
	 data-cod=".field-clienteortext-cod"
	 data-saldo=".field-clienteortext-saldo"
	 data-button-select=".button-select"
	 data-button-consumidor-final=".button-consumidor-final">
	<div class="input-append">
		<input <?php echo ArrayHelper::toString($inputAttributes); ?> />
		<?php if (!$readonly) : ?>
			<button
				type="button"
				class="btn btn-primary button-select hasTooltip"
				data-title="<?php echo JText::_('COM_SABULLVIAL_COTIZACION_FIELD_SELECCIONAR_CLIENTE'); ?>"
				aria-label="<?php echo JText::_('COM_SABULLVIAL_COTIZACION_FIELD_SELECCIONAR_CLIENTE'); ?>"
				>
				<span class="icon-user" aria-hidden="true"></span>
			</button>
			<?php if (!$hideConsumidorfinal): ?>
				<button type="button" class="btn hasTooltip button-consumidor-final" aria-label="Limpiar" data-title="Consumidor Final">
					C.F.
				</button>
			<?php endif; ?>
			<button type="button" class="btn hasTooltip button-clear" aria-label="Limpiar" data-title="Limpiar">
				<span class="icon-remove" aria-hidden="true"></span>
			</button>
			<?php echo JHtml::_(
			    'bootstrap.renderModal',
			    'clienteModal_' . $id,
			    [
			                    'title'       => JText::_('COM_SABULLVIAL_COTIZACION_FIELD_SELECCIONAR_CLIENTE'),
			                    'modalWidth'  => '50',
			                    'closeButton' => true,
			                    'footer'      => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JCANCEL') . '</button>',
			                ]
			); ?>
		<?php endif; ?>
	</div>
	<?php if (!$hideDesc): ?>
		<p class="muted field-clienteortext-desc">
			Cliente: <span class="field-clienteortext-cod"><?php echo $clienteName; ?></span><br/>
			Cod: <span class="field-clienteortext-cod"><?php echo $cod; ?></span> |
			Cuit: <span class="field-clienteortext-cuit"><?php echo $cuit; ?></span> |
			CV: <span class="field-clienteortext-codigo_vendedor"><?php echo $codigo_vendedor; ?></span> |
			Saldo: <span class="field-clienteortext-saldo"><?php echo JText::sprintf('COM_SABULLVIAL_PESO_FORMAT', $saldo); ?></span> |
			Prom.: <span class="field-clienteortext-saldo"><?php echo $promedio; ?></span>
			<?php if (!$activo): ?>
				<br/>
				<span class="text-error">Inhabilitado</span>
			<?php endif; ?>
		</p>
	<?php endif ;?>
	<?php if (!$readonly) : ?>
		<input type="hidden" id="<?php echo $id; ?>_id" name="<?php echo $name; ?>" value="<?php echo $value; ?>" class="field-clienteortext-input<?php echo $class ? ' ' . $class : ''; ?>" data-onchange="<?php echo $this->escape($onchange); ?>" />
	<?php endif; ?>
</div>
