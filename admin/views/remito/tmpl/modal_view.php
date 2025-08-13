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

$app = JFactory::getApplication();
$input = $app->input;

//JFactory::getDocument()->addStyleDeclaration('');

$productos = $this->getModel()->getProductos($this->item->N_REMITO);
?>

<table class="table">
    <thead>
        <tr>
            <th class="text-center"><?php echo JText::_('COM_SABULLVIAL_REMITO_MODAL_CODIGO_ARTICULO'); ?></th>
            <th><?php echo JText::_('COM_SABULLVIAL_REMITO_MODAL_DESCRIPCION'); ?></th>
            <th class="text-center"><?php echo JText::_('COM_SABULLVIAL_REMITO_MODAL_CANTIDAD'); ?></th>
            <th class="text-center"><?php echo JText::_('COM_SABULLVIAL_REMITO_MODAL_PRECIO'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($productos as $key => $row) : ?>
            <tr>
                <td class="text-center small"><?php echo $row->codigo_articulo; ?></td>
                <td class="text-left"><?php echo $row->descripcion; ?></td>
                <td class="text-center small"><?php echo $row->cantidad_remito; ?></td>
                <td class="text-center small"><?php echo PriceHelper::format($row->precio_unitario); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>