<?php

/**
 * Layout file for the footer component of the modal showing the batch options
 */
defined('_JEXEC') or die;
?>
<a class="btn" type="button" data-dismiss="modal" onclick="setReviewData([])">
    <?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success btnReviewButton" type="submit" disabled onclick="Joomla.submitbutton('cotizacion.review');">
    <?php echo JText::_('JAPPLY'); ?>
</button>