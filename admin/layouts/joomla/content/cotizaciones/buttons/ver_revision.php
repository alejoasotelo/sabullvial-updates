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


if (!SabullvialButtonsHelper::canVerRevision($cotizacion)) {
    return;
}

$modalId = 'viewReviewModal' . $cotizacion->id;
$show = isset($show) ? $show : 'all';

if ($show == 'button' || $show == 'all') {
    echo JLayoutHelper::render(
        'joomla.toolbar.modal',
        [
            'selector' => $modalId,
            'text' => JText::_('JACTION_VIEW_REVIEW'),
            'icon' => 'list'
        ]
    );
}

if ($show == 'modal' || $show == 'all') {
    JFactory::getDocument()->addScriptDeclaration("
        jQuery(document).ready(function() {
            jQuery('#".$modalId."').on('hide', function() {
                window.setReviewCotizacionId(0);
            });
        });
    ");

    echo JHtml::_(
        'bootstrap.renderModal',
        $modalId,
        [
            'title' => JText::sprintf('COM_SABULLVIAL_COTIZACIONES_VER_REVISION', $cotizacion->id),
            'url' => 'index.php?option=com_sabullvial&view=cotizacion&layout=modal_view_review&tmpl=component&id=' . $cotizacion->id,
            'footer' => $this->sublayout('modal_footer', $displayData),
            'bodyHeight' => 70,
            'modalWidth' => 50,
        ]
    );
}
