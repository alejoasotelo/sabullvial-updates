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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

$document = JFactory::getDocument();
$document->addScriptOptions('com_sabullvial', [
    'cotizacion' => $this->item
]);

$isVueProd = JComponentHelper::getParams('com_sabullvial')->get('vue_production', false);
HTMLHelper::script('com_sabullvial/vue.' . ($isVueProd ? 'global.prod.js' : 'global.js'), ['version' => 'auto', 'relative' => true]);
HTMLHelper::script('com_sabullvial/modal_approve.js', ['version' => 'auto', 'relative' => true]);

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addStyleDeclaration('
    .width-64px{
        width: 64px;
    }
    .box-shadow-remove{-moz-box-shadow: none; -webkit-box-shadow: none; box-shadow: none;}
    .b-0{border: 0px;}
    .p-0{padding: 0px !important;}
    .text-left-important {
        text-align: left !important;
    }
    .text-center-important {
        text-align: center !important;
    }
    .text-right-important {
        text-align: right !important;
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
');

$items = count($this->item->revisiondetalle) ? $this->item->revisiondetalle : $this->item->cotizaciondetalle;

$simbolo = in_array($this->cliente->COD_LISTA, SabullvialHelper::LISTAS_DOLAR) ? 'COM_SABULLVIAL_DOLAR_SIMBOLO' : 'COM_SABULLVIAL_PESO_SIMBOLO';
$simbolo = JText::_($simbolo);

$model = $this->getModel();
$estadosHistorico = $model->getCotizacionHistorico($this->item->id);
$estadosTangoHistorico = $model->getCotizacionTangoHistorico($this->item->id);

JFormHelper::loadFieldClass('EstadoCotizacionTango');

$sitCliente = Table::getInstance('SitClientes', 'SabullvialTable');
$sitCliente->loadByCodCliente($this->item->id_cliente);

/** @var SabullvialTableBullvialSitTransportes $tableTransporte */
$tableTransporte = Table::getInstance('BullvialSitTransportes', 'SabullvialTable');
$tableTransporte->loadByCodigoTransporte($this->item->id_transporte);
?>
<div id="modalApproveApp" class="container-popup">
    <div class="form">
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <table class="table table-striped table-bordered">
                    <?php /*<tr>
                        <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_PEDIDO'); ?></th>
                        <td><?php echo $this->item->id; ?></td>
                    </tr>
                    <tr>
                        <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_FECHA'); ?></th>
                        <td><?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC5')); ?>hs</td>
                    </tr>*/?>
                    <tr>
                        <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_CLIENTE'); ?></th>
                        <td><?php echo $this->item->cliente; ?></td>
                    </tr>
                    <tr>
                        <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_VENDEDOR'); ?></th>
                        <td><?php echo $this->item->created_by_alias; ?></td>
                    </tr>
                    <tr>
                        <th class="text-left">
                            Plazo de Entrega
                        </th>
                        <td>
                            <?php echo $this->item->delivery_term; ?>
                        </td>
                    </tr>
                    <?php if (SabullvialHelper::getConfig('VAL_OFF')) : ?>
                        <tr>
                            <th class="text-left">
                                Mantenimiento de oferta:
                            </th>
                            <td>
                                <?php echo SabullvialHelper::getConfig('VAL_OFF'); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php if ($sitCliente): ?>
                        <tr>
                            <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_SALDO_EN_C_C'); ?></th>
                            <td><?php echo PriceHelper::format($sitCliente->SALDO_CC);?></td>
                        </tr>
                        <tr>
                            <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_CREDITO_DISPONIBLE'); ?></th>
                            <td><?php echo PriceHelper::format($sitCliente->CREDITO_DISPONIBLE);?></td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_TRANSPORTE'); ?></th>
                        <td><?php echo $tableTransporte->getFullName(); ?></td>
                    </tr>
                    <tr>
                        <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_NOTA_INTERNA'); ?></th>
                        <td><?php echo $this->item->note; ?></td>
                    </tr>
                    <tr>
                        <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_OBSERVACIONES'); ?></th>
                        <td><?php echo $this->item->observations; ?></td>
                    </tr>
                </table>
            </div>

            <div class="span6">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_FORMA_DE_PAGO'); ?></th>
                            <td>
                                <span class="hasTooltip" data-title="Forma de Pago">
                                    <?php echo $this->item->condicionventaFake; ?>
                                </span>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="muted">Tango</td>
                            <td>
                                <span class="hasTooltip" data-title="Forma de Pago Tango">
                                    <?php echo $this->item->condicionventa; ?>
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_ESTADO'); ?></th>
                            <td>
                                <?php
                                    $estadoCreatedBy = $estadosHistorico[0]->created_by_alias;
                                    $estadoCreated = $estadosHistorico[0]->created > 0 ? JHtml::_('date', $estadosHistorico[0]->created, JText::_('DATE_FORMAT_LC5')) . 'hs' : '-'
                                ?>
                                <span class="label" style="background-color: <?php echo $estadosHistorico[0]->bg_color; ?>; color: <?php echo $estadosHistorico[0]->color; ?>">
                                    <?php echo $estadosHistorico[0]->estado; ?>
                                </span> <?php echo Text::sprintf('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_ESTADO_TANGO_TEXT', $estadoCreatedBy, $estadoCreated); ?>
                            </td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="muted">Tango</td>
                            <td>
                                <?php if (count($estadosTangoHistorico)) : ?>
                                    <?php
    $estadoCreatedBy = $estadosTangoHistorico[0]->created_by_alias;
                                    $estadoCreated = $estadosTangoHistorico[0]->created > 0 ? JHtml::_('date', $estadosTangoHistorico[0]->created, JText::_('DATE_FORMAT_LC5')) . 'hs' : '-'
                                    ?>
                                    <span class="label" style="background-color: <?php echo $estadosTangoHistorico[0]->bg_color; ?>; color: <?php echo $estadosTangoHistorico[0]->color; ?>">
                                        <?php echo $estadosTangoHistorico[0]->estado; ?>
                                    </span> <?php echo Text::sprintf('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_ESTADO_TANGO_TEXT', $estadoCreatedBy, $estadoCreated); ?>
                                <?php else : ?>
                                    <?php $estadoTango = JFormFieldEstadoCotizacionTango::findById($this->item->id_estado_tango); ?>
                                    <span class="label" style="background-color: <?php echo $estadoTango['background_color']; ?>; color: <?php echo $estadoTango['color']; ?>">
                                        <?php echo $estadoTango['text']; ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row-fluid">

            <div class="span12">
                <table class="table table-bordered table-striped table-condensed">
                    <thead>
                        <tr>
                            <th>Descripción</th>
                            <th class="text-center-important">Precio Unitario</th>
                            <th class="text-center-important">Cant.</th>
                            <th class="text-center-important">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $k => $item) : ?>
                            <?php
                                if ($item['cantidad'] == 0 && $item['cantidad'] != $item['cantidad_requerida']) {
                                    continue;
                                }

                                $odd = ($k % 2 == 0 ? 'class="odd"' : '');
                            ?>
                            <tr <?php echo $odd; ?>>
                                <td><?php echo $item['nombre'] . ($item['marca'] ? ' - ' . $item['marca'] : ''); ?></td>
                                <td class="text-right-important"><?php echo $simbolo . ' ' . number_format($item['precio'], 2, ',', '.'); ?></td>
                                <td class="text-center-important"><?php echo $item['cantidad']; ?></td>
                                <td class="text-right-important">
                                    <?php echo $simbolo . ' ' . number_format($item['subtotal'], 2, ',', '.'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

        </div>

        <div class="row-fluid">

            <div class="span12">

                <p class="right">
                    Subtotal: <?php echo $simbolo . ' ' . number_format($this->item->subtotal, 2, ',', '.'); ?>
                    <br>

                    <?php if ($this->item->ivaTotal > 0) : ?>
                        IVA 21%: <?php echo $simbolo . ' ' . number_format($this->item->ivaTotal, 2, ',', '.'); ?>
                        <br>
                    <?php endif; ?>

                    <?php echo Text::sprintf('COM_SABULLVIAL_PUNTOS_DE_VENTA_IIBB', number_format($this->item->porcentaje_iibb ?: 0, 2, ',', '.')); ?>%: <?php echo $simbolo . ' ' . number_format($this->item->iibb, 2, ',', '.'); ?>
                    <br>

                    <b>TOTAL con IVA: <?php echo $simbolo . ' ' . number_format($this->item->total, 2, ',', '.'); ?></b>

                    <?php if (
                        in_array($this->cliente->COD_LISTA, SabullvialHelper::LISTAS_SIN_IVA) ||
                        (is_null(SabullvialHelper::getLabelListaDePrecio($this->cliente->COD_LISTA)) && $this->cliente->COD_LISTA != '1')
                    ) : ?>
                        <br>
                        <br>
                        <i>Atención los precios son más impuestos.</i>
                    <?php endif; ?>
                </p>

            </div>
        </div>

        <?php /*<div class="row-fluid">

            <div class="span6 pull-right well well-small">
                <div class="control-group">
                    <div class="control-label">
                        <?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_COMENTARIO'); ?>
                    </div>
                    <div class="controls">
                        <textarea v-model="comentarios" class="inputbox span12" rows="6" name="modal_approve_comentarios" placeholder="<?php echo Text::_('COM_SABULLVIAL_COTIZACION_MODAL_APPROVE_COMENTARIO_PLACEHOLDER'); ?>"></textarea>
                    </div>
                </div>
            </div>
        </div>*/?>

    </div>
</div>