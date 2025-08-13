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

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

$document = JFactory::getDocument();
$document->addScriptOptions('com_sabullvial', [
    'cotizacion' => $this->item
]);

$isVueProd = JComponentHelper::getParams('com_sabullvial')->get('vue_production', false);
HTMLHelper::script('com_sabullvial/vue.' . ($isVueProd ? 'global.prod.js' : 'global.js'), ['version' => 'auto', 'relative' => true]);
HTMLHelper::script('com_sabullvial/modal_review.js', ['version' => 'auto', 'relative' => true]);

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addStyleDeclaration('
    .width-64px{
        width: 64px;
    }
    .box-shadow-remove{-moz-box-shadow: none; -webkit-box-shadow: none; box-shadow: none;}
    .b-0{border: 0px;}
    .p-0{padding: 0px !important;}
');
?>
<div id="modalReviewApp" class="container-popup">
    <div class="form-horizontal">
        <div class="row-fluid form-horizontal-desktop">
            <div class="span12">
                <table class="adminlist table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="1%" class="center hidden-phone"><?php echo JText::_('COM_SABULLVIAL_COTIZACION_ESTADO'); ?></th>
                            <th class="hidden-phone"><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CODIGO'); ?></th>
                            <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_NOMBRE'); ?></th>
                            <th class="hidden-phone" width="60"><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CANTIDAD_REQUERIDA'); ?></th>
                            <th class="hidden-phone" width="90"><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CANTIDAD_DISPONIBLE'); ?></th>
                            <th class="hidden-phone"><?php echo JText::_('COM_SABULLVIAL_COTIZACION_STOCK'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(producto, i) in productos" :key="producto.id" :class="{'success': producto.estado == 1 || producto.cantidad == producto.cantidad_disponible, 'error': producto.estado == -1 && producto.cantidad_disponible < producto.cantidad}">
                            <td class="center hidden-phone">
                                <span v-if="producto.estado == 1 || producto.cantidad == producto.cantidad_disponible" class="icon-publish text-success " aria-hidden="true"></span>
                                <span v-else-if="producto.estado == -1" class="icon-unpublish text-danger" aria-hidden="true"></span>
                                <span v-else="producto.estado == 0" class="muted icon-info" aria-hidden="true"></span>
                            </td>
                            <td class="small hidden-phone">{{producto.codigo_sap}}</td>
                            <td>
                                <div class="hidden-phone">
                                    {{producto.nombre}}
                                </div>
                                <div class="visible-phone thumbnail box-shadow-remove b-0 p-0">
                                    <div class="caption">
                                        <label class="mb-sm-1">
                                            <span v-if="producto.estado == 1 || producto.cantidad == producto.cantidad_disponible" class="icon-publish text-success " aria-hidden="true"></span>
                                            <span v-else-if="producto.estado == -1" class="icon-unpublish text-danger" aria-hidden="true"></span>
                                            <span v-else="producto.estado == 0" class="muted icon-info" aria-hidden="true"></span>
                                            <b>{{producto.nombre}}</b>
                                        </label>
                                        <p>
                                            Código: <small class="muted">{{producto.codigo_sap}}</small><br/>
                                            Cantidad requerida: {{producto.cantidad}}<br/>
                                            Cantidad disponible: 
                                            <span v-if="producto.cantidad <= 0">-</span>
                                            <input v-else type="number" class="width-64px" v-model="producto.cantidad_disponible" @change="updateData(producto)" :readonly="producto.estado > -1" :max="producto.cantidad" min="0" step="1"/>
                                        </p>
                                        <p v-if="producto.cantidad > 0" class="mb-0">
                                            <button type="button" class="btn btn-default" @click="completo(producto)">
                                                <span class="icon-publish" aria-hidden="true"></span>
                                                <?php echo JText::_('COM_SABULLVIAL_COMPLETE'); ?>
                                            </button>
                                            &nbsp;
                                            <button type="button" class="btn btn-default" @click="incompleto(producto)">
                                                <span class="icon-unpublish" aria-hidden="true"></span>
                                                <?php echo JText::_('COM_SABULLVIAL_INCOMPLETE'); ?>
                                            </button>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="center hidden-phone">{{producto.cantidad}}</td>
                            <td class="hidden-phone">
                                <input type="number" class="input-mini" v-model="producto.cantidad_disponible" @change="updateData(producto)" :readonly="producto.estado > -1" :max="producto.cantidad" min="0" step="1"/>
                            </td>
                            <td class="center hidden-phone">
                                <template v-if="producto.cantidad > 0">
                                    <button type="button" class="btn btn-default btn-mobile-min" @click="completo(producto)">
                                        <span class="icon-publish" aria-hidden="true"></span>
                                        <?php echo JText::_('COM_SABULLVIAL_COMPLETE'); ?>
                                    </button>
                                    &nbsp;
                                    <button type="button" class="btn btn-default btn-mobile-min" @click="incompleto(producto)">
                                        <span class="icon-unpublish" aria-hidden="true"></span>
                                        <?php echo JText::_('COM_SABULLVIAL_INCOMPLETE'); ?>
                                    </button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div>
                <h4><?php echo JText::_('COM_SABULLVIAL_STATES'); ?></h4>
                <ul>
                    <li><span class="muted icon-info" aria-hidden="true"></span> <?php echo JText::_('COM_SABULLVIAL_WITHOUT_STATE'); ?></li>
                    <li><span class="icon-publish" aria-hidden="true"></span> <?php echo JText::_('COM_SABULLVIAL_COMPLETE'); ?></li>
                    <li><span class="icon-unpublish" aria-hidden="true"></span> <?php echo JText::_('COM_SABULLVIAL_INCOMPLETE'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>