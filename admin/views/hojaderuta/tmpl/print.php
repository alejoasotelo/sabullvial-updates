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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

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

    table.detalle tbody td{
        font-size: 11px;
        padding: 1px 2px;
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

<table class="head">
    <tr>
        <td>Ruta de la tradición 4020 - Bo. 9 de Abril - E.Echeverría - Bs.As.<br>
            Tel/Fax: (011) 5263-5020<br>
            www.bull-vial.com.ar
        </td>
        <td style="text-align:right;width:30%;">
            <img src="<?php echo Juri::root() . 'media/com_sabullvial/img/bull-vial-logo.png'; ?>" alt="Logo">
        </td>
    </tr>
</table>
<hr>

<table class="cabecera" border="1">
    <tr>
        <td colspan="2">
            <b>Hoja de ruta Nº:</b> <?php echo $this->item->id; ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Fecha de entrega:</b> <?php echo HtmlHelper::date($this->item->delivery_date, Text::_('DATE_FORMAT_LC1'), null); ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Chofer:</b> <?php echo $this->item->chofer; ?>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <b>Patente:</b> <?php echo $this->item->patente; ?>
        </td>
    </tr>
</table>
<br/>

<table class="detalle" border="1">
    <thead>
        <th><?php echo JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_NUMERO_REMITO_LABEL'); ?></th>
        <th><?php echo JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_CLIENTE_LABEL'); ?></th>
        <th><?php echo JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_DIRECCION_LABEL'); ?></th>
        <th><?php echo JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_EXPRESO_LABEL'); ?></th>
        <th><?php echo JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_DIRECCION_LABEL'); ?></th>
        <th><?php echo JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_MERCADERIA_LABEL'); ?></th>
    </thead>
    <tbody>
        <?php
            $montoTotal = 0;
            /** @var SabullvialModelRemito $model */
            $model = BaseDatabaseModel::getInstance('Remito', 'SabullvialModel');
            $k = 0;
        ?>
        <?php foreach ($this->item->hojaderutaremito as $i => $row) : ?>
            <?php
                $productos = $model->getProductos($row['numero_remito']);
                $countProducts = count($productos);
            ?>
            <?php foreach ($productos as $j => $producto): ?>
                <?php
                    $odd = ($k++ % 2 == 0 ? 'odd' : '');

                    $nombreProducto = '(' . (int)$producto->cantidad_remito . ') ' . trim($producto->descripcion);
                    if (!empty($producto->marca)) {
                        $nombreProducto .= ' - ' . trim($producto->marca);
                    }

                    $nombreLentgh = strlen($nombreProducto);
                    $maxLengthToWrap = 40;
                    $nombreStyle = $nombreLentgh <= $maxLengthToWrap ? 'style="white-space: nowrap;"' : '';

                ?>
                <tr class="<?php echo $odd; ?>">
                    <?php if ($j == 0): ?>
                        <?php
                            $rowsPan = $countProducts > 1 ? ' rowspan="' . $countProducts  . '"' : '';
                        ?>
                        <td <?php echo $rowsPan; ?>><?php echo $row['numero_remito']; ?></td>
                        <td <?php echo $rowsPan; ?>><?php echo $row['cliente']; ?></td>
                        <td <?php echo $rowsPan; ?>><?php echo $row['direccion']; ?></td>
                        <td <?php echo $rowsPan; ?>><?php echo $row['expreso']; ?></td>
                        <td <?php echo $rowsPan; ?>><?php echo $row['transporte_direccion']; ?></td>
                        <td <?php echo $nombreStyle; ?>><?php echo $nombreProducto; ?></td>
                    <?php else: ?>
                        <td <?php echo $nombreStyle; ?>><?php echo $nombreProducto; ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </tbody>
</table>