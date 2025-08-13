<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>
<button type="button" class="btn" onclick="document.getElementById('batch-upload-images-action').value='';document.getElementById('batch-upload-images-images').value=null;" data-dismiss="modal"><?php echo JText::_('JACTION_CLOSE'); ?></button>
<button type="submit" class="btn btn-success" onclick="Joomla.submitbutton('producto.batch');this.disabled=true;return false;">
	<?php echo JText::_('JTOOLBAR_SUBIR_IMAGENES'); ?>
</button>