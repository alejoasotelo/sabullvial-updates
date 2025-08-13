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

$title = isset($displayData['title']) ? $displayData['title'] : '';
$onclick = isset($displayData['onclick']) ? $displayData['onclick'] : '';
$icon = isset($displayData['icon']) ? $displayData['icon'] : '';
$data = isset($displayData['data']) ? $displayData['data'] : [];
$class = isset($displayData['class']) ? $displayData['class'] : '';
$attribs = isset($displayData['attribs']) && is_array($displayData['attribs']) ? $displayData['attribs'] : [];

$attribData = '';
if (count($data)) {
    foreach ($data as $key => $value) {
        $attribData .= ' data-' . $key . '="' . $value . '"';
    }
}

if (count($attribs)) {
	foreach ($attribs as $key => $value) {
		$attribData .= ' ' . $key . '="' . $value . '"';
	}
}

JText::script('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST');
?>
<button type="button" <?php echo !empty($attribData) ? $attribData : ''; ?> @click="<?php echo $onclick; ?>" class="btn btn-small <?php echo $class; ?>">
	<?php if (!empty($icon)): ?>
		<span class="<?php echo $icon; ?>" aria-hidden="true"></span>
	<?php endif; ?>
	<?php echo $title; ?>
</button>
