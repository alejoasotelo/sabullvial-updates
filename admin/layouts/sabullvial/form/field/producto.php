<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete    Autocomplete attribute for the field.
 * @var   boolean  $autofocus       Is autofocus enabled?
 * @var   array    $checkedOptions  Options that will be set as checked.
 */

JHtml::_('jquery.framework');
HTMLHelper::script('com_sabullvial/producto.js', ['version' => 'auto', 'relative' => true]);


// The active article id field.
$value = $field->value > 0 ? $field->value : '';

// Create the modal id.
$modalId = 'Producto_' . $field->id;

// Script to proxy the select modal function to the modal-fields.js file.
if ($allowSelect) {
    static $scriptSelect = null;

    if (is_null($scriptSelect)) {
        $scriptSelect = [];
    }

    if (!isset($scriptSelect[$field->id])) {
        JFactory::getDocument()->addScriptDeclaration("
			function jSelectProducto_" . $field->id . "(id, codigo_sap, nombre, marca, precio, stock) {
				window.processModalProductoSelect('Producto', '" . $field->name . "', '" . $field->id . "', id, codigo_sap, nombre, marca, precio, stock);
			}
		");

        JText::script('JGLOBAL_ASSOCIATIONS_PROPAGATE_FAILED');
        JText::script('COM_SABULLVIAL_FIELD_PRODUCTO_SUCCESS_TITLE');
        JText::script('COM_SABULLVIAL_FIELD_PRODUCTO_SUCCESS_MESSAGE');

        $scriptSelect[$field->id] = true;
    }
}

// Setup variables for display.
$linkArticles = 'index.php?option=com_sabullvial&amp;view=productos&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';

$modalTitle    = JText::_('COM_SABULLVIAL_FIELD_PRODUCTO_SELECCIONAR_PRODUCTOS');

$urlSelect = $linkArticles . '&amp;function=jSelectProducto_' . $field->id;

if ($value) {
    $params = JComponentHelper::getParams('com_sabullvial');
    $dbName = $params->get('database_name_tango');

    $db    = JFactory::getDbo();
    $query = $db->getQuery(true);

    // Create the base select statement.
    $query->select('pm.nombre')
        ->from($db->qn($dbName . '.producto_medidas', 'pm'));

    $query->select('p.id_categoria, p.id_tipo, p.id_marca, p.nombre AS modelo, p.descripcion, p.velocidades, p.beneficios, p.caracteristicas, p.ver_precio')
        ->leftJoin($db->qn($dbName . '.productos', 'p') . ' ON (p.id = pm.id_producto)');

    $query->select('c1.nombre AS categoria, c1.id_subcate')
        ->leftJoin($db->qn($dbName . '.categorias', 'c1') . ' ON (p.id_categoria = c1.id)');

    $query->select('c2.nombre AS subcate')
        ->leftJoin($db->qn($dbName . '.categorias', 'c2') . ' ON (c1.id_subcate = c2.id)');

    $query->select('pt.nombre AS tipo')
        ->leftJoin($db->qn($dbName . '.producto_tipos', 'pt') . ' ON (pt.id = p.id_tipo)');

    $query->leftJoin($db->qn($dbName . '.marcas', 'm') . ' ON (p.id_marca = m.id)');

    $query
        ->select('(SELECT CONCAT(pf.id_producto,' / ', pf.url) FROM ' . $db->qn($dbName . '.producto_fotos', 'pf') . ' WHERE pf.id_producto = pm.id_producto ORDER BY pf.orden, pf.id LIMIT 1) AS foto')
        ->select('(SELECT GROUP_CONCAT(CONCAT(pf.id_producto,' / ', pf.url) SEPARATOR \',\') FROM ' . $db->qn($dbName . '.producto_fotos', 'pf') . ' WHERE pf.id_producto = pm.id_producto ORDER BY pf.orden, pf.id) AS fotos')
        ->where($db->quoteName('id') . ' IN (' . implode(',', $value) . ')');

    $db->setQuery($query);

    try {
        $title = $db->loadObjectList();
    } catch (RuntimeException $e) {
        JError::raiseWarning(500, $e->getMessage());
    }
}

$title = empty($title) ? JText::_('COM_SABULLVIAL_SELECCIONAR_PRODUCTO') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');

?>

<span class="input-append">
    <input class="input-medium" id="<?php echo $field->id . '_name'; ?>" type="text" value="<?php echo $title; ?>" disabled="disabled" size="35" />

    <?php if ($allowSelect) : ?>
        <button type="button" class="btn hasTooltip <?php echo($value ? ' hidden' : ''); ?>" id="<?php echo $field->id; ?>_select" data-toggle="modal" data-target="#ModalSelect<?php echo $modalId; ?>" title="<?php echo JHtml::tooltipText('COM_SABULLVIAL_CAMBIAR_PRODUCTO'); ?>">
            <span class="icon-file" aria-hidden="true"></span>
            <?php echo JText::_('JSELECT'); ?>
        </button>
    <?php endif; ?>

    <?php if ($allowClear) : ?>
        <button type="button" class="btn<?php echo($value ? '' : ' hidden'); ?>" id="<?php echo $field->id . '_clear'; ?>" onclick="window.processModalProductoParent('<?php echo $field->id; ?>'); return false;">
            <span class="icon-remove" aria-hidden="true"></span>
            <?php echo JText::_('JCLEAR'); ?>
        </button>
    <?php endif; ?>

</span>

<?php if ($allowSelect) : ?>
    <?php echo JHtml::_(
        'bootstrap.renderModal',
        'ModalSelect' . $modalId,
        [
            'title' => $modalTitle,
            'url' => $urlSelect,
            'height' => '400px',
            'width' => '800px',
            'bodyHeight' => '70',
            'modalWidth' => '80',
            'footer' => '<button type="button" class="btn" data-dismiss="modal">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
        ]
        ); 
    ?>
<?php endif; ?>

<?php
// Note: class='required' for client side validation.
$class = $field->required ? ' class="required modal-value"' : '';
$checkIVA = true;
$checkDolar = false;
?>

<div class="clearfix"></div>

<div id="<?php echo $field->id . '_alert'; ?>" class="alert alert-no-items <?php echo !empty($items) ? 'hidden' : ''; ?>">
    <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
</div>
<br/>
<table id="<?php echo $field->id . '_table'; ?>" class="table table-striped table-hover <?php echo empty($items) ? 'hidden' : ''; ?>">
    <thead>
        <tr>
            <th width="1%" class="nowrap center">
                <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_CODIGO_ART'); ?>
            </th>
            <th style="min-width:100px" class="nowrap">
                <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_NOMBRE'); ?>
            </th>
            <th width="10%" class="nowrap hidden-phone">
                <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_MARCA'); ?>
            </th>
            <th width="10%" class="nowrap hidden-phone">
                <?php if ($checkIVA) : ?>
                    <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_PRECIO_CON_IVA'); ?>
                <?php else : ?>
                    <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_PRECIO_SIN_IVA'); ?>
                <?php endif; ?>
            </th>
            <th width="1%" class="nowrap hidden-phone">
                <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_STOCK'); ?>
            </th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($items)) : ?>
            <?php foreach ($items as $i => $row) : ?>
                <?php
                $precio = number_format($row->precio2 + ($row->precio2 * $condicionVenta->dias * $porcDia / 100), 2, '.', '');

                if ($checkDolar) {
                    $precio = $precio / $cotiDol;
                }
                $precio += ($checkIVA == '1' ? ($precio * 21 / 100) : 0);
                ?>
                <tr>
                    <td class="center">
                        <?php echo $row->codigo_sap; ?>
                    </td>
                    <td>
                        <span class="select-link text-link">
                            <?php echo $row->nombre; ?>
                        </span>
                    </td>
                    <td class="small hidden-phone">
                        <?php echo $row->marca; ?>
                    </td>
                    <td class="nowrap small hidden-phone">
                        <?php
                        echo PriceHelper::format($precio);
                ?>
                    </td>
                    <td class="center hidden-phone">
                        <?php if (!$vendedor->get('ver.stockReal')) : ?>
                            <?php echo $row->actual > 10 ? '10+' : $row->actual; ?>
                        <?php else : ?>
                            <?php echo $row->actual; ?>
                        <?php endif; ?>
                        <input type="hidden" id="<?php echo $field->id . '_id_'.$row->id; ?>" <?php echo $class; ?> data-required="<?php echo (int) $field->required; ?>" name="<?php echo $field->name; ?>" data-text="<?php echo htmlspecialchars(JText::_('COM_SABULLVIAL_SELECCIONAR_PRODUCTO'), ENT_COMPAT, 'UTF-8'); ?>" value="<?php echo $row->id; ?>" />
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>