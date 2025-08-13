<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Table\Table;

$data = $displayData;

// Receive overridable options
$data = !empty($data) ? $data : [];

if (is_array($data)) {
    $data = new Registry($data);
}

$vendedor = SabullvialHelper::getVendedor();
$isRevendedor = $vendedor->get('esRevendedor', false);
$isAdministrador = SabullvialHelper::isUserAdministrador();
$canEnviarAFacturacion = $vendedor->get('enviarAFacturacion', true);

$estadosParaEditar = $data->get('estadosParaEditar', []);
$estadosParaDuplicar = $data->get('estadosParaDuplicar', []);
$estadoAprobadoAutomatico = $data->get('estadoAprobadoAutomatico');

$estadoOrdenDeTrabajo = Table::getInstance('EstadoCotizacion', 'SabullvialTable');
$estadoOrdenDeTrabajo->load($data->get('estadoOrdenDeTrabajo'));

Factory::getDocument()->addScriptOptions('com_sabullvial.quotes', [
    'idVendedor' => JFactory::getUser()->id,
    'token' => JSession::getFormToken(),
    'estadosParaEditar' => $estadosParaEditar,
    'estadosParaDuplicar' => $estadosParaDuplicar,
    'estadoAprobadoAutomatico' => $estadoAprobadoAutomatico,
]);

HTMLHelper::script('com_sabullvial/tools.js', ['version' => 'auto', 'relative' => true]);

Text::script('COM_SABULLVIAL_COTIZACIONES_TANGO_SINCRONIZADO');
Text::script('COM_SABULLVIAL_COTIZACIONES_TANGO_ENVIADO');
Text::script('COM_SABULLVIAL_COTIZACIONES_TANGO_SIN_ENVIAR');
HTMLHelper::script('com_sabullvial/components/cliente-label.js', ['version' => 'auto', 'relative' => true]);

Text::script('COM_SABULLVIAL_CLIENTES_RAZON_SOCIAL');
Text::script('COM_SABULLVIAL_CLIENTES_CONSUMIDOR_FINAL');
HTMLHelper::script('com_sabullvial/components/status-sync-label.js', ['version' => 'auto', 'relative' => true]);

