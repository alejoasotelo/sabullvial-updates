<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$doTask = $displayData['doTask'];
$class  = $displayData['class'];
$text   = $displayData['text'];
$title = $displayData['title'];
$target   = isset($displayData['target']) ? $displayData['target'] : '';
$onClick = empty($target) ? "location.href='$doTask'" : "window.open('$doTask', '$target')";

?>
<button onclick="<?php echo $onClick; ?>" class="btn btn-small" <?php echo !empty($title) ? 'title="' . $title . '"' : ''; ?>>
	<span class="<?php echo $class; ?>" aria-hidden="true"></span>
	<?php echo $text; ?>
</button>
