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

$esperarPagos = filter_var($cotizacion->esperar_pagos, FILTER_VALIDATE_BOOLEAN);

if (!SabullvialButtonsHelper::canAprobarPago($esperarPagos, $cotizacion->is_esperar_pagos_pagado)) {
    return;
}

$modalId = 'aprobarPagoModal' . $cotizacion->id;
$modalTitle = 'COM_SABULLVIAL_COTIZACIONES_APROBAR_PAGO_MODAL_TITLE';
$show = isset($show) ? $show : 'all';

if ($show == 'button' || $show == 'all') {
    echo JLayoutHelper::render(
            'joomla.toolbar.modal',
            [
                'selector' => $modalId,
                'text' => JText::_('JACTION_APPROVE_PAGO'),
                'icon' => 'publish'
            ]
        );
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