Text::script('JACTION_SEND_TO_FACTURACION');
Text::script('JCANCEL');
Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_HAS_CUSTOM_PRODUCTS_DESC');
Text::script('JYES');
Text::script('JACTION_TEST');
Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_SUCCESS');
Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_DESC');
Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ELIJA_EL_TIPO_DE_FACTURACION');
Text::script('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIANDO_A_FACTURACION');
HTMLHelper::script('com_sabullvial/components/send-to-facturacion-button.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::script('com_sabullvial/components/label-estado.js', ['version' => 'auto', 'relative' => true]);
HTMLHelper::script('com_sabullvial/components/quotes.js', ['version' => 'auto', 'relative' => true]);
?>
<script type="text/x-template" id="quotes-template">
    <div>
        <slot name="header" :query="query" :search="search" :searchFilters="searchFilters" :clear="clear" :clearFilters="clearFilters" :queryFilters="queryFilters" :loading="loading" :searchMyQuotes="searchMyQuotes" :toggleFilters="toggleFilters" :showFilters="showFilters"></slot>
        <div class="table-responsive">
            <table class="table table-striped table-hover table-condensed" :class="{'table-loading': loading}">
                <thead>
                    <tr>
                        <th width="1%" class="nowrap center">
                            <?php echo Text::_('JGRID_HEADING_ID'); ?>
                        </th>
                        <th width="1%" class="nowrap center line-height-12">
                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_HERRAMIENTAS'); ?>
                        </th>
                        <th width="115" class="nowrap left">
                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_ESTADO_COTIZACION'); ?>
                        </th>
                        <th style="min-width:200px" class="nowrap">
                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_CLIENTE'); ?>
                        </th>
                        <th class="center">
                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_NUMERO_REMITO_Y_FACTURAS'); ?>
                        </th>
                        <th class="nowrap center">
                            <?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_ACCIONES'); ?>
                        </th>
                        <?php if (!$isRevendedor): ?>
                            <th width="10%" class="nowrap center">
                                <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_CONDICION_VENTA'); ?>
                            </th>
                        <?php endif; ?>
                        <th width="5%" class="nowrap center">
                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_TOTAL'); ?>
                        </th>
                        <th width="5%" class="nowrap center">
                            <?php echo Text::_('COM_SABULLVIAL_CREATED_DATE'); ?>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-if="loading && quotes.length == 0">
                        <td colspan="<?php echo !$isRevendedor ? 9 : 8; ?>" class="center">
                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_MIS_COTIZACIONES_LOADING'); ?>
                        </td>
                    </tr>
                    <tr v-if="!loading && quotes.length == 0">
                        <td colspan="<?php echo !$isRevendedor ? 9 : 8; ?>" class="center muted">
                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_MIS_COTIZACIONES_EMPTY'); ?>
                        </td>
                    </tr>
                    <tr v-for="(quote, index) in quotes" :key="quote.id">
                        <td class="nowrap small center">
                            <status-sync-label 
                                :key="'status-sync-label-' + quote.id" 
                                :id-cotizacion="quote.id"
                                :tango-enviar="parseInt(quote.tango_enviar)"
                                :tango-fecha-sincronizacion="quote.tango_fecha_sincronizacion"
                                class="mb-sm1" />
                            <div> 
                                <span v-if="quote.is_orden_de_trabajo" class="label hasTooltip mb-sm1" title="<?php echo $estadoOrdenDeTrabajo->nombre; ?>"
                                    style="background-color: <?php echo $estadoOrdenDeTrabajo->color ; ?>; color: <?php echo $estadoOrdenDeTrabajo->color_texto; ?>">
                                    <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_ORDEN_DE_TRABAJO_SHORT'); ?>
                                </span>
                            </div>
                        </td>
                        <td class="center">
                            <div class="btn-group">
                                <?php if ($isAdministrador) : ?>
                                    <a v-if="quote.id_estadocotizacion != <?php echo $estadoAprobadoAutomatico; ?> && quote.is_reviewed" 
                                        class="btn btn-micro active hasTooltip" 
                                        :href="'index.php?option=com_sabullvial&view=cotizacion&layout=edit&type=revisiondetalle&id=' + quote.id + '&format=pdf&layout=print'" 
                                        target="_blank" 
                                        data-placement="auto-dir right"
                                        data-title="<?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_IMPRIMIR_REVISION'); ?>">
                                        <span class="icon-print text-info" aria-hidden="true"></span>
                                    </a>
                                <?php endif; ?>
                                <a v-if="quote.has_cotizaciondetalle" 
                                    class="btn btn-micro hasTooltip" 
                                    :href="'index.php?option=com_sabullvial&view=cotizacion&layout=edit&id=' + quote.id + '&format=pdf&layout=print'" target="_blank" 
                                    data-placement="auto-dir right"
                                    data-title="<?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_IMPRIMIR_COTIZACION'); ?>">
                                    <span class="icon-print" aria-hidden="true"></span>
                                </a>
                                <a v-if="quote.canEdit" @click="edit(quote)" class="btn btn-micro hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>" href="javascript:void(0)">
                                    <span class="icon-edit" aria-hidden="true"></span>
                                </a>
                                <a v-if="quote.canDuplicate" @click="edit(quote)" class="btn btn-micro hasTooltip" title="<?php echo Text::_('JACTION_DUPLICATE_COTIZACION'); ?>" href="javascript:void(0)">
                                    <span class="icon-copy" aria-hidden="true"></span>
                                </a>
                            </div>
                        </td>
                        <td class="left">
                            <span class="label" :style="'background-color: ' + quote.estadocotizacion_bg_color + ' ; color: ' + quote.estadocotizacion_color">
                                {{ quote.estadocotizacion }}
                            </span>
                            
                            <?php if ($isAdministrador || $canEnviarAFacturacion) : ?>
                                <div class="hasTooltip" data-title="<?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_ESTADO_TANGO_DESC'); ?>" data-placement="auto-dir right">
                                    <span class="muted small">Tango </span>
                                    <span class="label" :style="'background-color: ' + quote.estadotango_bg_color + '; color: ' +  quote.estadotango_color">
                                        {{ quote.estadotango }}
                                    </span>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <cliente-label
                                :key="'cliente-label-' + quote.id"
                                :cliente="quote.cliente"
                                :razon-social="quote.razon_social"
                                :id-cliente="quote.id_cliente"
                                :codcli="quote.codcli"
                                :cuit="quote.cuit"
                                :codigoVendedor="quote.codigo_vendedor"
                                :documentoTipo="parseInt(quote.documento_tipo)"
                                :documentoNumero="quote.documento_numero"
                                :can-click="quote.canEdit || quote.canDuplicate"
                                :title="quote.canEdit ? '<?php echo Text::_('JACTION_EDIT'); ?>' : quote.canDuplicate ? '<?php echo Text::_('JACTION_DUPLICATE_COTIZACION'); ?>' : ''"
                                @click="edit(quote)"
                            />
                        </td>
                        <td class="center small nowrap">
                            <span v-if="quote.id_deposito > 0" class="deposito"><span class="muted">Depósito:</span> {{quote.deposito}}<br/></span>
                            <div v-if="quote.remitos.length || quote.numeros_facturas.length" class="btn-group">
                                <a class="btn dropdown-toggle btn-small" data-toggle="dropdown" href="#">
                                    <svg v-if="quote.remitos.length" class="mr-1" height="16px" style="vertical-align: middle;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M14 2.2C22.5-1.7 32.5-.3 39.6 5.8L80 40.4 120.4 5.8c9-7.7 22.3-7.7 31.2 0L192 40.4 232.4 5.8c9-7.7 22.3-7.7 31.2 0L304 40.4 344.4 5.8c7.1-6.1 17.1-7.5 25.6-3.6s14 12.4 14 21.8V488c0 9.4-5.5 17.9-14 21.8s-18.5 2.5-25.6-3.6L304 471.6l-40.4 34.6c-9 7.7-22.3 7.7-31.2 0L192 471.6l-40.4 34.6c-9 7.7-22.3 7.7-31.2 0L80 471.6 39.6 506.2c-7.1 6.1-17.1 7.5-25.6 3.6S0 497.4 0 488V24C0 14.6 5.5 6.1 14 2.2zM96 144c-8.8 0-16 7.2-16 16s7.2 16 16 16H288c8.8 0 16-7.2 16-16s-7.2-16-16-16H96zM80 352c0 8.8 7.2 16 16 16H288c8.8 0 16-7.2 16-16s-7.2-16-16-16H96c-8.8 0-16 7.2-16 16zM96 240c-8.8 0-16 7.2-16 16s7.2 16 16 16H288c8.8 0 16-7.2 16-16s-7.2-16-16-16H96z"/></svg>
                                    <svg v-if="quote.numeros_facturas.length" class="mr-1" height="16px" style="vertical-align: middle;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M64 464c-8.8 0-16-7.2-16-16V64c0-8.8 7.2-16 16-16H224v80c0 17.7 14.3 32 32 32h80V448c0 8.8-7.2 16-16 16H64zM64 0C28.7 0 0 28.7 0 64V448c0 35.3 28.7 64 64 64H320c35.3 0 64-28.7 64-64V154.5c0-17-6.7-33.3-18.7-45.3L274.7 18.7C262.7 6.7 246.5 0 229.5 0H64zm56 256c-13.3 0-24 10.7-24 24s10.7 24 24 24H264c13.3 0 24-10.7 24-24s-10.7-24-24-24H120zm0 96c-13.3 0-24 10.7-24 24s10.7 24 24 24H264c13.3 0 24-10.7 24-24s-10.7-24-24-24H120z"/></svg>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu nav nav-list">
                                    <template v-if="quote.remitos.length">
                                        <li class="nav-header">Remitos</li>
                                        <li v-for="(remito, remIndex) in quote.remitos" :key="remito.numero_remito">
                                            {{remito.numero_remito}}<br/>
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
                                        </li>
                                    </template>
                                    <template v-if="quote.numeros_facturas.length">
                                        <li class="nav-header">Facturas</li>
                                        <li v-for="(factura, facIndex) in quote.numeros_facturas" :key="factura">
                                            {{factura}}
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </td>
                        <td class="center nowrap">
                            <send-to-facturacion-button v-if="quote.canSendToFacturacion"
                                :key="'send-to-facturacion-button-' + quote.id"
                                :id-cotizacion="quote.id"
                                :has-custom-products="quote.has_custom_products"
                                :estado-tango-srl="<?php echo SabullvialTableCotizacion::ESTADO_TANGO_SRL; ?>"
                                :estado-tango-prueba="<?php echo SabullvialTableCotizacion::ESTADO_TANGO_PRUEBA; ?>"
                                @on-sent="fetchData"
                            />
                        </td>
                        <?php if (!$isRevendedor): ?>
                            <td class="nowrap small center">
                                {{quote.condicionVenta}}
                            </td>
                        <?php endif; ?>
                        <td class="nowrap small center">
                            ${{numberFormat(quote.total, 2, ',', '.')}}
                        </td>
                        <td class="small left">
                            {{quote.created}}
                            <br/>
                            <span class="muted"><?php echo Text::_('JAUTHOR'); ?>:</span> {{quote.author}}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo !$isRevendedor ? 9 : 8; ?>">
                            <div class="pagination">
                                <ul class="pagination-list pagination-toolbar"></ul>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <slot name="footer" :query="query" :search="search" :loading="loading"></slot>
    </div>
</script>