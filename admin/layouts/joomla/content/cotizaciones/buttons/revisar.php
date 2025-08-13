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

if (!SabullvialButtonsHelper::canRevisar($cotizacion)) {
    return;
}

$modalReviewId = 'reviewModal' . $cotizacion->id;
$show = isset($show) ? $show : 'all';

if ($show == 'button' || $show == 'all') {
    echo JLayoutHelper::render(
        'joomla.toolbar.modal',
        [
            'selector' => $modalReviewId,
            'text' => JText::_('JACTION_REVIEW'),
            'icon' => 'list',
            'class' => 'btn-review-' . $cotizacion->id,
        ]
    );
}

if ($show == 'modal' || $show == 'all') {
    JFactory::getDocument()->addScriptDeclaration("
        jQuery(document).ready(function() {
            jQuery('#" . $modalReviewId . "').on('hide', function() {
                window.setCotizacionId(0);
                jQuery('#" . $modalReviewId . " .btnReviewButton').attr('disabled', 'disabled');
            });
        });
    ");

    echo JHtml::_(
        'bootstrap.renderModal',
        $modalReviewId,
        [
            'title' => JText::sprintf('COM_SABULLVIAL_COTIZACIONES_REVISAR_PEDIDO', $cotizacion->id),
            'url' => 'index.php?option=com_sabullvial&view=cotizacion&layout=modal_review&tmpl=component&id=' . $cotizacion->id,
            'footer' => $this->sublayout('modal_footer', $displayData),
            'bodyHeight' => 70,
            'modalWidth' => 70,
        ]
    );
}
