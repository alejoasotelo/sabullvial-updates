<?php
/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Language\Text;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$vendedor = SabullvialHelper::getVendedor();

$isAprobarClientes = $vendedor->get('aprobar.clientes', false);
$esRevendedor = $vendedor->get('esRevendedor', false);

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

JFactory::getDocument()->addScriptDeclaration("
    window.refreshApproveButton = function() {
        const plazo = jQuery('#modal_approve_plazo').val();
        const monto = jQuery('#modal_approve_monto').val();

        if (plazo.trim().length > 0 && monto.trim().length > 0) {
            return jQuery('.btn-approve').removeAttr('disabled');
        }
            
        jQuery('.btn-approve').attr('disabled', 'disabled');
    }
    window.setApprovePlazo = function(plazo) {
        jQuery('#modal_approve_plazo').val(plazo);
        refreshApproveButton();
    }
    window.setApproveMonto = function(monto) {
        jQuery('#modal_approve_monto').val(monto);
        refreshApproveButton();
    }

    jQuery(document).ready(function() {
        refreshApproveButton();
    });
");
?>
<form action="index.php?option=com_sabullvial&view=clientes" method="post" id="adminForm" name="adminForm">
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
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
                        </th>
                        <th width="8%" class="hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_ESTADO_CLIENTE', 'estadocliente', $listDirn, $listOrder); ?>
                        </th>
                        <th style="min-width:140px" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_RAZON_SOCIAL', 'razon_social', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%" class="center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_CODIGO_VENDEDOR', 'codigo_vendedor', $listDirn, $listOrder); ?>
                        </th>
                        <?php if (!$esRevendedor): ?>
                            <th width="20%" class="nowrap center">
                                <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_CONDICION_VENTA', 'condicion_venta', $listDirn, $listOrder); ?>
                            </th>
                        <?php endif; ?>
                        <th width="10%" class="nowrap center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_ACTIVIDAD_COMERCIAL', 'actividad_comercial', $listDirn, $listOrder); ?>
                        </th>
                        <?php if ($isAprobarClientes): ?>
                            <th width="30%" class="nowrap center">
                                <?php echo JText::_('COM_SABULLVIAL_COTIZACIONES_ACCIONES'); ?>
                            </th>
                        <?php endif; ?>
                        <th width="5%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <td colspan="<?php echo $isAprobarClientes ? 9 : 8; ?>">
                            <?php echo $this->pagination->getListFooter(); ?>
                        </td>
                    </tr>
                </tfoot>
                <tbody>
                    <?php if (!empty($this->items)) : ?>
                        <?php foreach ($this->items as $i => $row) : ?>
                            <?php
                                $link = JRoute::_('index.php?option=com_sabullvial&task=cliente.edit&id=' . $row->id);
                            ?>
                            <tr>
                                <td class="center">
                                    <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                                </td>
                                <td class="center small">
                                    <?php
                                        $isSincWithTango = !SabullvialHelper::isTangoFechaSincronizacionNull($row->tango_fecha_sincronizacion);

                                        $class = 'label hasTooltip';
                                        $text = Text::_('COM_SABULLVIAL_CLIENTES_TANGO_SIN_ENVIAR');
                                        if ($row->tango_enviar && $isSincWithTango) {
                                            $class .= ' label-success';
                                            $date = JHtml::_('date', $row->tango_fecha_sincronizacion, JText::_('DATE_FORMAT_LC5'), null);
                                            $text = Text::sprintf('COM_SABULLVIAL_CLIENTES_TANGO_SINCRONIZADO', $date, null, true);
                                        } elseif ($row->tango_enviar && !$isSincWithTango) {
                                            $class .= ' label-warning';
                                            $text = Text::_('COM_SABULLVIAL_CLIENTES_TANGO_ENVIADO');
                                        }
                                    ?>
                                    <span class="<?php echo $class;?>" title="<?php echo $text; ?>">
                                        <?php echo $row->id; ?>
                                    </span>
                                </td>
                                <td class="hidden-phone">
                                    <span class="label" style="background-color: <?php echo $row->estadocliente_bg_color; ?>; color: <?php echo $row->estadocliente_color; ?>">
                                        <?php echo $row->estadocliente; ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JSHOW'); ?>">
                                        <?php echo $this->escape($row->razon_social); ?>
                                    </a>
                                    <div class="small">
                                        <span class="muted"><?php echo JText::_('COM_SABULLVIAL_CLIENTES_DOCUMENTO_TIPO_' . $row->documento_tipo); ?>: </span>
                                        <span><?php echo $row->documento_numero; ?></span>
                                    </div>
                                </td>
                                <td class="small center nowrap">
                                    <span class="muted"><?php echo $row->nombre_vendedor; ?></span>
                                    <span>(<?php echo $row->codigo_vendedor; ?>)</span>
                                </td>
                                <?php if (!$esRevendedor): ?>
                                    <td class="small center">
                                        <?php echo  $row->condicion_venta; ?>
                                    </td>
                                <?php endif; ?>
                                <td class="small center">
                                    <?php echo  $row->actividad_comercial; ?>
                                </td>
                                <?php if ($isAprobarClientes) : ?>
                                    <td class="center">
                                        <?php $btnParams = ['cliente' => $row, 'index' => $i, 'show' => 'button']; ?>
                                        <?php echo JLayoutHelper::render('joomla.content.clientes.buttons.aprobar', $btnParams); ?>
                                        <?php echo JLayoutHelper::render('joomla.content.clientes.buttons.rechazar', $btnParams); ?>
                                    </td>
                                <?php endif; ?>
                                <td class="nowrap small">
                                    <?php
                                        echo $row->created > 0 ? JHtml::_('date', $row->created, JText::_('DATE_FORMAT_LC5')) : '-';
                                    ?>
                                    <br/>
                                    <span class="muted"><?php echo JText::_('JAUTHOR');?>:</span> <?php echo $row->created_by_alias; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Creamos las modales -->
    <?php if ($isAprobarClientes) : ?>
        <?php foreach ($this->items as $i => $row) : ?>
            <?php
                $btnParams = ['cliente' => $row, 'index' => $i, 'show' => 'modal'];
                echo JLayoutHelper::render('joomla.content.clientes.buttons.aprobar', $btnParams);
                echo JLayoutHelper::render('joomla.content.clientes.buttons.rechazar', $btnParams);
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <input type="hidden" name="modal_approve_monto" id="modal_approve_monto" value=""/>
    <input type="hidden" name="modal_approve_plazo" id="modal_approve_plazo" value=""/>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo JHtml::_('form.token'); ?>
</form>