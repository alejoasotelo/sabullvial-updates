<?php
/**
 * Layout file for the footer component of the modal showing the batch options
 */
defined('_JEXEC') or die;
?>
<a type="button" class="btn" onclick="document.getElementById('delivered_by_counter_delivery_date').value='';" data-dismiss="modal" :disabled="loadingModal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button type="button" class="btn btn-success validate" @click="saveDeliveredByCounter" :disabled="loadingModal">
	<template v-if="loadingModal"><?php echo JText::_('JTOOLBAR_MARCANDO_COMO_ENTREGADO'); ?></template>
	<template v-else><?php echo JText::_('JTOOLBAR_MARCAR_COMO_ENTREGADO'); ?></template>
</button>