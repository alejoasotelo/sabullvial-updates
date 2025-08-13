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

if (!SabullvialButtonsHelper::canAprobarORechazarCliente($cliente->id_estadocliente)) {
    return;
}

$modalId = 'rechazarModal' . $cliente->id;
$modalTitle = 'COM_SABULLVIAL_CLIENTES_RECHAZAR_CLIENTE';
$show = isset($show) ? $show : 'all';

if ($show == 'button' || $show == 'all') {
    echo JLayoutHelper::render(
        'joomla.toolbar.modal',
        [
            'selector' => $modalId,
            'text' => JText::_('JACTION_REJECT'),
            'icon' => 'unpublish'
        ]
    );
}

if ($show == 'modal' || $show == 'all') {
    echo JHtml::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => JText::sprintf($modalTitle, $cliente->id, JHtml::_('date', $cliente->created, JText::_('DATE_FORMAT_LC5'))),
            'url' => 'index.php?option=com_sabullvial&view=cliente&layout=modal_reject&tmpl=component&id=' . $cliente->id,
            'footer' => $this->sublayout('modal_footer', $displayData),
            'bodyHeight' => 70,
            'modalWidth' => 50,
        ]
    );
}
