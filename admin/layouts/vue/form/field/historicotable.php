<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

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
 * @var   array    $checkedOptions  Options that will be set as checked.
 * @var   boolean  $hasValue        Has this field a value assigned?
 * @var   array    $options         Options available for this field.
 * @var   array    $inputType       Options available for this field.
 * @var   string   $accept          File types that are accepted.
 */

$attributes = [
    'class="' . (!empty($class) ? $class : '') . '"',
    !empty($style) ? 'style="'.$style.'"' : ''
];
?>
<div <?php echo implode(' ', $attributes); ?>>
	<table name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="table table-hover table-striped table-bordered">
		<thead>
			<tr>
				<th><?php echo Text::_('COM_SABULLVIAL_FIELD_COTIZACIONHISTORICOTABLE_ESTADO'); ?></th>
				<th><?php echo Text::_('COM_SABULLVIAL_FIELD_COTIZACIONHISTORICOTABLE_FECHA'); ?></th>
				<th><?php echo Text::_('COM_SABULLVIAL_FIELD_COTIZACIONHISTORICOTABLE_VENDEDOR'); ?></th>
			</tr>
		</thead>
		<tbody>
			<tr v-for="option in <?php echo $options; ?>" :key="option.id">
				<td>
					<span class="label" :style="'background-color:' + option.bg_color + '; color: ' + option.color">
						{{ option.estado }}
						
						<span v-if="(option.entregado || option.entregado_mostrador) && <?php echo $deliveryDate; ?>">
							el {{ <?php echo $deliveryDateFormated; ?> }}
						</span>
					</span>

					<a v-if="option.image" :href="'<?php echo JUri::root(); ?>' + option.image" target="_blank" class="btn btn-default btn-mini btn-img-remito">
						<i class="icon-picture"></i> <?php echo Text::_('COM_SABULLVIAL_FIELD_COTIZACIONHISTORICOTABLE_VER_FOTO_DEL_REMITO'); ?>
					</a>

					<?php if ($deleteImage): ?>
						&nbsp;<button v-if="option.image" type="button" class="btn btn-danger btn-mini btn-delete-image" <?php echo $onDeleteImage ? '@click="'.$onDeleteImage.'(option.id)"': ''; ?>>
							<i class="icon-remove"></i> <?php echo Text::_('COM_SABULLVIAL_FIELD_COTIZACIONHISTORICOTABLE_DELETE_FOTO_DEL_REMITO'); ?>
						</button>
					<?php endif; ?>
				</td>
				<td>
					<span v-if="option.created">{{ formatDatetime(option.created) }}</span>
					<span v-else>-</span>
				</td>
				<td>{{ option.created_by_alias  }}</td>
			</tr>
		</tbody>
	</table>
</div>