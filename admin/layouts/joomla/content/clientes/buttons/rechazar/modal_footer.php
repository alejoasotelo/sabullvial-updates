<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

?>
<a class="btn" type="button" data-dismiss="modal">
    <?php echo JText::_('JCANCEL'); ?>
</a>

<button type="button" class="btn btn-default" onclick="return listItemTask('cb<?php echo $index; ?>', 'clientes.rechazado');">
    <span class="icon-unpublish" aria-hidden="true"></span>
    <?php echo JText::_('JACTION_REJECT'); ?>
</button>