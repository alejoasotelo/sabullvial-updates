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


if (!SabullvialButtonsHelper::canVer($cotizacion->id_estadocotizacion)) {
    return;
}

$modalId = 'verModal' . $cotizacion->id;
$show = isset($show) ? $show : 'all';

if ($show == 'button' || $show == 'all') {
    echo JLayoutHelper::render(
        'joomla.toolbar.modal',
        [
            'selector' => $modalId,
            'text' => JText::_('JACTION_VIEW'),
            'icon' => 'search'
        ]
    );
}

if ($show == 'modal' || $show == 'all') {
    echo JHtml::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => JText::sprintf('COM_SABULLVIAL_COTIZACIONES_VER_PEDIDO', $cotizacion->id, JHtml::_('date', $cotizacion->created, JText::_('DATE_FORMAT_LC5'))),
            'url' => 'index.php?option=com_sabullvial&view=cotizacion&layout=modal_approve&tmpl=component&id=' . $cotizacion->id,
            'bodyHeight' => 70,
            'modalWidth' => 80,
            'footer' => $this->sublayout('modal_footer', $displayData),
        ]
    );
}
