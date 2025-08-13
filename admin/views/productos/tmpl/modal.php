<?php
/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\HTML\HTMLHelper;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$app = JFactory::getApplication();

if ($app->isClient('site')) {
    JSession::checkToken('get') or die(JText::_('JINVALID_TOKEN'));
}

//$selectedIds =

JHtml::_('behavior.core');
JHtml::_('behavior.polyfill', ['event'], 'lt IE 9');
HTMLHelper::script('com_sabullvial/productos-modal.js', ['version' => 'auto', 'relative' => true]);
JHtml::_('jquery.framework');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.popover');

JText::script('COM_SABULLVIAL_FIELD_PRODUCTO_SUCCESS_TITLE');
JText::script('COM_SABULLVIAL_FIELD_PRODUCTO_SUCCESS_MESSAGE');

$porcentajeDia = (float)SabullvialHelper::getConfig('PORC_DIA');
$cotizacionDolar = (float)SabullvialHelper::getConfig('COTI_DOL');

$conIva = (bool)$this->state->get('filter.iva', 1);
$conDolar = (bool)$this->state->get('filter.dolar', 0);

$filterCondicionVenta = (int)$this->state->get('filter.id_condicionventa', 49);
$condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
$condicionVenta->loadByCondicionVenta($filterCondicionVenta);

$kInteres = (float)$condicionVenta->DIAS * $porcentajeDia / 100;

$vendedor = SabullvialHelper::getVendedor();

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$function  = $app->input->getCmd('function', 'jSelectProducto');
$onclick   = $this->escape($function);
?>
<div class="container-popup">
    <form action="<?php echo JRoute::_('index.php?option=com_sabullvial&view=productos&layout=modal&tmpl=component&function=' . $function . '&' . JSession::getFormToken()); ?> " method="post" id="adminForm" name="adminForm" class="form-inline">
        
        <?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>

        <div class="clearfix"></div>

        <?php if (empty($this->items)) : ?>
            <div class="alert alert-no-items">
                <?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
            </div>
        <?php else: ?>
            <table id="table" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="1%" class="center">
                            <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_IMAGEN'); ?>
                        </th>
                        <th width="1%" class="nowrap center">					
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_CODIGO_ART', 'codigo_sap', $listDirn, $listOrder); ?>
                        </th>
                        <th style="min-width:100px" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_NOMBRE', 'nombre', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_MARCA', 'marca', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php if ($conIva): ?>
                                <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_PRECIO_CON_IVA', 'precio', $listDirn, $listOrder); ?>
                            <?php else: ?>
                                <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_PRECIO_SIN_IVA', 'precio', $listDirn, $listOrder); ?>
                            <?php endif; ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_STOCK', 'stock', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="5">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (!empty($this->items)) : ?>
                        <?php foreach ($this->items as $i => $row) : ?>
                            <?php
                                $row->precio +=  $row->precio * $kInteres;

                            if ($conDolar) {
                                $row->precio /= $cotizacionDolar;
                            }

                            if ($conIva) {
                                $row->precio *= (1 + (SabullvialHelper::IVA_21 / 100));
                            }

                            $stock = !$vendedor->get('ver.stockReal') && $row->stock > 10 ? '+10' : (int)$row->stock;

                            $attribs = 'data-function="' . $this->escape($onclick) . '"'
                                . ' data-id="' . $row->id . '"'
                                . ' data-codigo_sap="' . $this->escape($row->codigo_sap) . '"'
                                . ' data-nombre="' . $this->escape($row->nombre) . '"'
                                . ' data-marca="' . $this->escape($row->marca) . '"'
                                . ' data-precio="' . $this->escape($row->precio) . '"'
                                . ' data-stock="' . $this->escape($stock) . '"'
                                . ' data-added="0"';
                            ?>
                            <tr id="row_<?php echo $row->id; ?>" class="select-row <?php /*echo $rowSelected ? 'success' : '';*/ ?>" <?php echo $attribs; ?>>
                                <td class="center">
                                    <?php if (count($row->images)): ?>
                                        <?php
                                            $image = array_values($row->images)[0];
                                            $src = JUri::root() . $image['path'];
                                        ?>
                                        <img src="<?php echo $src; ?>" width="32" height="32" />
                                    <?php endif; ?>
                                </td>
                                <td class="small center">
                                    <?php echo $row->codigo_sap; ?>
                                </td>
                                <td>
                                    <?php echo $this->escape($row->nombre); ?>
                                </td>
                                <td class="small">
                                    <?php echo $row->marca; ?>
                                </td>
                                <td class="nowrap small">
                                    <?php
                                        echo JText::_($conDolar ? 'COM_SABULLVIAL_DOLAR_SIMBOLO' : 'COM_SABULLVIAL_PESO_SIMBOLO').number_format($row->precio, 2, ',', '.');
                            ?>
                                </td>
                                <td class="center">
                                    <?php if (!$vendedor->get('ver.stockReal')): ?>
                                        <?php
                                            $stock = $row->stock > 10 ? '10+' : (int)$row->stock;
                                            $stockDeposito1 = $row->stock_deposito_1 > 10 ? '10+' : (int)$row->stock_deposito_1;
                                            $stockDeposito2 = $row->stock_deposito_2 > 10 ? '10+' : (int)$row->stock_deposito_2;
                                            $stockDeposito3 = $row->stock_deposito_3 > 10 ? '10+' : (int)$row->stock_deposito_3;
                                            $popoverTitle = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_TITLE', $stock);
                                            $popoverContent = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_1', $stockDeposito1);
                                            $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_2', $stockDeposito2);
                                            $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_3', $stockDeposito3);
                                        ?>
                                    <?php else: ?>
                                        <?php
                                            $stock = (int)$row->stock;
                                            $popoverTitle = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_TITLE', $stock);
                                            $popoverContent = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_1', (int)$row->stock_deposito_1);
                                            $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_2', (int)$row->stock_deposito_2);
                                            $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_3', (int)$row->stock_deposito_3);
                                        ?>
                                    <?php endif; ?>
                                    <div class="hasPopover" title="<?php echo htmlspecialchars($popoverTitle); ?>" data-content="<?php echo htmlspecialchars($popoverContent); ?>" data-placement="left" data-trigger="hover focus click" data-html="true" data-container="container" data-toggle="popover">
                                        <?php echo $stock; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?php /*<input type="hidden" name="selected_id" value="<?php echo $selectedIds; ?>"/>*/ ?>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>