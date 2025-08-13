<?php
/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;

$idCotizacion = $this->form->getData()->get('id_cotizacion');
$cotizacion = $this->getModel()->getCotizacion($idCotizacion);

Table::addIncludePath(JPATH_COMPONENT . '/tables/');
$cliente = Table::getInstance('SitClientes', 'SabullvialTable');
$cliente->loadByCodCliente($cotizacion->id_cliente);
$this->cliente = $cliente;

$condicionVenta = Table::getInstance('SitCondicionesVenta', 'SabullvialTable');
$condicionVenta->loadByCondicionVenta($cotizacion->id_condicionventa);
$cotizacion->condicionVenta = $condicionVenta;

$condicionVentaFake = Table::getInstance('SitCondicionesVenta', 'SabullvialTable');
$condicionVentaFake->loadByCondicionVenta($cotizacion->id_condicionventa_fake);
$cotizacion->condicionVentaFake = $condicionVentaFake;

$this->type = 'cotizaciondetalle';
$itemsDetalle = $cotizacion->cotizaciondetalle;

$hasIVA = (int)$cotizacion->iva == 1;

$cotizacion->subtotal = SabullvialCotizacionesHelper::calcSubtotal($itemsDetalle);
$cotizacion->ivaTotal = SabullvialCotizacionesHelper::calcIva($hasIVA, $itemsDetalle);
$cotizacion->iibb = SabullvialCotizacionesHelper::calcIIBB($cotizacion->porcentaje_iibb, $itemsDetalle, $hasIVA);

$cotizacion->total = $cotizacion->subtotal + $cotizacion->ivaTotal + $cotizacion->iibb;

$descCalculado = 0;

$simboloPrecio = in_array($this->cliente->COD_LISTA, SabullvialHelper::LISTAS_DOLAR) || (bool)$cotizacion->dolar ? PriceHelper::FORMAT_USD : PriceHelper::FORMAT_ARS;

$isRevision = $this->type == 'revisiondetalle';
$itemsdetalle = $isRevision ? $cotizacion->revisiondetalle : $cotizacion->cotizaciondetalle;

if (count($itemsdetalle)) {
    foreach ($cotizacion->cotizaciondetalle as $k => $item) {
        $descCalculado += ($item['cantidad'] * $item['precio']) * ($item['descuento'] / 100);
    }
}
$vendedor = SabullvialHelper::getVendedor();
$isRevendedor = $vendedor->get('esRevendedor', false);

SabullvialHelper::loadEstadosCotizacionStylesheet();
?>

<div class="control-group">
    <div class="control-label">
        <label for="">Cotización</label>
    </div>
    <div class="controls">
        <?php echo $cotizacion->id; ?>
    </div>
</div>

<div class="control-group">
    <div class="control-label">
        <label for="">Fecha</label>
    </div>
    <div class="controls">
        <?php echo JHtml::_('date', $cotizacion->created, Text::_('DATE_FORMAT_LC5')); ?>hs
    </div>
</div>

<div class="control-group">
    <div class="control-label">
        <label for="">Cliente</label>
    </div>
    <div class="controls">
        <?php echo $cotizacion->cliente; ?>
    </div>
</div>

<?php if (isset($cotizacion->solicitante) && !empty($cotizacion->solicitante)) : ?>
    <div class="control-group">
        <div class="control-label">
            <label for="">Solicitante</label>
        </div>
        <div class="controls">
            <?php echo $cotizacion->solicitante; ?>
        </div>
    </div>
<?php endif; ?>

<?php
    $field = $this->form->getField('id_estadocotizacion');
    $field->setValue($cotizacion->id_estadocotizacion);
    echo $field->renderField();
?>

<?php
    $field = $this->form->getField('cotizacionhistorico');
    $field->id_cotizacion = $idCotizacion;
    echo $field->renderField();
?>

