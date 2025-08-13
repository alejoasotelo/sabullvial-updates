<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

extract($displayData);

require_once JPATH_ADMINISTRATOR . '/components/com_sabullvial/controllers/cotizaciones.php';
require_once JPATH_ADMINISTRATOR . '/components/com_sabullvial/tables/estadocotizacion.php';

$items = Table::getInstance('EstadoCotizacion', 'SabullvialTable')->getEstadosCancelado();

Factory::getDocument()->addStyleDeclaration("
    .mb-1-i {
        margin-bottom: 6px !important;
    }
");

?>
<a class="btn mb-1-i" type="button" data-dismiss="modal">
    <?php echo JText::_('JTOOLBAR_CLOSE'); ?>
</a>

<?php foreach ($items as $item): ?>
    <?php
        $label = trim(str_replace(' ', '_', strtoupper($item->nombre)));
        $action = SabullvialControllerCotizaciones::ACTION_ESTADO_COTIZACION_CANCELADO_PATTERN . $item->id;
    ?>
    <button type="button" class="btn btn-default mb-1-i" onclick="return Joomla.listItemTask('cb<?php echo $index; ?>', 'cotizaciones.<?php echo $action; ?>');">
        <span class="icon-unpublish" aria-hidden="true"></span>
        <?php echo Text::_('COM_SABULLVIAL_LAYOUT_COTIZACIONES_CANCELAR_' .$label); ?>
    </button>
<?php endforeach; ?>