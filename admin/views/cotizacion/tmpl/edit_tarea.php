<?php
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Table\Table;

$modelTareasUsuarios = ListModel::getInstance('TareasUsuarios', 'SabullvialModel', ['ignore_request' => true]);

$cmpParams = SabullvialHelper::getComponentParams();
$diasParaExpiracion = $cmpParams->get('tareas_dias_alerta_expiracion', 3);

Table::getInstance('Tarea', 'SabullvialTable');

$uri = JUri::getInstance();
$uri->setVar('id', $this->item->id);
$uri->setVar('task', 'cotizacion.edit');
$uri->setVar('view', null);
$uri->setVar('layout', null);

$return = $uri->toString();
?>
<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="nowrap center" width="1%" style="min-width: 145px;">
                    <?php echo Text::_('COM_SABULLVIAL_TAREA_TAREA'); ?>
                </th>
                <th width="20%" class="nowrap">
                    <?php echo Text::_('COM_SABULLVIAL_TAREA_CLIENTE'); ?>
                </th>
                <th width="2%" class="nowrap center">
                    <?php echo Text::_('COM_SABULLVIAL_TAREA_COTIZACION'); ?>
                </th>
                <th width="6%" class="nowrap center">
                    <?php echo Text::_('COM_SABULLVIAL_TAREA_USUARIOS'); ?>
                </th>
                <th width="2%" class="center">
                    <?php echo Text::_('COM_SABULLVIAL_TAREA_START_DATE'); ?>
                </th>
                <th width="2%" class="center">
                    <?php echo Text::_('COM_SABULLVIAL_TAREA_EXPIRATION_DATE'); ?>
                </th>
                <th width="1%" class="nowrap hidden-phone center">
                    <?php echo Text::_('JGRID_HEADING_ID'); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($this->tareas)) : ?>
                <?php foreach ($this->tareas as $i => $row) :
                    $modelTareasUsuarios->setState('filter.id_tarea', $row->id);
                    $usuarios = $modelTareasUsuarios->getItems();
                    $link = JRoute::_('index.php?option=com_sabullvial&task=tarea.edit&id=' . $row->id . '&return=' . urlencode(base64_encode($return)));
                    ?>
                    <tr>
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
                                    <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('COM_SABULLVIAL_COTIZACION_EDIT_TAREA'); ?>">
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
                                    <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('COM_SABULLVIAL_COTIZACION_EDIT_TAREA'); ?>">
                                        <?php echo $row->cliente; ?> <span class="muted small">[tango]</span>
                                    </a>
                                    <?php if (!empty($row->regla)): ?>
                                        <div class="small muted">
                                            Regla: <?php echo $row->regla; ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('COM_SABULLVIAL_COTIZACION_EDIT_TAREA'); ?>">
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
                        <td class="small center">
                            <?php echo $row->id_cotizacion; ?>
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