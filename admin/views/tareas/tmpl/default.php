<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ListModel;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('formbehavior.chosen', 'select:not(.no-select2,.no-chosen)');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$cmpParams = SabullvialHelper::getComponentParams();
$diasParaExpiracion = $cmpParams->get('tareas_dias_alerta_expiracion', 3);
$oneDayInSeconds = 24 * 60 * 60;
$todayTime = strtotime(date('Y-m-d'));

$model = ListModel::getInstance('TareasUsuarios', 'SabullvialModel', ['ignore_request' => true]);

// Instancio para poder usar las constantes TASK_TYPE_X
Table::getInstance('Tarea', 'SabullvialTable');
?>
<form action="index.php?option=com_sabullvial&view=tareas" method="post" id="adminForm" name="adminForm">
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
                        <?php
                            /*
                            <th width="1%" class="nowrap center">
                                <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
                            </th>
                            */
                        ?>
                        <th class="nowrap center" width="1%" style="min-width: 145px;">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_TAREA_TAREA', 'task_type', $listDirn, $listOrder); ?>
                        </th>
                        <th width="20%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_TAREA_CLIENTE', 'cliente', $listDirn, $listOrder); ?>
                        </th>
                        <th width="8%" class="nowrap text-left">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_TAREA_COTIZACION', 'id_cotizacion', $listDirn, $listOrder); ?>
                        </th>
                        <th width="6%" class="nowrap center">
                            <?php echo Text::_('COM_SABULLVIAL_TAREA_USUARIOS'); ?>
                        </th>
                        <th width="2%" class="center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_TAREA_START_DATE', 'start_date', $listDirn, $listOrder); ?>
                        </th>
                        <th width="2%" class="center">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_TAREA_EXPIRATION_DATE', 'expiration_date', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone center">
                            <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
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
                        <?php foreach ($this->items as $i => $row) :
                            $model->setState('filter.id_tarea', $row->id);
                            $usuarios = $model->getItems();
                            $link = JRoute::_('index.php?option=com_sabullvial&task=tarea.edit&id=' . $row->id);
                            ?>
                            <tr>
                                <td class="center">
                                    <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                                </td>
                                <td class="small center">
                                    <?php if ($row->task_type == SabullvialTableTarea::TASK_TYPE_LLAMADA): ?>
                                        <?php if (empty($row->task_value)): ?>
                                            <a href="#" class="btn btn-small hasTooltip disabled" title="<?php echo Text::_('COM_SABULLVIAL_TAREA_TELEFONO_VACIO_DESC'); ?>">
                                                <span class="icon-phone" aria-hidden="true"></span>
                                                <?php echo Text::_('COM_SABULLVIAL_TAREA_LLAMAR'); ?>
                                            </a>
                                            <div class="small mt-sm1">
                                                <span class="muted">Teléfono:</span>
                                                <?php echo Text::_('COM_SABULLVIAL_TAREA_TELEFONO_VACIO_DESC'); ?>
                                            </div>
                                        <?php else: ?>
                                            <a class="btn btn-default btn-small hasTooltip" href="tel:<?php echo $row->task_value; ?>" title="<?php echo Text::sprintf('COM_SABULLVIAL_TAREA_LLAMAR_A', $row->task_value); ?>">
                                                <span class="icon-phone text-success" aria-hidden="true"></span>
                                                <?php echo Text::_('COM_SABULLVIAL_TAREA_LLAMAR'); ?>
                                            </a>
                                            <div class="small mt-sm1">
                                                <span class="muted"><?php echo Text::_('COM_SABULLVIAL_TAREA_TELEFONO'); ?>:</span>
                                                <?php echo $row->task_value; ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php elseif ($row->task_type == SabullvialTableTarea::TASK_TYPE_NOTIFICAR_POR_EMAIL): ?>
                                        <a class="btn btn-default btn-small hasTooltip" href="mailto:<?php echo $row->task_value; ?>" title="<?php echo Text::sprintf('COM_SABULLVIAL_TAREA_NOTIFICAR_POR_EMAIL_A', $row->task_value); ?>">
                                            <span class="icon-mail text-info" aria-hidden="true"></span>
                                            <?php echo Text::_('COM_SABULLVIAL_TAREA_NOTIFICAR_POR_EMAIL'); ?>
                                        </a>
                                        <div class="small mt-sm1">
                                            <span class="muted"><?php echo Text::_('COM_SABULLVIAL_TAREA_EMAIL'); ?>:</span>
                                            <?php echo $row->task_value; ?>
                                        </div>
                                    <?php elseif ($row->task_type == SabullvialTableTarea::TASK_VALUE_ACTION_APROBAR_CLIENTE): ?>
                                        <span class="icon-save text-success" aria-hidden="true"></span>
                                        <?php echo Text::_('COM_SABULLVIAL_TAREA_APROBAR_CLIENTE'); ?>
                                    <?php elseif ($row->task_type == SabullvialTableTarea::TASK_TYPE_ACTION): ?>
                                        <span class="icon-info text-info" aria-hidden="true"></span> <?php echo $row->task_value; ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                        $hasCliente = !empty($row->cliente);
                                        $hasClienteSistema = !empty($row->cliente_sistema);
                                    ?>
                                    <div>
                                        <?php if ($hasCliente && $hasClienteSistema): ?>
                                            <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                                <?php echo $row->cliente; ?> <span class="muted small">[tango]</span>
                                            </a>
                                            <div>
                                                <?php echo $row->cliente_sistema; ?> <span class="muted small">[sistema]</span>
                                            </div>
                                            <?php if (!empty($row->regla)): ?>
                                                <div class="small muted">
                                                    Regla: <?php echo $row->regla; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif ($hasCliente): ?>
                                            <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                                <?php echo $row->cliente; ?> <span class="muted small">[tango]</span>
                                            </a>
                                            <?php if (!empty($row->regla)): ?>
                                                <div class="small muted">
                                                    Regla: <?php echo $row->regla; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                                                <?php echo $row->cliente_sistema; ?> <span class="muted small">[sistema]</span>
                                            </a>
                                            <?php if (!empty($row->regla)): ?>
                                                <div class="small muted">
                                                    Regla: <?php echo $row->regla; ?>
                                                </div>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="btn-group">
                                        <?php if ($row->notas_count > 0): ?>
                                            <?php $title = JText::plural('COM_SABULLVIAL_TAREAS_NOTAS', $row->notas_count); ?>
                                            <button type="button" data-target="#tareaModal_<?php echo $row->id; ?>" id="modal-_<?php echo $row->id; ?>" data-toggle="modal" class="hasTooltip btn btn-mini" title="<?php echo $title; ?>">
                                                <span class="icon-drawer-2" aria-hidden="true"></span>
                                                <span class="hidden-phone"><?php echo $title; ?></span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="small text-left">
                                    <?php if ($row->id_cotizacion): ?>
                                        <?php
                                            $isSincWithTango = !SabullvialHelper::isTangoFechaSincronizacionNull($row->cotizacion_tango_fecha_sincronizacion);

                                            $class = 'label hasTooltip';
                                            $text = Text::_('COM_SABULLVIAL_COTIZACIONES_TANGO_SIN_ENVIAR');
                                            if ($row->cotizacion_tango_enviar && $isSincWithTango) {
                                                $class .= ' label-success';
                                                $date = JHtml::_('date', $row->cotizacion_tango_fecha_sincronizacion, JText::_('DATE_FORMAT_LC5'), null);
                                                $text = Text::sprintf('COM_SABULLVIAL_COTIZACIONES_TANGO_SINCRONIZADO', $date, null, true);
                                            } elseif ($row->cotizacion_tango_enviar && !$isSincWithTango) {
                                                $class .= ' label-warning';
                                                $text = Text::_('COM_SABULLVIAL_COTIZACIONES_TANGO_ENVIADO');
                                            }
                                        ?>
                                        <span class="<?php echo $class;?>" title="<?php echo $text; ?>">
                                            <?php echo $row->id_cotizacion; ?>
                                        </span>
                                        <br/>

                                        <span class="label mt-sm1" style="background-color: <?php echo $row->estadocotizacion_bg_color; ?>; color: <?php echo $row->estadocotizacion_color; ?>">
                                            <?php echo $row->estadocotizacion; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="center small nowrap">
                                    <?php $countUsuarios = count($usuarios); ?>
                                    <?php if ($countUsuarios == 0): ?>
                                        -
                                    <?php else: ?>
                                        <ul class="unstyled mb-0">
                                            <?php foreach ($usuarios as $usuario): ?>
                                                <li><?php echo $usuario->user; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                    <div class="small muted">
                                        Grupo: <?php echo $row->group; ?>
                                    </div>
                                </td>
                                <td class="small center">
                                    <?php
                                        $startDateTime = strtotime($row->start_date);
                                        echo $row->start_date > 0 ?  date(Text::_('DATE_FORMAT_LC4'), $startDateTime) : '-';
                                    ?>
                                </td>
                                <td class="small center">
                                <?php echo JLayoutHelper::render('joomla.content.tareas.badge-expiration-date', [
                                        'expirationDate' => $row->expiration_date,
                                        'daysToConsiderExpired' => $diasParaExpiracion,
                                        'showLabel' => true
                                    ]); ?>
                                </td>
                                <td class="center hidden-phone small">
                                    <?php echo $row->id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
</form>