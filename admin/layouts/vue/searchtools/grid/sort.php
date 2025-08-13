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

$data = new Registry($displayData);

$title = htmlspecialchars(JText::_($data->get('title', '')));

$onClick = $data->get('onClick', '');
$onClick = !empty($onClick) ? 'v-on:click="' . $onClick . '"' : '';

JHtml::_('bootstrap.popover');
?>
<a href="#" <?php echo $onClick; ?> class="hasPopover"
   title="<?php echo $title; ?>"
   data-content="<?php echo htmlspecialchars(JText::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN')); ?>"
   data-placement="top">
   
   <?php if (!empty($data->get('icon', ''))) : ?>
      <span class="<?php echo $data->get('icon', ''); ?>"></span>
   <?php endif; ?>
   
   <?php if (!empty($data->get('title', ''))) : ?>
      <?php echo JText::_($data->get('title', '')); ?>
   <?php endif; ?>

   <template v-if="'<?php echo $data->get('order', ''); ?>' == <?php echo $data->get('orderSelected', ''); ?>">
      <span :class="{'icon-arrow-up-3': <?php echo $data->get('direction', ''); ?> == 'ASC', 'icon-arrow-down-3': <?php echo $data->get('direction', ''); ?> == 'DESC'}"></span>
   </template>
</a>
