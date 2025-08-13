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

require_once JPATH_ADMINISTRATOR . '/components/com_sabullvial/controllers/cotizaciones.php';
require_once JPATH_ADMINISTRATOR . '/components/com_sabullvial/tables/estadocotizacion.php';

?>
<a class="btn" type="button" data-dismiss="modal">
    <?php echo JText::_('JCANCEL'); ?>
</a>

<?php if ($cotizacion->is_reviewed && !$cotizacion->estadocotizacion_aprobado) : ?>
    <?php
        $action = 'aprobado';
    if ($cotizacion->estadocotizacion_revisado == SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA) {
        $action = SabullvialControllerCotizaciones::ESTADO_COTIZACION_APROBADO_COMPLETO;
    } elseif ($cotizacion->estadocotizacion_revisado == SabullvialTableEstadoCotizacion::ESTADO_REVISION_COMPLETA_CON_FALTANTES) {
        $action = SabullvialControllerCotizaciones::ESTADO_COTIZACION_APROBADO_CON_FALTANTES;
    }
?>

    <button type="button" class="btn btn-default" onclick="return listItemTask('cb<?php echo $index; ?>', 'cotizaciones.<?php echo $action; ?>');">
        <span class="icon-publish" aria-hidden="true"></span>
        <?php echo JText::_('JACTION_APPROVE'); ?>
    </button>
<?php endif; ?>

<?php if (!$cotizacion->estadocotizacion_rechazado) : ?>
    <button type="button" class="btn btn-default" onclick="return listItemTask('cb<?php echo $index; ?>', 'cotizaciones.rechazado');">
        <span class="icon-unpublish" aria-hidden="true"></span>
        <?php echo JText::_('JACTION_REJECT') ?>
    </button>
<?php endif; ?>