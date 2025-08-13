<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : [];

if (is_array($data['options'])) {
    $data['options'] = new Registry($data['options']);
}

$onSelectCliente = $data['options']->get('onSelectCliente', '');

if (!empty($onSelectCliente)) {
    $onSelectCliente = '@click="' . $onSelectCliente . '(modalCliente)"';
}
?>
<button type="button" class="btn" data-dismiss="modal">
    <?php echo JText::_('JCANCEL'); ?>
</button>

<button type="button" class="btn btn-success" <?php echo $onSelectCliente; ?>>
    <?php echo JText::_('JSELECT'); ?>
</button>
