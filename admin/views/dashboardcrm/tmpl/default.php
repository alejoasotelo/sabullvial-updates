<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JTable::getInstance('Tarea', 'SabullvialTable');

$cmpParams = SabullvialHelper::getComponentParams();
$daysToConsiderExpired = $cmpParams->get('tareas_dias_alerta_expiracion', 3);

JHTML::_('bootstrap.tooltip', '.hasTooltip');

$user = Factory::getUser();
$vendedor = SabullvialHelper::getVendedor();

$isClienteRevendedor = $vendedor->get('esRevendedor', false);
$showOnlyOwn = $isClienteRevendedor || !$vendedor->get('ver.presupuestos', false);

$cmpParams = SabullvialHelper::getComponentParams();
$diasUltimaCompra = (int)$cmpParams->get('dashboard_crm_dias_ultima_compra', 90);
?>
<div class="j-sidebar-container j-sidebar-visible">
    <div class="well well-small">
        <h2 class="nav-header pl-sm-2-i pr-sm-2-i"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_AGENDA_DE_TAREAS'); ?></h2>
        <?php foreach ($this->tareas as $tarea) : ?>
            <?php echo JLayoutHelper::render('joomla.content.tareas.card-crm', ['tarea' => $tarea]); ?>
        <?php endforeach; ?>
    </div>
