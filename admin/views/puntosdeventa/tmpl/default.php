<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$document = Factory::getDocument();

$document->addScriptDeclaration("

    function closeModalByBackButton(modalId) {
        const \$modal = jQuery(modalId);
    
        const historyListener = (e) => {
            e.preventDefault();
            \$modal.modal('hide');
            const url = new URL(window.location);
            url.hash = '';
            window.history.replaceState(null, '', url);
        }

        \$modal.on('show', function (e) {
            if (e.target !== this) {
                return;
            }

            window.history.pushState(modalId, 'Modal title', document.location + modalId);
            window.addEventListener('popstate', historyListener, false);
        });

        \$modal.on('hide', function (e) {
            if (e.target !== this) {
                return;
            }
            
            if (window.history.state === modalId ) {
                history.back();
            }
            
            window.removeEventListener('popstate', historyListener);
        });
    }

    jQuery(document).ready(function() {
        jQuery('#table tbody tr td:not(:has(input))').off('click').click(function(e) {
            var el = jQuery(this);
            var \$input = el.parent().find('input');
            \$input.prop('checked', !\$input.prop('checked'));
        });

        closeModalByBackButton('#myCotizaciones');
        closeModalByBackButton('#selectClientecliente');
        closeModalByBackButton('#previewCotizacion');
        closeModalByBackButton('#afterCreatedCotizacion');
        
        const url = new URL(window.location);
        url.hash = '';
        window.history.replaceState(null, '', url);
    });
");

$document->addStyleDeclaration("
    #modalProductoCarousel .splide {
        padding-left: 12px;
        padding-right: 12px;
    }

    .splide__pagination__page.is-active{
        background: #2384d3;
    }

    .splide__slide__container{
        text-align: center;
    }
");

$consumidorFinal = [
    'id' => '000000',
    'text' => '<b>Consumidor Final</b>',
    'codigo_cliente' => '000000',
    'razon_social' => 'Consumidor Final',
    'cuit' => '',
    'saldo' => ''
];

$vendedor = SabullvialHelper::getVendedor();
$isRevendedor = $vendedor->get('esRevendedor', false);

JText::script('COM_SABULLVIAL_CONSUMIDOR_FINAL');
JText::script('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_TITLE');
JText::script('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_1');
JText::script('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_2');
JText::script('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_3');

?>
<form action="index.php?option=com_sabullvial&view=puntosdeventa" method="post" id="adminForm" name="adminForm">

    <div id="j-sidebar-container" class="j-sidebar-container j-sidebar-visible">

        <div class="container-fluid form-vertical" :class="{'has-cliente': quotation.cliente != '' && transportes.length}">

            <div class="control-group">
                <div class="control-label">
                    <label>Cliente</label>
                </div>
                <div class="controls">
                    <?php
                        echo JLayoutHelper::render(
                            'vue.form.cliente',
                            [
                                'view' => $this,
                                'options' => [
                                    'id' => 'cliente',
                                    'disabled' => 'busy',
                                    'renderModal' => false,
                                    'v-model' => 'quotation.cliente',
                                    'v-model-consumidor-final' => 'quotation.consumidorFinal',
                                    'onClickConsumidorFinal' => 'onClickConsumidorFinal',
                                    'onClickClear' => 'onClickClear',
                                    'onChangeCliente' => 'onChangeClienteModal',
                                    'onSelectCliente' => 'onSelectClienteModal'
                                ]
                            ]
                        );
                    ?>
                </div>
            </div>

            <div v-if="quotation.cliente == ''" class="control-group documento-field">
                <div class="controls">
                    <div class="input-prepend">
                        <div class="btn-group">
                            <button class="btn dropdown-toggle text-uppercase" data-toggle="dropdown">{{formatDocument(quotation.documentoTipo)}} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a href="#" @click="changeDocumentoTipo(80)">CUIT</a>
                                </li>
                                <li>
                                    <a href="#" @click="changeDocumentoTipo(96)">DNI</a>
                                </li>
                            </ul>
                        </div>
                        <input type="text" class="input-small" v-model="quotation.documentoNumero">
                    </div>
                </div>
            </div>

            <div v-if="quotation.cliente != '' && direcciones.length" class="control-group">
                <div class="control-label">
                    <label for="filter_direccion">Dirección</label>
                </div>
                <div class="controls">
                    <select2 id="filter_direccion" class="span12 no-chosen" :disabled="busy" v-model="quotation.id_direccion" :options="direcciones" :settings="{theme: 'bootstrap', language: 'es'}" data-placeholder="- Seleccione una dirección -">
                        <option :value="" disabled>- Seleccione una dirección -</option>
                    </select2>
                </div>
            </div>

            <div v-if="quotation.cliente != '' && transportes.length" class="control-group control-group-transporte">
                <div class="control-label">
                    <label for="filter_transporte">Transporte</label>
                </div>
                <div class="controls">
                    <select2 id="filter_transporte" class="span12 no-chosen" :disabled="busy" v-model="quotation.id_transporte" :options="transportes" :settings="{theme: 'bootstrap', language: 'es'}" data-placeholder="- Seleccione un transporte -">
                        <option :value="" disabled>- Seleccione un transporte -</option>
                    </select2>
                </div>
            </div>

            <div class="control-group control-group-deposito">
                <div class="control-label">
                    <label for="filter_deposito">Depósito</label>
                </div>
                <div class="controls">
                    <select2 id="filter_deposito" class="span12 no-chosen" :disabled="busy" v-model="quotation.id_deposito" :options="depositos" :settings="{theme: 'bootstrap', language: 'es'}" data-placeholder="- Seleccione un depósito -">
                        <option :value="" disabled>- Seleccione un depósito -</option>
                    </select2>
                </div>
            </div>

            <div class="control-group <?php echo $isRevendedor ? 'hidden' : ''; ?>">
                <div class="control-label">
                    <label for="filter_id_condicionventa" class="width-auto">Condición de Venta</label>
                    <select class="width-auto pull-right no-chosen select-mini" :disabled="busy" v-model="quotation.id_condicionventa" @change="onChangeCondicionVenta(quotation.id_condicionventa)">
                        <option v-for="item of condicionesVentaReales" :key="item.id" :value="item.id">{{item.dias}}</option>
                    </select>
                </div>
                
                <div class="controls">
                    <chosen-select id="filter_id_condicionventa" :disabled="busy" v-model="quotation.id_condicionventa_fake" @change="onChangeCondicionVentaFake(quotation.id_condicionventa_fake)" class="span12" data-placeholder="- Seleccione una condición de venta -">
                        <option v-for="item of condicionesVenta" :key="item.id" :value="item.id">{{item.text}}</option>
                    </chosen-select>
                </div>
            </div>

            <button-yesno v-model="quotation.iva" name="filter[iva]" :class="{disabled: busy}" @change="onChangeIva">
                <?php echo JText::_('COM_SABULLVIAL_FILTER_IVA'); ?>
            </button-yesno>
            <button-yesno v-model="quotation.dolar" name="filter[dolar]" :class="{disabled: busy}" @change="onChangeDolar">
                <?php echo JText::_('COM_SABULLVIAL_FILTER_DOLAR'); ?>
            </button-yesno>
        </div>
    </div>
    <div id="j-main-container" class="span10 j-toggle-main">
        <div class="row-fluid">
            <div class="span12">
                <?php
                    echo JLayoutHelper::render(
                        'vue.searchtools.default',
                        [
                            'view' => $this,
                            'options' => [
                                'filterButton' => false,
                                'vModel' => 'activeFilters.search',
                                'onSearchClick' => 'submitFilter',
                                'onClearClick' => 'clearFilter',
                            ]
                        ]
                    );
                ?>
            </div>
        </div>
        <div class="table-responsive">
            <table id="table" class="table table-striped table-hover" :class="{'table-loading': loading}">
                <thead>
                    <tr>
                        <th width="1%" class="center">
                            <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_IMAGEN'); ?>
                        </th>
                        <th width="1%" class="center">
                            <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_CATALOGO'); ?>
                        </th>
                        <th width="1%" class="nowrap center">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_PUNTOS_DE_VENTA_CODIGO_ART',
                                    'onClick' => "sortProducts('codigo_sap')",
                                    'order' => 'codigo_sap',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th style="min-width:100px" class="nowrap">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_PUNTOS_DE_VENTA_NOMBRE',
                                    'onClick' => "sortProducts('nombre')",
                                    'order' => 'nombre',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th width="10%" class="nowrap">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_PUNTOS_DE_VENTA_MARCA',
                                    'onClick' => "sortProducts('marca')",
                                    'order' => 'marca',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                        <th width="10%" class="nowrap">
                            <span v-if="quotation.iva == 1">
                                <?php
                                    echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                        'title' => 'COM_SABULLVIAL_PUNTOS_DE_VENTA_PRECIO_CON_IVA',
                                        'onClick' => "sortProducts('precio')",
                                        'order' => 'precio',
                                        'orderSelected' => 'activeFilters.ordering',
                                        'direction' => 'activeFilters.direction',
                                    ]);
                                ?>
                            </span>
                            <span v-else>
                                <?php
                                    echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                        'title' => 'COM_SABULLVIAL_PUNTOS_DE_VENTA_PRECIO_SIN_IVA',
                                        'onClick' => "sortProducts('precio')",
                                        'order' => 'precio',
                                        'orderSelected' => 'activeFilters.ordering',
                                        'direction' => 'activeFilters.direction',
                                    ]);
                                ?>
                            </span>
                        </th>
                        <th width="1%" class="nowrap">
                            <?php
                                echo JLayoutHelper::render('vue.searchtools.grid.sort', (object)[
                                    'title' => 'COM_SABULLVIAL_PUNTOS_DE_VENTA_STOCK',
                                    'onClick' => "sortProducts('stock')",
                                    'order' => 'stock',
                                    'orderSelected' => 'activeFilters.ordering',
                                    'direction' => 'activeFilters.direction',
                                ]);
                            ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="7">
                            <div class="pagination">
                                <ul id="vue-pagination" class="pagination-list pagination-toolbar"></ul>
                            </div>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <tr v-for="(producto, index) in productos" :key="producto.id" @click="toggleProducto(producto)" :class="{'success': producto.selected}">
                        <td class="center">
                            <carousel v-if="producto.images.length" :images="producto.images">
                                <span class="icon-image" aria-hidden="true"></span>
                            </carousel>
                        </td>
                        <td class="center">
                            <a v-if="producto.url" @click.stop :href="producto.url" target="_blank" title="<?php echo JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_ABRIR_CATALOGO'); ?>" class="hasTooltip">
                                <i class="icon-file-2"></i>
                            </a>
                        </td>
                        <td class="small center">
                            {{producto.codigo_sap}}
                        </td>
                        <td class="nowrap">
                            {{producto.nombre}}

                            <?php /*<div class="visible-phone left">
                                <span class="muted">
                                    {{quotation.dolar ? '<?php echo JText::_('COM_SABULLVIAL_DOLAR_SIMBOLO'); ?>' : '<?php echo JText::_('COM_SABULLVIAL_PESO_SIMBOLO'); ?>'}}{{numberFormat(producto.precioFinal, 2, ',', '.')}}
                                </span>
                                <span class="muted small">
                                    | Stock:
                                    <span v-if="!vendedor.ver.stockReal && producto.stock > 10">10+</span>
                                    <span v-else>{{producto.stock}}</span>
                                </span>
                            </div>*/ ?>
                        </td>
                        <td class="nowrap small">
                            {{producto.marca}}
                        </td>
                        <td class="nowrap small">
                            {{quotation.dolar ? '<?php echo JText::_('COM_SABULLVIAL_DOLAR_SIMBOLO'); ?>' : '<?php echo JText::_('COM_SABULLVIAL_PESO_SIMBOLO'); ?>'}}{{numberFormat(producto.precioFinal, 2, ',', '.')}}
                        </td>
                        <td class="center" @click.native.stop>							
                            <popover v-if="!vendedor.ver.stockReal && producto.stock > 10" :title="JText('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_TITLE', '10+')" data-placement="left" data-trigger="hover focus click" data-html="true" :data-content="producto.popover">
                                10+
                            </popover>
                            <popover v-else :title="JText('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_TITLE', producto.stock|numberFormat(0))" data-placement="left" data-trigger="hover focus click" data-html="true" :data-content="producto.popover">
                                {{producto.stock|numberFormat(0)}}
                            </popover>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php
        // La renderizo antes para que la modal del cliente no quede detras.
        echo JHtml::_(
            'bootstrap.renderModal',
            'previewCotizacion',
            [
                'title'       => '{{quotation.id ? JText("COM_SABULLVIAL_PUNTOS_DE_VENTA_COTIZACION_MODAL_TITLE_EDITAR", quotation.id) : "'.JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_COTIZACION').'"}}',
                'modalWidth'  => '90',
                'modalHeight' => '800',
                'height' => '800',
                'closeButton' => true,
                'footer' => $this->loadTemplate('cotizacion_footer'),
            ],
            $this->loadTemplate('cotizacion_body')
        );
