<?php
/**
 * Layout file for the footer component of the modal showing the batch options
 */
defined('_JEXEC') or die;

?>
<a class="btn" type="button" data-dismiss="modal" :disabled="loadingModal">
	<?php echo JText::_('JCANCEL'); ?>
</a>
<button class="btn btn-success" type="button" @click="saveGenerateRouteSheet" :disabled="loadingModal">
	<template v-if="loadingModal"><?php echo JText::_('JTOOLBAR_GENERANDO_HOJA_DE_RUTA'); ?></template>
	<template v-else><?php echo JText::_('JTOOLBAR_GENERAR_HOJA_DE_RUTA'); ?></template>
</button>