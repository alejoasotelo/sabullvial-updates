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

use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

?>
<style>
    * {
        font-size: 14px;
        font-family: 'Helvetica';
    }

    table.head,
    table.detalle,
    table.cabecera {
        width: 100%;
        border-collapse: collapse;
        border-spacing: 0;
        vertical-align: middle;
    }

    table.head {
        font-size: 12px !important;
    }

    table.cabecera td {
        padding: 3px;
    }

    table.detalle tbody tr.odd {
        background-color: #ededed;
    }

    table.detalle th {
        text-align: center;
        background-color: #000085;
        color: #fff;
    }

    table.detalle tfoot .marco {
        text-align: center;
        background-color: #000085;
        color: #fff;
    }

    table.detalle .descripcion-img{
        vertical-align: middle;
    }

    table.detalle .descripcion-img a {
        display: inline-block; 
        width: 16px; 
        height: auto; 
    }

    #footer {
        position: fixed;
        left: 0px;
        bottom: -160px;
        right: 0px;
        height: 150px;
        font-size: 10px;
    }

    .left {
        text-align: left;
    }

    .right {
        text-align: right;
    }

    .center {
        text-align: center;
    }

    .clearfix::after {
        content: "";
        clear: both;
        display: table;
    }
</style>

<?php
$verhtml = false;
$subtotal = 0;
$descCalculado = 0;
$iva = 0;
$iibb = 0;

$simboloPrecio = in_array($this->cliente->COD_LISTA, SabullvialHelper::LISTAS_DOLAR) || (bool)$this->item->dolar ? PriceHelper::FORMAT_USD : PriceHelper::FORMAT_ARS;

$isRevision = $this->type == 'revisiondetalle';
$itemsdetalle = $isRevision ? $this->item->revisiondetalle : $this->item->cotizaciondetalle;

if (count($itemsdetalle)) {
    foreach ($this->item->cotizaciondetalle as $k => $item) {
        $descCalculado += ($item['cantidad'] * $item['precio']) * ($item['descuento'] / 100);
    }
}
$vendedor = SabullvialHelper::getVendedor();
$isRevendedor = $vendedor->get('esRevendedor', false);
?>

<?php if (!$verhtml) : ?>
    <table class="head">
        <tr>
            <td>
                Ruta de la tradición 4020 - Bo. 9 de Abril - E.Echeverría - Bs.As.<br>
                <img src="<?php echo Juri::root() . 'media/com_sabullvial/img/whatsapp.png'; ?>" alt="Whatsapp" width="16" height="16" style="vertical-align:bottom; margin-bottom: 3px"> Tel/Fax: (011) 5263-5020<br>
                www.bull-vial.com.ar
            </td>
            <td style="text-align:right;width:30%;">
                <img src="<?php echo Juri::root() . 'media/com_sabullvial/img/bull-vial-logo.png'; ?>" alt="Logo">
            </td>
        </tr>
    </table>
    <hr>
<?php endif; ?>

<table class="cabecera" border="1">
    <tr class="center">
        <td><b><?php echo JHtml::_('date', $this->item->created, Text::_('DATE_FORMAT_LC5')); ?></b></td>
        <td><b>PPTO Nº <?php echo $this->item->id; ?></b></td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Cliente/Empresa:</b> <?php echo $this->item->cliente; ?>
            <?php if (isset($this->item->solicitante) && !empty($this->item->solicitante)) : ?>
                <br>
                <b>Solicitante:</b> <?php echo $this->item->solicitante; ?>
            <?php endif; ?>
        </td>
    </tr>
</table>
<?php if (!$verhtml) : ?>
    <p><br>Estimado Cliente,</p>
    <p>Por medio del presente damos respuesta a su pedido de cotización:</p>
<?php else : ?>
    <br>
<?php endif; ?>

