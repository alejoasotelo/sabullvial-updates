<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Make thing clear
 *
 * @var JForm   $form       The form instance for render the section
 * @var string  $basegroup  The base group name
 * @var string  $group      Current group name
 * @var array   $buttonsRow    Array of the buttons that will be rendered
 */
extract($displayData);

$fieldIdProducto = $form->getField('id_producto');
?>

<tr	class="subform-repeatable-group subform-repeatable-group-<?php echo $unique_subform_id; ?>">
	<?php foreach ($form->getGroup('') as $field) : ?>
		<?php $classHeader = $field->getAttribute('class_header', ''); ?>
		<td 
			data-fieldname="<?php echo strip_tags($field->fieldname); ?>" 
			<?php echo $field->fieldname == 'id_producto' ? 'data-value="'.strip_tags($field->value).'"' : ''; ?> 
			data-column="<?php echo strip_tags($field->label); ?>"
			<?php echo !empty($classHeader) ? 'class="'.$classHeader.'"' : ''; ?>
			<?php echo strtolower($field->type) == 'hidden' ? 'style="display: none;"' : ''; ?>
		>
			<?php echo $field->renderField(['hiddenLabel' => true]); ?>
		</td>
	<?php endforeach; ?>
</tr>
