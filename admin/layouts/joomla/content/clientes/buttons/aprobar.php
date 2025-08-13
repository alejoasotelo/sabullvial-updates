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

$modalId = 'aprobarModal' . $cliente->id;
$modalTitle = 'COM_SABULLVIAL_CLIENTES_APROBAR_CLIENTE';
$show = isset($show) ? $show : 'all';

if ($show == 'button' || $show == 'all') {
    echo JLayoutHelper::render(
        'joomla.toolbar.modal',
        [
            'selector' => $modalId,
            'text' => JText::_('JACTION_APPROVE'),
            'icon' => 'publish'
        ]
    );
}

if ($show == 'modal' || $show == 'all') {
    JFactory::getDocument()->addScriptDeclaration("
        jQuery(document).ready(function() {
            jQuery('#" . $modalId . "').on('show', function() {
                jQuery('#" . $modalId . " .btn-approve').attr('disabled', 'disabled');
            });
        });
    ");

    echo JHtml::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => JText::sprintf($modalTitle, $cliente->id, JHtml::_('date', $cliente->created, JText::_('DATE_FORMAT_LC5'))),
            'url' => 'index.php?option=com_sabullvial&view=cliente&layout=modal_approve&tmpl=component&id=' . $cliente->id,
            'footer' => $this->sublayout('modal_footer', $displayData),
            'bodyHeight' => 70,
            'modalWidth' => 50,
        ]
    );
}
