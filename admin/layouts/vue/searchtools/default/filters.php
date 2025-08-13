<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$data = $displayData;

if (is_array($data['options']))
{
    $data['options'] = new Registry($data['options']);
}

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
$filtersVModels = $data['options']->get('vModelFilters', []);
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php if ($fieldName !== 'filter_search') : ?>
			<?php $dataShowOn = ''; ?>
			<?php if ($field->showon) : ?>
				<?php JHtml::_('jquery.framework'); ?>
				<?php JHtml::_('script', 'jui/cms.js', ['version' => 'auto', 'relative' => true]); ?>
				<?php $dataShowOn = " data-showon='" . json_encode(JFormHelper::parseShowOnConditions($field->showon, $field->formControl, $field->group)) . "'"; ?>
			<?php endif; ?>
			<div class="js-stools-field-filter"<?php echo $dataShowOn; ?>>
				<?php if (isset($filtersVModels->{$field->fieldname})): ?>
					<?php 
						$vObject = $filtersVModels->{$field->fieldname};
						$vAttribs = 'v-model="'.$vObject->vModel.'" @change="'.$vObject->onChange.'"';
						$input = str_replace('id="', $vAttribs . ' id="', $field->input); 
						echo $input;
					?>
				<?php else: ?>
					<?php echo $field->input; ?>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
