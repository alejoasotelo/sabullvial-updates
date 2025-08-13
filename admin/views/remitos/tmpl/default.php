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

use Joomla\CMS\Factory;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

JHtml::_('bootstrap.framework');
JHtml::_('bootstrap.modal');
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleClientes', null, ['placeholder_text_multiple' => Text::_('JOPTION_SELECT_CLIENTE')]);
JHtml::_('formbehavior.chosen', '.multipleExpresos', null, ['placeholder_text_multiple' => Text::_('JOPTION_SELECT_CLIENTE')]);
JHtml::_('formbehavior.chosen', 'select:not(.no-chosen)');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

/** @var HtmlDocument $doc */
$doc = Factory::getDocument();
$doc->addStyleDeclaration('
    .monto-total{
        cursor: default;
        border: 0px;
        background: 0px;
        box-shadow: none;
    }

    .select2-results__option--highlighted .text-info {
        color: white;
    }

    .margin-remove{
        margin: 0px !important;
    }

    @media (max-width: 767px) {
        .table td.left-phone,
        .left-phone{
            text-align: left;
        }
    }
');

$isAdministrador = SabullvialHelper::isUserAdministrador();
?>
<form action="index.php?option=com_sabullvial&view=remitos" v-on:submit.prevent="preventSubmit" method="post" id="adminForm" name="adminForm" class="form-validate">
    <div id="j-sidebar-container" class="span2">
        <?php echo JHtmlSidebar::render(); ?>
    </div>
    <div id="j-main-container" class="span10">		
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
                                'onSearchClick' => 'submitFilter',
                                'onClearClick' => 'clearFilter',
                                'vModelFilters' => [
                                    'estado' => [
                                        'vModel' => 'activeFilters.estado',
                                        'onChange' => 'changeSearch',
                                    ],
                                    'date_from' => [
                                        'vModel' => 'activeFilters.date_from',
                                        'onChange' => 'changeSearch',
                                    ],
                                    'date_to' => [
                                        'vModel' => 'activeFilters.date_to',
                                        'onChange' => 'changeSearch',
                                    ]
                                ],
                            ]
                        ]
                    );
                ?>
            </div>
        </div>
        <div v-if="!loading && remitos.length == 0" class="alert alert-no-items">
            <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
        </div>
            <table v-else class="table table-striped table-hover" :class="{'table-loading': loading}">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap">
                            <input type="checkbox" class="hasTooltip" @click="selectAll()" title="Marcar todos los elementos" />
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_ESTADO',
                                    'onClick' => "sortRemitos('er.nombre')",
                                    'order' => 'er.nombre',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th width="5%" class="nowrap center">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_NUMERO_REMITO',
                                    'onClick' => "sortRemitos('r.N_REMITO')",
                                    'order' => 'r.N_REMITO',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th width="5%" class="center">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_NUMERO_FACTURA',
                                    'onClick' => "sortRemitos('f.NCOMP_FAC')",
                                    'order' => 'f.NCOMP_FAC',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th width="5%" class="nowrap center hidden-phone">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_ID_COTIZACION',
                                    'onClick' => "sortRemitos('co.id')",
                                    'order' => 'co.id',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th style="min-width:100px" class="nowrap hidden-phone">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_CLIENTE',
                                    'onClick' => "sortRemitos('cliente')",
                                    'order' => 'cliente',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th class="nowrap hidden-phone">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_EXPRESO',
                                    'onClick' => "sortRemitos('expreso')",
                                    'order' => 'expreso',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_DIRECCION_ENTREGA',
                                    'onClick' => "sortRemitos('direccion_entrega')",
                                    'order' => 'direccion_entrega',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <?php if ($isAdministrador) : ?>
                            <th width="5%" class="nowrap hidden-phone">
                                <?php
                                    echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                        'title' => 'COM_SABULLVIAL_REMITOS_MONTO_REMITO',
                                        'onClick' => "sortRemitos('monto_remito')",
                                        'order' => 'monto_remito',
                                        'orderSelected' => 'activeFilters.ordering',
                                        'direction' => 'activeFilters.direction',
                                    ]);
                                ?>
                            </th>
                        <?php endif; ?>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_FECHA_ENTREGA',
                                    'onClick' => "sortRemitos('delivery_date')",
                                    'order' => 'delivery_date',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th width="5%" class="nowrap hidden-phone">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_REMITOS_FECHA_REMITO',
                                    'onClick' => "sortRemitos('fecha_remito')",
                                    'order' => 'fecha_remito',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo $isAdministrador ? 11 : 10;?>">
                            <div class="pagination">
                                <ul id="vue-pagination" class="pagination-list pagination-toolbar"></ul>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <tbody>					
                    <tr v-for="(remito, index) in remitos" :key="remito.id" @click="toggleRemito(remito)" :class="{'info': remito.selected}">
                        <td class="center">
                            <span v-if="!remito.entregado">
                                <input type="checkbox" :id="'cb'+remito.id" :value="remito.id" @click="toggleRemito(remito, $event)" :checked="remito.selected" />
                            </span>
                        </td>
                        <td class="left hidden-phone">
                            <label-estado
                                :key="'label-estado-remito-' + remito.id"
                                :background-color="remito.estadoremito_bg_color"
                                :color="remito.estadoremito_color"
                            >
                                {{remito.estadoremito}}
                                <span v-if="(remito.estadoremito_entregado == 1 || remito.estadoremito_entregado_mostrador == 1) && remito.delivery_date">
                                    el {{ formatDate(remito.delivery_date) }}
                                </span>
                            </label-estado>
                        </td>
                        <td class="text-left left-phone">
                            <a @click="showModalDetailRemito(remito, $event)" class="hasTooltip cursor-pointer" title="<?php echo Text::_('JACTION_VIEW_DETAIL'); ?>">
                                {{remito.numero_remito}}
                            </a>

                            <div class="text-left small">
                                <div v-if="remito.id_hojaderuta" class="small">
                                    <?php echo Text::_('COM_SABULLVIAL_REMITOS_HOJA_DE_RUTA'); ?>: 
                                    <span class="hasTooltip" :title="sprintf('COM_SABULLVIAL_REMITOS_HOJA_DE_RUTA_DESC', remito.id_hojaderuta, formatDate(remito.hojaderuta_delivery_date), remito.hojaderuta_chofer, remito.hojaderuta_patente)">{{remito.id_hojaderuta}}</span>
                                </div>
                            </div>

                            <div class="visible-phone text-left small">
                                <span class="label" :style="'background-color: ' + remito.estadoremito_bg_color + '; color: '+  remito.estadoremito_color">
                                    {{remito.estadoremito}}
                                </span>
                                </br>
                                <template v-if="remito.id_cotizacion">
                                    <span class="cotizacion">
                                        <span class="muted">Pedido:</span> {{remito.id_cotizacion}}
                                    </span>
                                    </br>
                                </template>
                                <span class="cliente">
                                    <span class="muted">Cliente:</span> {{remito.cliente ? remito.cliente : '-'}}
                                </span>
                                </br>
                                <span class="expreso">
                                    <span class="muted">Expreso:</span> {{remito.expreso ? remito.expreso : '-'}}
                                </span>
                                <?php if ($isAdministrador): ?>
                                    </br>
                                    <span class="monto">
                                        <span class="muted">Monto:</span> {{remito.montoRemitoFormated}}
                                    </span>
                                <?php endif; ?>
                                </br>
                                <span class="fecha-remito">
                                    <span class="muted">Fecha remito:</span> {{ formatDate(remito.fecha_remito) }}
                                </span>
                            </div>
                        </td>
                        <td>
                            {{remito.numero_factura}}
                        </td>
                        <td class="center hidden-phone">
                            {{remito.id_cotizacion}}
                        </td>
                        <td class="hidden-phone">
                            {{remito.cliente}}
                        </td>
                        <td class="small hidden-phone">
                            {{remito.expreso}}
                        </td>
                        <td class="nowrap small hidden-phone">
                            {{remito.direccion_entrega}}
                        </td>
                        <?php if ($isAdministrador) : ?>
                            <td class="nowrap small hidden-phone">
                                {{remito.montoRemitoFormated}}
                            </td>
                        <?php endif; ?>
                        <td class="nowrap small hidden-phone">
                            {{ formatDate(remito.delivery_date) }}
                        </td>
                        <td class="nowrap small hidden-phone">
                            {{ formatDate(remito.fecha_remito) }}
                        </td>
                    </tr>
                    <tr v-if="remitos.length == 0">
                        <td colspan="<?php echo $isAdministrador ? 11 : 10;?>" class="center">
                            <span v-if="loading">
                                <?php echo Text::_('COM_SABULLVIAL_REMITOS_CARGANDO'); ?>
                            </span>
                            <span v-else>
                                <?php echo Text::_('COM_SABULLVIAL_REMITOS_SIN_REMITOS'); ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>

        <input type="hidden" name="task" value="" />
        <input type="hidden" name="boxchecked" value="0" />
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
    
