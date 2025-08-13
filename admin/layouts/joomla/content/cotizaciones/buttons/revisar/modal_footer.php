<?php

/**
 * Layout file for the footer component of the modal showing the batch options
 */
defined('_JEXEC') or die;

extract($displayData);

?>
<a class="btn" type="button" data-dismiss="modal" onclick="setReviewData([])">
    <?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success btnReviewButton" type="submit" disabled onclick="if(this.disabled) {return false}Joomla.submitbutton('cotizacion.review'); this.disabled = true; this.innerText='<?php echo JText::_('JACTION_SAVING'); ?>...';jQuery('.btn-review-<?php echo $cotizacion->id;?>').attr('disabled', 'disabled')">
    <?php echo JText::_('JAPPLY'); ?>
</button>