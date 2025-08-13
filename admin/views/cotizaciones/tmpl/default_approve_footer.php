<?php

/**
 * Layout file for the footer component of the modal showing the batch options
 */
defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_sabullvial/controllers/cotizaciones.php';
require_once JPATH_ADMINISTRATOR . '/components/com_sabullvial/tables/estadocotizacion.php';

?>
<a class="btn" type="button" data-dismiss="modal">
    <?php echo JText::_('JCANCEL'); ?>
</a>

<?php if ($this->row->is_reviewed && !$this->row->estadocotizacion_aprobado) : ?>
    <?php
        $action = 'aprobado';
    if ($this->row->estadocotizacion_revisado == SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA) {
        $action = SabullvialControllerCotizaciones::ESTADO_COTIZACION_APROBADO_COMPLETO;
    } elseif ($this->row->estadocotizacion_revisado == SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA_CON_FALTANTES) {
        $action = SabullvialControllerCotizaciones::ESTADO_COTIZACION_APROBADO_CON_FALTANTES;
    }
?>

    <button type="button" class="btn btn-default" onclick="return listItemTask('cb<?php echo $this->row->i; ?>', 'cotizaciones.<?php echo $action; ?>');">
        <span class="icon-publish" aria-hidden="true"></span>
        <?php echo JText::_('JACTION_APPROVE'); ?>
    </button>
<?php endif; ?>

<?php if (!$this->row->estadocotizacion_rechazado) : ?>
    <button type="button" class="btn btn-default" onclick="return listItemTask('cb<?php echo $this->row->i; ?>', 'cotizaciones.rechazado');">
        <span class="icon-unpublish" aria-hidden="true"></span>
        <?php echo JText::_('JACTION_REJECT') ?>
    </button>
<?php endif; ?>