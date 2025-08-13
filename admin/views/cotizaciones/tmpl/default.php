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
use Joomla\CMS\Table\Table;
use Joomla\CMS\Router\Route;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', '.multipleAuthors', null, ['placeholder_text_multiple' => JText::_('JOPTION_SELECT_AUTHOR')]);
JHtml::_('formbehavior.chosen', '.multipleEstadosCotizacion', null, ['placeholder_text_multiple' => JText::_('JOPTION_SELECT_PUBLISHED')]);
JHtml::_('formbehavior.chosen', '.multipleEstadosCotizacionTango', null, ['placeholder_text_multiple' => JText::_('JOPTION_SELECT_ESTADO_COTIZACION_TANGO')]);
JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$cotizacionDolar = SabullvialHelper::getConfig('COTI_DOL');

$isAdministrador = SabullvialHelper::isUserAdministrador();
$vendedor = SabullvialHelper::getVendedor();
$canEnviarAFacturacion = $vendedor->get('enviarAFacturacion', true);

if (!defined('ESTADO_APROBADO_AUTOMATICO')) {
    define('ESTADO_APROBADO_AUTOMATICO', $this->params->get('cotizacion_estado_aprobado_automatico'));
}

$doc = Factory::getDocument();
$doc->addScriptDeclaration("
    window.setReviewData = function(productos) {
        var data = productos.map(function(producto) {
            return {
                id: parseInt(producto.id),
                id_producto:  producto.id_producto,
                cantidad:  parseInt(producto.cantidad),
                cantidad_disponible:  parseInt(producto.cantidad_disponible)
            };
        });
        jQuery('#reviewData').val(JSON.stringify(data));
    }

    window.setCotizacionId = function(idCotizacion) {
        jQuery('#reviewCotizacionId').val(idCotizacion);
    }

    window.enableButtonReview = function(idCotizacion) {
        console.log('enable ', idCotizacion);
        jQuery('#reviewModal' + idCotizacion + ' .btnReviewButton').removeAttr('disabled');
    }

    window.setReviewCotizacionId = function(idCotizacion) {
        jQuery('#viewReviewCotizacionId').val(idCotizacion);
    }

    window.createWithFaltantes = function(idCotizacion) {
        jQuery('#viewReviewCotizacionId').val(idCotizacion);
        Joomla.submitbutton('cotizacion.createWithFaltantes');
    }
    window.setApproveComentarios = function(comentarios) {
        console.log({comentarios});
        jQuery('#modal_approve_comentarios').val(comentarios);
    }
");
$estadoOrdenDeTrabajo = null;
?>
<form action="index.php?option=com_sabullvial&view=cotizaciones" method="post" id="adminForm" name="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo JHtmlSidebar::render(); ?>
    </div>
    <div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="span12">
                <?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th width="1%" class="center">
                            <?php echo JHtml::_('grid.checkall'); ?>
                        </th>
                        <th width="1%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="center">
                            <?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_HERRAMIENTAS'); ?>
                        </th>
                        <th width="8%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_COTIZACIONES_ESTADO_COTIZACION', 'estadocotizacion', $listDirn, $listOrder); ?>
                        </th>
                        <th style="min-width:100px" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_COTIZACIONES_CLIENTE', 'cliente', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_COTIZACIONES_NUMERO_REMITO', 'pr.N_REMITO', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_COTIZACIONES_NUMERO_FACTURA', 'pf.NCOMP_FAC', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_COTIZACIONES_TOTAL', 'total', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_COTIZACIONES_ESPERAR_PAGOS', 'esperar_pagos', $listDirn, $listOrder); ?>
                        </th>
                        <th width="<?php echo $isAdministrador ? '30%' : '15%'; ?>" class="nowrap center">
                            <?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_ACCIONES'); ?>
                        </th>					
                        <th width="5%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CREATED_DATE', 'a.created', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="10">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (!empty($this->items)) : ?>
                        <?php foreach ($this->items as $i => $row) : ?>
                            <?php
                                $link = Route::_('index.php?option=com_sabullvial&task=cotizacion.edit&id=' . $row->id);
                                $linkParent = $row->parent_id > 0 ? Route::_('index.php?option=com_sabullvial&task=cotizacion.edit&id=' . $row->parent_id) : '';

                                if ($row->estadocotizacion_cancelado) {
                                    $link = '#';
                                }
                            ?>
                            <tr>
                                <td class="center">
                                    <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                                </td>
                                <td class="center small">
                                    <?php
                                        $isSincWithTango = !SabullvialHelper::isTangoFechaSincronizacionNull($row->tango_fecha_sincronizacion);

                                        $class = 'label mb-sm1 hasTooltip';
                                        $text = Text::_('COM_SABULLVIAL_COTIZACIONES_TANGO_SIN_ENVIAR');
                                        if ($row->tango_enviar && $isSincWithTango) {
                                            $class .= ' label-success';
                                            $date = JHtml::_('date', $row->tango_fecha_sincronizacion, JText::_('DATE_FORMAT_LC5'), null);
                                            $text = Text::sprintf('COM_SABULLVIAL_COTIZACIONES_TANGO_SINCRONIZADO', $date, null, true);
                                        } elseif ($row->tango_enviar && !$isSincWithTango) {
                                            $class .= ' label-warning';
                                            $text = Text::_('COM_SABULLVIAL_COTIZACIONES_TANGO_ENVIADO');
                                        }
                                    ?>
                                    <span class="<?php echo $class;?>" title="<?php echo $text; ?>">
                                        <?php echo $row->id; ?>
                                    </span>
                                    <?php if ($row->is_orden_de_trabajo): ?>
                                        <?php 
                                            if (is_null($estadoOrdenDeTrabajo)) {
                                                $estadoOrdenDeTrabajo = Table::getInstance('EstadoCotizacion', 'SabullvialTable');
                                                $estadoOrdenDeTrabajo->load($this->params->get('orden_de_trabajo_estado_creado'));
                                            }
                                        ?>
                                        <span class="hasTooltip label mb-sm1" title="<?php echo $estadoOrdenDeTrabajo->nombre; ?>"
                                        style="background-color: <?php echo $estadoOrdenDeTrabajo->color ; ?>; color: <?php echo $estadoOrdenDeTrabajo->color_texto; ?>">
                                            <?php echo Text::_('COM_SABULLVIAL_COTIZACIONES_ORDEN_DE_TRABAJO_SHORT'); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="center">
                                    <div class="btn-group">
                                        <?php if ($isAdministrador && $row->id_estadocotizacion != ESTADO_APROBADO_AUTOMATICO): ?>
                                            <a class="btn btn-micro <?php echo $row->is_reviewed ? 'active' : 'disabled';?> hasTooltip" href="<?php echo !$row->is_reviewed ? 'javascript::void();' : Route::_('index.php?option=com_sabullvial&view=cotizacion&layout=edit&type=revisiondetalle&id=' . $row->id . '&format=pdf&layout=print'); ?>" target="_blank" 
                                                data-original-title="<?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_IMPRIMIR_REVISION'); ?>">
                                                <span class="icon-print <?php echo $row->is_reviewed ? 'text-info' : ''; ?>" aria-hidden="true"></span>
                                            </a>
                                        <?php endif; ?>
                                        <a class="btn btn-micro <?php echo $row->has_cotizaciondetalle ? '' : 'disabled';?> hasTooltip" href="<?php echo !$row->has_cotizaciondetalle ? 'javascript::void();' : Route::_('index.php?option=com_sabullvial&view=cotizacion&layout=edit&id=' . $row->id . '&format=pdf&layout=print'); ?>" target="_blank" 
                                            data-original-title="<?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_IMPRIMIR_COTIZACION'); ?>">
                                            <span class="icon-print" aria-hidden="true"></span>
                                        </a>
                                        <?php if (SabullvialButtonsHelper::canDuplicate($row->id_estadocotizacion)): ?>
                                            <a class="btn btn-micro hasTooltip" title="<?php echo Text::_('JACTION_DUPLICATE_COTIZACION'); ?>" href="<?php echo Route::_('index.php?option=com_sabullvial&task=cotizaciones.duplicate&id=' . $row->id . '&' . JSession::getFormToken() . '=1'); ?>">
                                                <span class="icon-copy" aria-hidden="true"></span>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="hidden-phone">
                                    <span class="label mb-sm1" style="background-color: <?php echo $row->estadocotizacion_bg_color; ?>; color: <?php echo $row->estadocotizacion_color; ?>">
                                        <?php echo $row->estadocotizacion; ?>
                                    </span>
                                    
                                    <?php if ($isAdministrador || $canEnviarAFacturacion): ?>
                                        <div class="hasTooltip" data-title="<?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_ESTADO_TANGO_DESC'); ?>">
                                            <span class="muted small">Tango</span> 
                                            <span class="label" style="background-color: <?php echo $row->estadotango_bg_color; ?>; color: <?php echo $row->estadotango_color; ?>">
                                                <?php echo $row->estadotango; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT_COTIZACION'); ?>">
                                        <?php echo !empty($row->cliente) ? $this->escape($row->cliente) : $this->escape($row->razon_social); ?>
                                    </a>
                                    <div class="small">
                                        <?php if ($row->id_cliente && $row->id_cliente != '000000'): ?> 
                                            <span class="hasTooltip" data-title="<?php echo JText::_('COM_SABULLVIAL_CLIENTES_RAZON_SOCIAL'); ?>">RS</span>: <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT_COTIZACION'); ?>"><?php echo $row->razon_social; ?></a> | 
                                            Cod: <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT_COTIZACION'); ?>"><?php echo $row->codcli; ?></a> | 
                                            Cuit: <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT_COTIZACION'); ?>"><?php echo $row->cuit; ?></a> |
                                            CV: <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT_COTIZACION'); ?>"><?php echo $row->codigo_vendedor; ?></a>
                                        <?php else: ?>
                                            <span class="hasTooltip" data-title="<?php echo JText::_('COM_SABULLVIAL_CLIENTES_CONSUMIDOR_FINAL'); ?>">CF</span>
                                            <?php if (!empty($row->documento_numero)): ?>
                                                | 
                                                <?php echo $row->documento_tipo == 80 ? 'Cuit' : 'Dni'; ?>: 
                                                <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT_COTIZACION'); ?>"><?php echo $row->documento_numero; ?></a>
                                            <?php endif; ?>
                                        <?php endif;?>
                                        
                                        <?php if ($row->parent_id > 0): ?>
                                            <div>
                                                <?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_COTIZACION_PADRE'); ?>:	
                                                <a href="<?php echo $linkParent; ?>" class="hasTooltip" data-title="<?php echo JText::sprintf('COM_SABULLVIAL_COTIZACIONES_COTIZACION_PADRE_LINK_DESC', $row->parent_id); ?>">#<?php echo $row->parent_id;?></a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="visible-phone small">
                                        <span class="label" style="background-color: <?php echo $row->estadocotizacion_bg_color; ?>; color: <?php echo $row->estadocotizacion_color; ?>">
                                            <?php echo $row->estadocotizacion; ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="center small">
                                    <?php if ($row->id_deposito > 0): ?>
                                        <span class="deposito"><span class="muted">Depósito:</span> <?php echo $row->deposito; ?><br/></span>
                                    <?php endif; ?>

                                    <?php
                                        $lastIndex = count($row->remitos) - 1;
                                    ?>
                                    <?php foreach ($row->remitos as $i => $remito): ?>
                                        <div class="mb-sm1">
                                            <span>
                                                <?php echo $remito->numero_remito; ?>
                                            </span>
                                            <?php if (!is_null($remito->image)): ?>
                                                <a href="<?php echo JUri::root() . ltrim($remito->image, '/'); ?>" target="_blank" class="btn btn-default btn-mini btn-img-remito ml-sm-1 hasTooltip"
                                                    title="<?php echo Text::_('COM_SABULLVIAL_FIELD_COTIZACIONHISTORICOTABLE_VER_FOTO_DEL_REMITO'); ?>"
                                                >
                                                    <i class="icon-picture"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <span class="label" style="background-color: <?php echo $remito->estadoremito_bg_color; ?>; color: <?php echo $remito->estadoremito_color;?>">
                                            <?php echo $remito->estadoremito; ?>
                                            <?php if (($remito->estadoremito_entregado == 1 || $remito->estadoremito_entregado_mostrador == 1) && $remito->delivery_date): ?>
                                                <span>el <?php echo $remito->delivery_date; ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <?php echo ($i < $lastIndex ? '<br/>' : ''); ?>
                                    <?php endforeach; ?>
                                </td>
                                <td class="center small">
                                    <?php if (!empty($row->numeros_facturas)): ?>
                                        <?php echo implode('<br/>', $row->numeros_facturas); ?>
                                    <?php endif ?>
                                </td>
                                <td class="center">
                                    <?php if ($row->dolar): ?>
                                        <span class="hasTooltip" title="<?php echo PriceHelper::format($row->total * $cotizacionDolar); ?>">
                                            <?php echo PriceHelper::format($row->total, PriceHelper::FORMAT_USD); ?>
                                        </span>
                                        <br/>
                                        <span class="muted small"><?php echo PriceHelper::format($row->total * $cotizacionDolar); ?></span>
                                    <?php else: ?>
                                        <span><?php echo PriceHelper::format($row->total); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="center">
                                    <?php if ($row->esperar_pagos > 0): ?>
                                        <span class="label mb-sm1" style="background-color: <?php echo $row->estadocotizacionpago_bg_color; ?>; color: <?php echo $row->estadocotizacionpago_color; ?>">
                                            <?php echo $row->estadocotizacionpago; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="center">
                                    <?php $btnParams = ['cotizacion' => $row, 'params' => $this->params, 'index' => $i, 'type' => 'list', 'show' => 'button']; ?>
                                    <?php echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.cancelar', $btnParams); ?>
                                    <?php echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.aprobar', $btnParams); ?>
                                    <?php echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.aprobar_pago', $btnParams); ?>
                                    <?php echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.enviar_a_facturacion', $btnParams); ?>
                                    <?php echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.ver', $btnParams); ?>
                                    <?php echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.revisar', $btnParams); ?>
                                    <?php echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.ver_revision', $btnParams); ?>
                                </td>
                                <td class="nowrap small hidden-phone">
                                    <?php
                                        echo $row->created > 0 ? JHtml::_('date', $row->created, JText::_('DATE_FORMAT_LC5')) : '-';
                                    ?>
                                    <br/>
                                    <span class="muted"><?php echo JText::_('JAUTHOR');?>:</span> <?php echo $row->author; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Creamos las modales -->
    <?php foreach ($this->items as $i => $row) : ?>
        <?php
            $btnParams = ['cotizacion' => $row, 'params' => $this->params, 'index' => $i, 'type' => 'list', 'show' => 'modal'];
            echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.cancelar', $btnParams);
            echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.aprobar', $btnParams);
            echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.aprobar_pago', $btnParams);
            echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.enviar_a_facturacion', $btnParams);
            echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.ver', $btnParams);
            echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.revisar', $btnParams);
            echo JLayoutHelper::render('joomla.content.cotizaciones.buttons.ver_revision', $btnParams);
        ?>
    <?php endforeach; ?>

    <input type="hidden" name="review" id="reviewData" value=""/>
    <input type="hidden" name="reviewCotizacionId" id="reviewCotizacionId" value=""/>
    <input type="hidden" name="modal_approve_comentarios" id="modal_approve_comentarios" value=""/>
    <textarea class="hidden" name="comentariosApproveCotizacion" id="comentariosApproveCotizacion"></textarea>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo JHtml::_('form.token'); ?>
</form>