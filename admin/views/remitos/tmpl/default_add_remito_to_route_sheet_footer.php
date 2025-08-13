<?php
/**
 * Layout file for the footer component of the modal showing the batch options
 */
defined('_JEXEC') or die;
?>
<a type="button" class="btn" data-dismiss="modal" :disabled="loadingModal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button type="button" class="btn btn-success validate" @click="saveAddRemitosToRouteSheet" :disabled="loadingModal">	
	<template v-if="loadingModal"><?php echo JText::_('JTOOLBAR_AGREGANDO_REMITO_A_HOJA_DE_RUTA'); ?></template>
	<template v-else><?php echo JText::_('JTOOLBAR_AGREGAR_REMITO_A_HOJA_DE_RUTA'); ?></template>
</button>