<div class="control-group">
    <div class="control-label">
        <label for="">Productos</label>
    </div>
    <div class="controls">        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Precio Unitario</th>
                    <th class="center">Cantidad</th>
                    <?php if ($descCalculado > 0) : ?>
                        <th>Descuento</th>
                    <?php endif; ?>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($itemsdetalle)) : ?>

                    <?php foreach ($itemsdetalle as $k => $item) : ?>
                        <?php
                            if ($item['cantidad'] < 0 || ($isRevision && $item['cantidad'] == 0 && $item['cantidad'] != $item['cantidad_requerida'])) {
                                continue;
                            }

                            $odd = ($k % 2 == 0 ? 'class="odd"' : '');

                            $tableImagen = Table::getInstance('ProductoImagen', 'SabullvialTable');
                            $tableImagen->loadByIdProducto($item['id_producto']);

                            $item['images'] = json_decode($tableImagen->images, true);
                            $item['url'] = $tableImagen->url;
                        ?>

                        <tr <?php echo $odd; ?>>
                            <td>
                                <?php echo $item['nombre'] . ($item['marca'] ? ' - ' . $item['marca'] : ''); ?>
                            </td>
                            <td class="right">
                                <?php echo PriceHelper::format($item['precio'], $simboloPrecio); ?>
                            </td>
                            <td class="center"><?php echo $item['cantidad']; ?></td>
                            <?php if ($descCalculado > 0) : ?>
                                <td class="center">
                                    <?php echo $item['descuento'] > 0 ? number_format($item['descuento'], 2, ',', '.') . '%' : '-'; ?>
                                </td>
                            <?php endif; ?>
                            <td class="right">
                                <?php echo PriceHelper::format($item['subtotal'], $simboloPrecio); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5">No encontramos items.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="control-group">
    <div class="control-label">
        <label for="">Subtotal</label>
    </div>
    <div class="controls">
        <?php echo PriceHelper::format($cotizacion->subtotal, $simboloPrecio); ?>
    </div>
</div>

<?php if ($cotizacion->ivaTotal > 0) : ?>
    <div class="control-group">
        <div class="control-label">
            <label for="">IVA 21%</label>
        </div>
        <div class="controls">
            <?php echo PriceHelper::format($cotizacion->ivaTotal, $simboloPrecio); ?>
        </div>
    </div>
<?php endif; ?>

<div class="control-group">
    <div class="control-label">
        <label for="">IIBB <?php echo number_format($cotizacion->porcentaje_iibb, 1, ',', '.') ?: 0; ?>%</label>
    </div>
    <div class="controls">
        <?php echo PriceHelper::format($cotizacion->iibb, $simboloPrecio); ?>
    </div>
</div>

<?php if ($cotizacion->descuento > 0) : ?>
    <div class="control-group">
        <div class="control-label">
            <label for="">Descuento (<?php echo number_format($cotizacion->descuento, 2, ',', '.'); ?>%)</label>
        </div>
        <div class="controls">
            <?php $descuento = ($cotizacion->subtotal * ($cotizacion->descuento / 100)) + $descCalculado; ?>
            <?php echo PriceHelper::format($descuento, $simboloPrecio); ?>
        </div>
    </div>
<?php endif; ?>

<div class="control-group">
    <div class="control-label">
        <label for="">TOTAL con IVA</label>
    </div>
    <div class="controls">
        <b><?php echo PriceHelper::format($cotizacion->total, $simboloPrecio); ?></b>
    </div>
</div>

<?php if (!$isRevendedor): ?>
    <div class="control-group">
        <div class="control-label">
            <label for="">Forma de pago</label>
        </div>
        <div class="controls">
            <?php echo $cotizacion->condicionVentaFake->DESC_COND; ?>
        </div>
    </div>
<?php endif; ?>

<div class="control-group">
    <div class="control-label">
        <label for="">Plazo de Entrega</label>
    </div>
    <div class="controls">
        <?php echo $cotizacion->delivery_term; ?>
    </div>
</div>

<?php if (!empty($cotizacion->observations)) : ?>
    <div class="control-group">
        <div class="control-label">
            <label for="">Aclaración</label>
        </div>
        <div class="controls">
            <?php echo nl2br($cotizacion->observations); ?>
        </div>
    </div>
<?php endif; ?>