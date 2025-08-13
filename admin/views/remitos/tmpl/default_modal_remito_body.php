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

use Joomla\CMS\Language\Text;

$isAdministrador = SabullvialHelper::isUserAdministrador();
$vendedor = SabullvialHelper::getVendedor();
$canDeleteImagen = $vendedor->get('borrar.remito.imagen', false);
?>

<div class="container-popup">

    <div class="form-horizontal">

        <div class="row-fluid">

            <div class="control-group">
                <div class="control-label"><?php echo Text::_('COM_SABULLVIAL_REMITOS_CLIENTE'); ?></div>
                <div class="controls text-left" style="padding-top: 5px;">
                    {{ detailRemito.cliente }}
                </div>
            </div>

            <div v-if="detailRemito.numero_factura" class="control-group">
                <div class="control-label"><?php echo Text::_('COM_SABULLVIAL_REMITOS_NUMERO_FACTURA'); ?></div>
                <div class="controls text-left" style="padding-top: 5px;">
                    {{ detailRemito.numero_factura }}
                </div>
            </div>

            <div v-if="detailRemito.id_cotizacion" class="control-group">
                <div class="control-label"><?php echo Text::_('COM_SABULLVIAL_REMITOS_ID_COTIZACION'); ?></div>
                <div class="controls text-left" style="padding-top: 5px;">
                    {{ detailRemito.id_cotizacion }}
                </div>
            </div>

            <div class="control-group">
                <div class="control-label"><?php echo Text::_('COM_SABULLVIAL_REMITOS_EXPRESO'); ?></div>
                <div class="controls text-left" style="padding-top: 5px;">
                    {{ detailRemito.expreso }}
                </div>
            </div>

            <div class="control-group">
                <div class="control-label"><?php echo Text::_('COM_SABULLVIAL_REMITOS_DIRECCION_ENTREGA'); ?></div>
                <div class="controls text-left" style="padding-top: 5px;">
                    {{ detailRemito.direccion_entrega }}
                </div>
            </div>

            <?php if ($isAdministrador) : ?>
                <div class="control-group">
                    <div class="control-label"><?php echo Text::_('COM_SABULLVIAL_REMITOS_MONTO_REMITO'); ?></div>
                    <div class="controls text-left" style="padding-top: 5px;">
                        {{ detailRemito.montoRemitoFormated }}
                    </div>
                </div>
            <?php endif; ?>

            <div class="control-group">
                <div class="control-label">Estados</div>
                <div class="controls" v-if="!loadingModalDetail && detailRemito.historico">
                    <?php
                        echo JLayoutHelper::render(
                            'vue.form.field.historicotable',
                            [
                                'id' => 'historicotable',
                                'name' => 'historicotable',
                                'options' => 'detailRemito.historico',
                                'deliveryDate' => 'detailRemito.delivery_date',
                                'deliveryDateFormated' => 'formatDate(detailRemito.delivery_date)',
                                'style' => htmlentities('max-height: 161px;overflow-y: auto; max-width: ' . ($canDeleteImagen ? '600px' : '500px')),
                                'deleteImage' => $canDeleteImagen,
                                'onDeleteImage' => 'deleteRemitoImage',
                            ]
                        );
                    ?>
                </div>
            </div>

            <div class="control-group">
                <div class="control-label">Artículos</div>
                <div class="controls" v-if="!loadingModalDetail && detailRemito.productos">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="15%" class="center"><?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_CODIGO_ARTICULO'); ?></th>
                                <th><?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_DESCRIPCION'); ?></th>
                                <?php if ($isAdministrador) : ?>
                                    <th width="5%" class="center">
                                        <?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_CANTIDAD'); ?>
                                    </th>
                                    <th width="15%" class="center">
                                        <?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_PRECIO_UNITARIO'); ?>
                                    </th>
                                    <th width="8%" class="center">
                                        <?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_PRECIO_SUBTOTAL'); ?>
                                    </th>
                                <?php else : ?>
                                    <th width="10%" class="center">
                                        <?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_CANTIDAD'); ?>
                                    </th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="producto in detailRemito.productos" :key="producto.codigo_articulo">
                                <td class="center small">
                                    {{ producto.codigo_articulo }}
                                </td>
                                <td class="text-left nowrap">{{ producto.descripcion }}</td>
                                <?php if ($isAdministrador) : ?>
                                    <td class="center">{{ parseInt(producto.cantidad_remito) }}</td>
                                    <td class="center">{{ formatPrice(producto.precio_unitario) }}</td>
                                    <td class="center">{{ formatPrice(producto.precio_unitario * producto.cantidad_remito) }}</td>
                                <?php else : ?>
                                    <td class="center">{{ parseInt(producto.cantidad_remito) }}</td>
                                <?php endif; ?>
                            </tr>
                            <tr v-if="detailRemito.productos.length == 0">
                                <td colspan="<?php echo $isAdministrador ? 5 : 3; ?>" class="center">
                                    <?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_SIN_PRODUCTOS'); ?>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot v-if="modalDetailRemitoProductosTotal">
                            <tr>
                                <td colspan="<?php echo $isAdministrador ? 4 : 2; ?>" class="text-right">
                                    <b class="pull-right">
                                        <?php echo Text::_('COM_SABULLVIAL_REMITOS_MODAL_PRECIO_TOTAL'); ?>
                                    </b>
                                </td>
                                <td class="center">{{ formatPrice(modalDetailRemitoProductosTotal) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>