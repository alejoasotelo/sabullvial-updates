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

if (!SabullvialButtonsHelper::canAprobarORechazar($cotizacion->id_estadocotizacion, (int) $cotizacion->created_by)) {
    return;
}

$isAprobarAndRechazar = $cotizacion->is_reviewed && !$cotizacion->was_aprobado && !$cotizacion->has_rechazado && !$cotizacion->estadocotizacion_cancelado;
$isRechazar = !$cotizacion->is_reviewed && !$cotizacion->estadocotizacion_rechazado && !$cotizacion->estadocotizacion_cancelado;

$modalId = 'aprobarModal' . $cotizacion->id;
$modalTitle = $isAprobarAndRechazar ? 'COM_SABULLVIAL_COTIZACIONES_APROBAR_O_RECHAZAR_PEDIDO' : 'COM_SABULLVIAL_COTIZACIONES_RECHAZAR_PEDIDO';
$show = isset($show) ? $show : 'all';

if ($show == 'button' || $show == 'all') {
    // Si esta revisado mostrar.
    // Pero si ya fue aprobado, rechazado o cancelado no mostrar.
    if ($isAprobarAndRechazar) {
        echo JLayoutHelper::render(
            'joomla.toolbar.modal',
            [
                'selector' => $modalId,
                'text' => JText::_('JACTION_APPROVE') . ' / <span class="icon-unpublish" aria-hidden="true"></span>' . JText::_('JACTION_REJECT'),
                'icon' => 'publish'
            ]
        );
    }

    if ($isRechazar) {
        echo JLayoutHelper::render(
            'joomla.toolbar.modal',
            [
                'selector' => $modalId,
                'text' => JText::_('JACTION_REJECT'),
                'icon' => 'unpublish'
            ]
        );
    }
}

if ($show == 'modal' || $show == 'all') {
    echo JHtml::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => JText::sprintf($modalTitle, $cotizacion->id, JHtml::_('date', $cotizacion->created, JText::_('DATE_FORMAT_LC5'))),
            'url' => 'index.php?option=com_sabullvial&view=cotizacion&layout=modal_approve&tmpl=component&id=' . $cotizacion->id,
            'footer' => $this->sublayout('modal_footer', $displayData),
            'bodyHeight' => 70,
            'modalWidth' => 80,
        ]
    );
}