?>

    <?php
        // La renderizo antes para que la modal del cliente no quede detras.

        $title = '<span v-if="afterCreatedCotizacionStep == 1" class="text-success">
            <span v-if="quotationCreated.isNew">' . JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_AFTER_CREATED_COTIZACION_TITLE') . '</span>
            <span v-else>' . JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_AFTER_UPDATED_COTIZACION_TITLE') . '</span>
        </span>';
        $title .= '<span v-else-if="afterCreatedCotizacionStep == 2" class="text-warning">' . JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_TITLE') . '</span>';

        echo JHtml::_(
            'bootstrap.renderModal',
            'afterCreatedCotizacion',
            [
                'title'       => $title,
                'modalWidth'  => '20',
                'modalHeight' => '800',
                'height' => '800',
                'closeButton' => true,
                'footer' => $this->loadTemplate('after_created_cotizacion_footer'),
            ],
            $this->loadTemplate('after_created_cotizacion_body')
        );
    ?>

    <?php
        echo JHtml::_(
            'bootstrap.renderModal',
            'myCotizaciones',
            [
                'title'       => JText::_('COM_SABULLVIAL_PUNTOS_DE_VENTA_MIS_COTIZACIONES'),
                'modalWidth'  => '90',
                'modalHeight' => '800',
                'height' => '800',
                'closeButton' => true,
                'footer' => $this->loadTemplate('mis_cotizaciones_footer'),
            ],
            $this->loadTemplate('mis_cotizaciones_body')
        );
    ?>

    <?php
        echo JHtml::_(
            'bootstrap.renderModal',
            'modalProductoCarousel',
            [
                'title'       => 'Galería de Imágenes',
                'modalWidth'  => '50',
                'modalHeight' => '800',
                'height' => '800',
                'closeButton' => true,
                'footer' => $this->loadTemplate('producto_carousel_footer')
            ],
            ''
        );
    ?>
    
    <?php
        // Renderizo la modal del input del cliente.
        echo JLayoutHelper::render(
            'vue.form.cliente.modal',
            [
                'view' => $this, 
                'options' => [
                    'id' => 'cliente',
                    'v-model' => 'quotation.cliente',
                    'v-model-consumidor-final' => 'quotation.consumidorFinal',
                    'onClickConsumidorFinal' => 'onClickConsumidorFinal',
                    'onClickClear' => 'onClickClear',
                    'onChangeCliente' => 'onChangeClienteModal',
                    'onSelectCliente' => 'onSelectClienteModal'
                ]
            ]
        );
    ?>

    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
</form>