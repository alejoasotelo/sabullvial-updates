<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.core');

$title = $displayData['title'];
$class = $displayData['class'];
$value = $displayData['value'];
?>
<span class="btn btn-small <?php echo $class;?>">
	<?php echo $title; ?>
	<span class="value" aria-hidden="true"><?php echo isset($value) ? $value : '$0'; ?></span>
</span>