<?php
    echo JHtml::_(
        'bootstrap.renderModal',
        'modalGenerateRouteSheet',
        [
            'title' => Text::_('COM_SABULLVIAL_REMITOS_BATCH_TITLE'),
            'footer' => $this->loadTemplate('generate_route_sheet_footer')
        ],
        $this->loadTemplate('generate_route_sheet_body')
    );
?>
        
<?php
    echo JHtml::_(
        'bootstrap.renderModal',
        'modalDeliveredByCounter',
        [
                'title' => Text::_('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_TITLE'),
                'footer' => $this->loadTemplate('delivered_by_counter_footer'),
                'modalWidth' => 90
            ],
        $this->loadTemplate('delivered_by_counter_body')
    );
?>
        
<?php
    echo JHtml::_(
        'bootstrap.renderModal',
        'modalAddRemitoToRouteSheet',
        [
            'title' => Text::_('COM_SABULLVIAL_REMITOS_ADD_REMITO_TO_ROUTE_SHEET_TITLE'),
            'footer' => $this->loadTemplate('add_remito_to_route_sheet_footer'),
            'modalWidth' => 60
        ],
        $this->loadTemplate('add_remito_to_route_sheet_body')
    );
?>

<?php
    echo JHtml::_(
        'bootstrap.renderModal',
        'modalDetailRemito',
        [
            'title' => Text::_('COM_SABULLVIAL_REMITOS_MODAL_DETAIL_REMITO_VIEW_TITLE'),
            'bodyHeight' => 70,
            'modalWidth' => 60,
            'footer' => $this->loadTemplate('modal_remito_footer'),
        ],
        $this->loadTemplate('modal_remito_body')
    );
?>