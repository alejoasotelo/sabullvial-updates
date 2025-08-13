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

JHtml::_('formbehavior.chosen', 'select:not(.no-select2,.no-chosen)');

JTable::getInstance('Tarea', 'SabullvialTable');

$cmpParams = SabullvialHelper::getComponentParams();
$daysToConsiderExpired = $cmpParams->get('tareas_dias_alerta_expiracion', 3);

JHTML::_('bootstrap.tooltip', '.hasTooltip');
JHtml::_('bootstrap.popover');

$user = Factory::getUser();
$vendedor = SabullvialHelper::getVendedor();

$isClienteRevendedor = $vendedor->get('esRevendedor', false);
$showOnlyOwn = $isClienteRevendedor || !$vendedor->get('ver.presupuestos', false);

$cmpParams = SabullvialHelper::getComponentParams();
$diasUltimaCompra = (int)$cmpParams->get('dashboard_crm_dias_ultima_compra', 90);

$document = Factory::getDocument();
$document->addScriptDeclaration('
    jQuery(document).ready(function() {
        jQuery(".ordering-select, .js-stools-container-list").removeClass("hidden-phone").removeClass("hidden-tablet");
    });
');
$document->addStyleDeclaration('
    #list_fullordering_chzn { display: none; }
    .cursor-pointer { cursor: pointer; }
');
?>
<form action="index.php?option=com_sabullvial&view=dashboardscrm" method="post" id="adminForm" name="adminForm" v-on:submit.prevent="preventSubmit">
    <div id="j-main-container" class="span12 com_cpanel">
        <div class="row-fluid">
            <div class="span12">
                <?php
                    echo JLayoutHelper::render(
                        'vue.searchtools.default',
                        [
                            'view' => $this, 
                            'options' => [
                                'filterButton' => true, 
                                'vModel' => 'activeFilters.search',
                                'searchButton' => false,
                                'onSearchClick' => 'submitFilter',
                                'onClearClick' => 'clearFilter',
                                'vModelFilters' => [
                                    'codigo_vendedor' => [
                                         'vModel' => 'activeFilters.codigo_vendedor',
                                         'onChange' => 'changeSearch',
                                    ],
                                    'codigo_cliente' => [
                                        'vModel' => 'activeFilters.codigo_cliente',
                                        'onChange' => 'changeSearch',
                                    ],
                                    'date_from' => [
                                        'vModel' => 'activeFilters.date_from',
                                        'onChange' => 'changeSearch',
                                    ],
                                    'date_to' => [
                                        'vModel' => 'activeFilters.date_to',
                                        'onChange' => 'changeSearch',
                                    ],
                                    'total_from' => [
                                        'vModel' => 'activeFilters.total_from',
                                        'onChange' => 'changeSearch',
                                    ],
                                    'total_to' => [
                                        'vModel' => 'activeFilters.total_to',
                                        'onChange' => 'changeSearch',
                                    ],
                                ],
                            ]
                        ]
                    );
                ?>
                <?php /*echo JLayoutHelper::render('joomla.searchtools.withoutsearch', [
                    'view' => $this,
                ]); */?>
            </div>
        </div>
        <div class="row-fluid" :class="{'container-loading': loading}">
            <div class="span12">
                <fieldset class="form-horizontal">
                    <legend><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_REALIZADAS_VENDIDAS_Y_RECHAZADAS'); ?></legend>
                    <p class="mb-2"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_REALIZADAS_VENDIDAS_Y_RECHAZADAS_DESC'); ?></p>
                    <div class="row-fluid mb-2">
                        <div class="span3 mb-2">
                            <card-budget
                                session-key="realizadas"
                                :current-amount="budgetValues.realizadas"
                                class="text-center"
                                :title="priceFormat(cotizacionesRealizadasTotal)"
                                title-info="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_REALIZADAS_DESC'); ?>"
                                badge="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_REALIZADAS'); ?>"
                                badge-type="info"
                                :description="sprintf('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_REALIZADAS_CANTIDAD', cotizaciones.realizadas.cantidad)"
                                :target-amount="cotizaciones.realizadas.total"
                            ></card-budget>
                        </div>
                        <div class="span3 mb-2">
                            <card-budget
                                session-key="vendidas"
                                :current-amount="budgetValues.vendidas"
                                class="text-center"
                                :title="priceFormat(cotizacionesVendidasTotal)"
                                title-info="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_VENDIDAS_DESC'); ?>"
                                badge="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_VENDIDAS'); ?>"
                                badge-type="success"
                                :description="sprintf('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_VENDIDAS_CANTIDAD', cotizaciones.vendidas.cantidad)"
                                :target-amount="cotizaciones.vendidas.total"
                            ></card-budget>
                        </div>

                        <div class="span3 mb-2">
                            <card-budget
                                session-key="rechazadas"
                                :current-amount="budgetValues.rechazadas"
                                class="text-center"
                                :title="priceFormat(cotizacionesRechazadasTotal)"
                                title-info="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_RECHAZADAS_DESC'); ?>"
                                badge="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_RECHAZADAS'); ?>"
                                badge-type="danger"
                                :description="sprintf('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_RECHAZADAS_CANTIDAD', cotizaciones.rechazadas.cantidad)"
                                :target-amount="cotizaciones.rechazadas.total"
                            ></card-budget>
                        </div>

                        <div class="span3">
                            <div class="row mb-1">
                                <canvas id="chartCotizaciones" style="max-height: 300px;"></canvas>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <fieldset class="cotizaciones-todas-container form-horizontal">
                    <legend><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_TODAS'); ?></legend>
                    <p class="mb-2"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_COTIZACIONES_TODAS_DESC'); ?></p>

                    <div>
                        <div class="span6">
                            <table class="table table-striped table-hover table-bordered table-footer-totals">
                                <thead>
                                    <tr>
                                        <th>
                                            <a href="#" @click.prevent="sortBy('estado')" class="hasPopover"
                                                 title="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_ESTADO'); ?>"
                                                 data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>"
                                                 data-placement="right"
                                                 >
                                                <?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_ESTADO'); ?>
                                                <span v-if="sortField === 'estado'" :class="sortOrder === 'asc' ? 'icon-arrow-up-3' : 'icon-arrow-down-3'"></span>
                                            </a>
                                        </th>
                                        <th width="100px">
                                            <a href="#" @click.prevent="sortBy('cantidad')" class="hasPopover"
                                                 title="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CANTIDAD'); ?>"
                                                 data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>"
                                                 data-placement="top"
                                                 >
                                                <?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_CANTIDAD'); ?>
                                                <span v-if="sortField === 'cantidad'" :class="sortOrder === 'asc' ? 'icon-arrow-up-3' : 'icon-arrow-down-3'"></span>
                                            </a>
                                        </th>
                                        <th width="100px" class="text-right">
                                            <a href="#" @click.prevent="sortBy('total')" class="hasPopover"
                                                 title="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_PORCENTAJE'); ?>"
                                                 data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>"
                                                 data-placement="top"
                                                 >
                                                <?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_PORCENTAJE'); ?>
                                                <span v-if="sortField === 'porcentaje'" :class="sortOrder === 'asc' ? 'icon-arrow-up-3' : 'icon-arrow-down-3'"></span>
                                            </a>
                                        </th>
                                        <th width="200px" class="text-right">
                                            <a href="#" @click.prevent="sortBy('total')" class="hasPopover"
                                                 title="<?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_TOTAL'); ?>"
                                                 data-content="<?php echo Text::_('JGLOBAL_CLICK_TO_SORT_THIS_COLUMN'); ?>"
                                                 data-placement="top"
                                                 >
                                                <?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_TOTAL'); ?>
                                                <span v-if="sortField === 'total'" :class="sortOrder === 'asc' ? 'icon-arrow-up-3' : 'icon-arrow-down-3'"></span>
                                            </a>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="cotizacion in sortedCotizaciones" :key="cotizacion.id_estadocotizacion">
                                        <td>
                                            <span class="label" :style="{ backgroundColor: cotizacion.bg_color, color: cotizacion.color }">
                                                {{ cotizacion.estado }}
                                            </span>
                                        </td>
                                        <td class="text-muted text-right">{{ cotizacion.cantidad || 0 }}</td>
                                        <td class="text-right text-muted">{{ getPercentage(cotizacion.total || 0, cotizacionesTodasTotal, 2) }}%</td>
                                        <td class="text-right">{{ priceFormat(cotizacion.total || 0) }}</td>
                                    </tr>
                                    <tr v-if="!cotizacionesTodas.length">
                                        <td colspan="4"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_NO_HAY_COTIZACIONES'); ?></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td class="font-bold"><?php echo Text::_('COM_SABULLVIAL_DASHBOARD_CRM_TOTAL'); ?></td>
                                        <td class="font-bold text-muted text-right">{{ cotizacionesTodasCantidad }}</td>
                                        <td class="font-bold text-right text-muted">100%</td>
                                        <td class="font-bold text-right">{{ priceFormat(cotizacionesTodasTotal, 0) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="span6">
                            <canvas id="chartCotizacionesTodas" style="max-height: 532px;"></canvas>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo JHtml::_('form.token'); ?>
</form>
