<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : [];

if (is_array($data['options'])) {
    $data['options'] = new Registry($data['options']);
}

$modalId = 'selectCliente' . $data['options']->get('id', '0');


$onShowModal = $data['options']->get('onShowModal', '');
if (!empty($onShowModal)) {
    $doc = JFactory::getDocument();
    $doc->addScriptDeclaration('
        jQuery(document).ready(function() {
            jQuery("#'.$modalId.'").on("shown.bs.modal", function() {
                ' . $onShowModal . '
            });
        });
    ');
}

?>

<?php
echo JHtml::_(
    'bootstrap.renderModal',
    $modalId,
    [
        'title'       => JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_COTIZACION'),
        'modalWidth'  => '50',
        'modalHeight' => '500',
        'closeButton' => true,
        'footer' => $this->sublayout('footer', $data),
    ],
    $this->sublayout('body', $data)
); ?>