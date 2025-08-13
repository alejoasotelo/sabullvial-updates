<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

if (!SabullvialButtonsHelper::canEnviarAFacturacion($cotizacion->id_estadocotizacion)) {
    return;
}

$modalId = 'enviarAFacturacionModal' . $cotizacion->id;
$show = isset($show) ? $show : 'all';
?>

<?php if ($show == 'button' || $show == 'all') : ?>
    <button type="button" data-target="#<?php echo $modalId; ?>" data-toggle="modal" class="btn btn-default btn-small">
        <span class="icon-publish" aria-hidden="true"></span>
        <?php echo JText::_('JACTION_SEND_TO_FACTURACION'); ?>
    </button>
<?php endif; ?>

<?php if ($show == 'modal' || $show == 'all') {
    echo JHtml::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title'       => '<span class="text-warning">' . JText::sprintf('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_TITLE', $cotizacion->id) . '</span>',
            'modalWidth'  => '40',
            'modalHeight' => '800',
            'height' => '800',
            'closeButton' => true,
            'footer' => $this->sublayout('modal_footer', $displayData),
        ],
        $this->sublayout('modal_body', $displayData)
    );
} ?>