<table class="detalle" border="1">
    <thead>
        <tr>
            <th colspan="3">Descripción</th>
            <th>Precio Unitario</th>
            <th>Cant.</th>
            <?php if ($descCalculado > 0) : ?>
                <th>Desc.</th>
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
                    <td style="border-right: 0px;">
                        <?php echo $item['nombre'] . ($item['marca'] ? ' - ' . $item['marca'] : ''); ?>
                    </td>
                    <td width="16" style="border-left: 0px; border-right: 0px;">
                        <div class="descripcion-img">
                            <?php if (!empty($item['url'])): ?>
                                <a href="<?php echo $item['url']; ?>" target="_blank" title="<?php echo Text::_('COM_SABULLVIAL_COTIZACION_PRINT_ABRIR_ENLACE'); ?>">
                                    <img src="<?php echo Juri::root() . 'media/com_sabullvial/img/icon-catalog-32x32.png'; ?>" width="16" height="16" />
                                </a>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td width="16" style="border-left: 0px;">
                        <div class="descripcion-img">
                            <?php if (!empty($item['images'])): ?>
                                <?php
                                    $image = array_values($item['images'])[0];
                                    $src = JUri::root() . $image['path'];
                                ?>
                                <a href="<?php echo $src; ?>" target="_blank" title="<?php echo Text::_('COM_SABULLVIAL_COTIZACION_PRINT_VER_IMAGEN_PRODUCTO'); ?>">
                                    <img src="<?php echo Juri::root() . 'media/com_sabullvial/img/icon-image-32x32.png'; ?>" width="16" height="16" />
                                </a>
                            <?php endif; ?>
                        </div>
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

<p class="right">
    Subtotal: <?php echo PriceHelper::format($this->item->subtotal, $simboloPrecio); ?>
    <br>

    <?php if ($this->item->ivaTotal > 0) : ?>
        IVA 21%: <?php echo PriceHelper::format($this->item->ivaTotal, $simboloPrecio); ?>
        <br>
    <?php endif; ?>

    IIBB <?php echo number_format($this->item->porcentaje_iibb, 1, ',', '.') ?: 0; ?>%: <?php echo PriceHelper::format($this->item->iibb, $simboloPrecio); ?>
    <br>

    <?php if ($this->item->descuento > 0) : ?>
        <?php $descuento = ($this->item->subtotal * ($this->item->descuento / 100)) + $descCalculado; ?>
        Descuento (<?php echo number_format($this->item->descuento, 2, ',', '.'); ?>%): <?php echo PriceHelper::format($descuento, $simboloPrecio); ?>
        <br>
    <?php endif ?>

    <b>TOTAL con IVA: <?php echo PriceHelper::format($this->item->total, $simboloPrecio); ?></b>

    <?php if (
        in_array($this->cliente->COD_LISTA, SabullvialHelper::LISTAS_SIN_IVA) ||
        (is_null(SabullvialHelper::getLabelListaDePrecio($this->cliente->COD_LISTA)) && $this->cliente->COD_LISTA != '1')
    ) : ?>
        <br>
        <br>
        <i>Atención los precios son más impuestos.</i>
    <?php endif; ?>
</p>

<?php if (!$isRevendedor): ?>
<p><b>Forma de pago:</b> <?php echo $this->item->condicionVentaFake->DESC_COND; ?>.</p>
<?php endif; ?>
<p><b>Plazo de Entrega:</b> <?php echo $this->item->delivery_term; ?>.</p>

<?php if (SabullvialHelper::getConfig('VAL_OFF')) : ?>
    <p><b>Mantenimiento de oferta:</b> <?php echo SabullvialHelper::getConfig('VAL_OFF'); ?></p>
<?php endif; ?>

<?php if (!empty($this->item->observations)) : ?>
    <p>
        <b>Aclaración</b><br>
        <?php echo nl2br($this->item->observations); ?>
    </p>
<?php endif; ?>

<?php if (!$verhtml) : ?>
    <table style="width:100%; text-align:center;">
        <tr>
            <td><img src="<?php echo Juri::root() . 'media/com_sabullvial/img/logo-goodyear.jpg'; ?>" alt="Goodyear" style="max-width:200px"></td>
            <td><img src="<?php echo Juri::root() . 'media/com_sabullvial/img/logo-maxam.jpg'; ?>" alt="Maxam" style="max-width:200px"></td>
            <td><img src="<?php echo Juri::root() . 'media/com_sabullvial/img/logo-titan.jpg'; ?>" alt="Titan" style="max-width:200px"></td>
        </tr>
    </table>
<?php endif; ?>