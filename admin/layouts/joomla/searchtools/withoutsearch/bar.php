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

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : array();

if (is_array($data['options']))
{
	$data['options'] = new Registry($data['options']);
}

// Options
$filterButton = $data['options']->get('filterButton', true);
$searchButton = $data['options']->get('searchButton', true);

$filters = $data['view']->filterForm->getGroup('filter');

JFactory::getDocument()->addStyleDeclaration('
	.js-stools-container-filters.js-stools-container-filters-visible {
		display: inline-block !important;
	}
');
?>

<?php if (!empty($filters['filter_search']) && $searchButton) : ?>
	<label for="filter_search" class="element-invisible">
		<?php if (isset($filters['filter_search']->label)) : ?>
			<?php echo JText::_($filters['filter_search']->label); ?>
		<?php else : ?>
			<?php echo JText::_('JSEARCH_FILTER'); ?>
		<?php endif; ?>
	</label>
	<div class="btn-wrapper input-append">
		<?php echo $filters['filter_search']->input; ?>
		<?php if ($filters['filter_search']->description) : ?>
			<?php JHtml::_('bootstrap.tooltip', '#filter_search', array('title' => JText::_($filters['filter_search']->description))); ?>
		<?php endif; ?>
		<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_SUBMIT'); ?>" aria-label="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>">
			<span class="icon-search" aria-hidden="true"></span>
		</button>
	</div>
<?php endif; ?>
<div class="js-stools-container-filters js-stools-container-filters-visible">
	<?php echo $this->sublayout('filters', $data); ?>
</div>
<div class="btn-wrapper">
	<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo JHtml::_('tooltipText', 'JSEARCH_FILTER_CLEAR'); ?>">
		<?php echo JText::_('JSEARCH_FILTER_CLEAR');?>
	</button>
</div>