</div>
<div id="j-main-container" class="span10 j-toggle-main com_cpanel">
    <div class="span4">
        <div class="well well-small mb-2">
            <h2 class="module-title nav-header pl-sm2-i pr-sm-2-i"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_PRODUCTOS_MAS_VENDIDOS'); ?></h2>
            <div class="row-striped">
                <?php foreach ($this->productos as $producto) : ?>
                    <div class="row-fluid">
                        <div class="span2">
                            <?php if (count($producto->images)) : ?>
                                <?php
                                $image = array_values($producto->images)[0];
                                $src = JUri::root() . $image['path'];
                                ?>
                                <a href="#" onclick="showCarousel(<?php echo htmlspecialchars(json_encode($producto->images)); ?>, event)">
                                    <img src="<?php echo $src; ?>" width="38" height="38" class="img-producto" loading="lazy" />
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="span3">
                            <?php echo $producto->codigo_sap; ?>
                            <div class="small left muted">
                                <?php echo $producto->marca; ?><br />
                            </div>
                        </div>
                        <div class="span6">
                            <b class="text-info"><?php echo $this->escape($producto->nombre); ?></b>
                        </div>
                        <div class="text-right small"><?php echo $producto->cantidad_total; ?> <span class="muted">ventas</span></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="well well-small">
            <h2 class="module-title nav-header pl-sm2-i pr-sm-2-i"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_RANKING_DE_VENDEDORES'); ?></h2>
            <div class="row-striped">
                <div class="row-fluid">
                    <div class="span4">
                        <b><small>Vendedor</small></b>
                    </div>
                    <div class="span2 text-center">
                        <span class="icon-stack cotizacion hasTooltip" title="<?php echo Text::_('COM_SABULLVIAL_VENDEDORES_COTIZACIONES'); ?>"></span>
                    </div>
                    <div class="span2 text-center">
                        <span class="icon-publish hasTooltip" title="<?php echo Text::_('COM_SABULLVIAL_VENDEDORES_COTIZACIONES_APROBADAS'); ?>"></span>
                    </div>
                    <div class="span2 text-center">
                        <span class="icon-unpublish hasTooltip" title="<?php echo Text::_('COM_SABULLVIAL_VENDEDORES_COTIZACIONES_SIN_CONCRETAR'); ?>"></span>
                    </div>
                    <div class="span2 text-center">
                        <span class="icon-bars hasTooltip" title="<?php echo Text::_('COM_SABULLVIAL_VENDEDORES_TASA_EFECTIVIDAD'); ?>"></span>
                    </div>
                </div>
                <?php foreach ($this->vendedores as $vendedor) : ?>
                    <div class="row-fluid">
                        <div class="span4">
                            <?php echo $this->escape($vendedor->name); ?>
                        </div>
                        <div class="span2 text-center">
                            <span class="badge <?php echo $vendedor->cotizaciones > 0 ? 'badge-info' : ''; ?>">
                                <?php echo $vendedor->cotizaciones; ?>
                            </span>
                        </div>
                        <div class="span2 text-center">
                            <span class="badge <?php echo $vendedor->cotizaciones_aprobadas > 0 ? 'badge-success' : ''; ?>">
                                <?php echo $vendedor->cotizaciones_aprobadas; ?>
                            </span>
                        </div>
                        <div class="span2 text-center">
                            <span class="badge <?php echo $vendedor->cotizaciones_sin_concretar > 0 ? 'badge-warning' : ''; ?>">
                                <?php echo $vendedor->cotizaciones_sin_concretar; ?>
                            </span>
                        </div>
                        <div class="span2 text-center">
                            <span class="badge <?php echo $vendedor->cotizaciones_efectividad > 0 ? 'badge-info' : ''; ?>">
                                <?php echo Text::sprintf('COM_SABULLVIAL_VENDEDORES_TASA_EFECTIVIDAD_VALUE', round($vendedor->cotizaciones_efectividad * 100, 0)); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="span4">
        <div class="row-fluid mb-2">
            <div class="span6 offset3 card-tarea text-center">
                <h1 class="text-info"><?php echo PriceHelper::format($this->totalVentasRealizadas); ?></h1>
                <p>
                    <span class="label label-success"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_VENTAS_REALIZADAS'); ?></span>
                </p>
                <p class="muted"><?php echo Text::sprintf('COM_SABULLVIAL_DASHBOARD_CRM_VENTAS_TOTALES', $this->cantidadVentasRealizadas); ?></p>
            </div>
            <!-- <div class="span3 card-tarea text-center">
                <h1 class="text-info">$ 18.242.918,00</h1>
                <p>
                    <span class="label label-warning">Cobranzas totales</span>
                </p>
                <p class="muted">$ 4.000.000 sin cobrar</p>
            </div> -->            
        </div>

        
        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'clientes-sin-compras']); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'clientes-sin-compras', Text::sprintf('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTES_QUE_NO_COMPRAN', $diasUltimaCompra)); ?>
                <div class="well well-small">
                    <h2 class="module-title nav-header pl-sm2-i pr-sm-2-i">
                        <?php echo Text::sprintf('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTES_QUE_NO_COMPRAN', $diasUltimaCompra); ?>
                    </h2>

                    <div class="row-striped">
                        <div class="row-fluid">
                            <div class="span5 small">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTE'); ?></b>
                            </div>
                            <div class="span2 small text-center">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_ULTIMA_COMPRA'); ?></b>
                            </div>
                            <div class="span2 small text-center">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_SALDO'); ?></b>
                            </div>
                            <div class="span3 small text-center">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_ASIGNAR_TAREA'); ?></b>
                            </div>
                        </div>
                        <?php foreach ($this->clientesUltimaCompra as $cliente) : ?>
                            <?php
                                $link = JRoute::_('index.php?option=com_sabullvial&task=tarea.add&id_cliente=' . $cliente->id . '&id_cotizacion=' . $cliente->id_ultima_cotizacion);
                                $linkCotizacion = JRoute::_('index.php?option=com_sabullvial&view=cotizaciones&filter[search]=id:' . $cliente->id_ultima_cotizacion);
                            ?>
                            <div class="row-fluid">
                                <div class="span5">
                                    <b class="text-info"><?php echo $this->escape($cliente->razon_social); ?></b>
                                    <div class="small">
                                        <span class="muted">Cod:</span> <?php echo $cliente->codcli; ?> |
                                        <span class="muted">Cuit:</span> <?php echo $cliente->cuit; ?> |
                                        <span class="muted">CV:</span> <?php echo $cliente->codigo_vendedor; ?>
                                    </div>
                                </div>
                                <div class="span2 text-center">
                                    <?php echo Text::sprintf('COM_SABULLVIAL_DASHBOARD_CRM_DIAS_ULTIMA_COMPRA', $cliente->dias_ultima_compra); ?>
                                    <?php if (!$showOnlyOwn || $user->id == $cliente->cotizacion_created_by): ?>
                                    <div class="small">
                                        <span class="muted">Pedido:</span> <a href="<?php echo $linkCotizacion; ?>" target="_blank"><?php echo $cliente->id_ultima_cotizacion; ?></a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="span2 text-center">
                                    <?php echo PriceHelper::format($cliente->saldo); ?>
                                </div>
                                <div class="span3 text-right">
                                    <a href="<?php echo $link; ?>" target="_blank" class="btn btn-small">
                                        <span class="icon-pencil-2"></span> <?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_ASIGNAR_TAREA'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'clientes-mas-compran', JText::_('COM_SABULLVIAL_DASHBOARD_CRM_MAS_COMPRAN')); ?>
                <div class="well well-small">
                    <h2 class="module-title nav-header pl-sm2-i pr-sm-2-i">
                        <?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTES_QUE_MAS_COMPRAN'); ?>
                    </h2>
                    <div class="row-striped">
                        <div class="row-fluid">
                            <div class="span7 small">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTE'); ?></b>
                            </div>
                            <div class="span2 small text-center">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CANTIDAD_DE_COMPRAS'); ?></b>
                            </div>
                            <div class="span3 small text-right">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_TOTAL'); ?></b>
                            </div>
                        </div>
                        <?php foreach ($this->clientesQueMasCompran as $cliente) : ?>
                            <div class="row-fluid">
                                <div class="span7">
                                    <b class="text-info"><?php echo $this->escape($cliente->razon_social); ?></b>
                                    <div class="small">
                                        <span class="muted">Cod:</span> <?php echo $cliente->codcli; ?> |
                                        <span class="muted">Cuit:</span> <?php echo $cliente->cuit; ?> |
                                        <span class="muted">CV:</span> <?php echo $cliente->codigo_vendedor; ?>
                                    </div>
                                </div>
                                <div class="span2 text-center">
                                    <?php echo $cliente->cantidad_compras; ?>
                                </div>
                                <div class="span3 text-right">
                                    <?php echo PriceHelper::format($cliente->total); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'clientes-menos-compran', JText::_('COM_SABULLVIAL_DASHBOARD_CRM_MENOS_COMPRAN')); ?>
                <div class="well well-small">
                    <h2 class="module-title nav-header pl-sm2-i pr-sm-2-i">
                        <?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTES_QUE_MENOS_COMPRAN'); ?>
                    </h2>
                    <div class="row-striped">
                        <div class="row-fluid">
                            <div class="span7">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTE'); ?></b>
                            </div>
                            <div class="span2 small text-center">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CANTIDAD_DE_COMPRAS'); ?></b>
                            </div>
                            <div class="span3 small text-right">
                                <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_TOTAL'); ?></b>
                            </div>
                        </div>
                        <?php foreach ($this->clientesQueMenosCompran as $cliente) : ?>
                            <div class="row-fluid">
                                <div class="span7">
                                    <b class="text-info"><?php echo $this->escape($cliente->razon_social); ?></b>
                                    <div class="small">
                                        <span class="muted">Cod:</span> <?php echo $cliente->codcli; ?> |
                                        <span class="muted">Cuit:</span> <?php echo $cliente->cuit; ?> |
                                        <span class="muted">CV:</span> <?php echo $cliente->codigo_vendedor; ?>
                                    </div>
                                </div>
                                <div class="span2 text-center">
                                    <?php echo $cliente->cantidad_compras; ?>
                                </div>
                                <div class="span3 text-right">
                                    <?php echo PriceHelper::format($cliente->total); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
            <?php // echo JHtml::_('bootstrap.addTab', 'myTab', 'clientes-mas-deudores', JText::_('COM_SABULLVIAL_DASHBOARD_CRM_MAS_DEUDORES'));?>
            <?php // echo JHtml::_('bootstrap.endTab');?>
        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>
    <div class="span4">
        <div class="well well-small">
            <h2 class="module-title nav-header pl-sm2-i pr-sm-2-i">
                <?php echo Text::sprintf('COM_SABULLVIAL_DASHBOARD_CRM_VENTAS_REALIZADAS', $diasUltimaCompra); ?>
            </h2>

            <div class="row-striped">
                <div class="row-fluid">
                    <div class="span2 small text-center">
                        <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_PEDIDO'); ?></b>
                    </div>
                    <div class="span5 small text-left">
                        <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CLIENTE'); ?></b>
                    </div>
                    <div class="span3 small text-center">
                        <b><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_TOTAL'); ?></b>
                    </div>
                    <div class="span2 small">
                        <b><?php echo Text::_('COM_SABULLVIAL_CREATED_DATE'); ?></b>
                    </div>
                </div>
                <?php foreach ($this->ventasRealizadas as $cotizacion) : ?>
                    <?php
                        // $link = JRoute::_('index.php?option=com_sabullvial&task=tarea.add&id_cliente=' . $cliente->id . '&id_cotizacion=' . $cliente->id_ultima_cotizacion);
                        $linkCotizacion = JRoute::_('index.php?option=com_sabullvial&view=cotizaciones&filter[search]=id:' . $cotizacion->id);
                    ?>
                    <div class="row-fluid">
                        <div class="span2 text-center">
                            <?php if (!$showOnlyOwn || $user->id == $cotizacion->cotizacion_created_by): ?>
                                <a href="<?php echo $linkCotizacion; ?>" target="_blank" class="hasTooltip" title="<?php echo JText::_('JACTION_VIEW_COTIZACION'); ?>"><?php echo $cotizacion->id; ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="span5">
                            <b class="text-info"><?php echo $this->escape($cotizacion->cliente); ?></b>
                            <div class="small">
                                <?php if ($cotizacion->id_cliente && $cotizacion->id_cliente != '000000'): ?>
                                    <span class="hasTooltip" data-title="<?php echo JText::_('COM_SABULLVIAL_CLIENTES_RAZON_SOCIAL'); ?>">RS</span>: <a href="<?php echo $linkCotizacion; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT_COTIZACION'); ?>"><?php echo $cotizacion->razon_social; ?></a> | 
                                    <span class="muted">Cod:</span> <?php echo $cotizacion->codcli; ?> |
                                    <span class="muted"><?php echo JText::_('COM_SABULLVIAL_CLIENTES_DOCUMENTO_TIPO_' . $cotizacion->documento_tipo); ?>:</span> <?php echo $cotizacion->documento_numero; ?> |
                                    <span class="muted">CV:</span> <?php echo $cotizacion->codigo_vendedor; ?>
                                <?php else: ?>
                                    <span class="hasTooltip" data-title="<?php echo JText::_('COM_SABULLVIAL_CLIENTES_CONSUMIDOR_FINAL'); ?>">CF</span>
                                    <?php if (!empty($cotizacion->documento_numero)): ?>
                                        | <span class="muted"><?php echo JText::_('COM_SABULLVIAL_CLIENTES_DOCUMENTO_TIPO_' . $cotizacion->documento_tipo); ?>:</span> <?php echo $cotizacion->documento_numero; ?>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="span3 text-center">
                            <?php echo PriceHelper::format($cotizacion->total_revision > 0 ? $cotizacion->total_revision : $cotizacion->total); ?>
                        </div>
                        <div class="span2 nowrap small">
                            <?php echo $cotizacion->created > 0 ? JHtml::_('date', $cotizacion->created, JText::_('DATE_FORMAT_LC5')) : '-'; ?>
                            <br/>
                            <span class="muted"><?php echo JText::_('JAUTHOR');?>:</span> <?php echo $cotizacion->author; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>