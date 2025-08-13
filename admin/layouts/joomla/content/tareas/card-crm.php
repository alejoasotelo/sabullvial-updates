<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

extract($displayData);

$cmpParams = SabullvialHelper::getComponentParams();
$daysToConsiderExpired = $cmpParams->get('tareas_dias_alerta_expiracion', 3);

$modelUsuarios = ListModel::getInstance('TareasUsuarios', 'SabullvialModel', ['ignore_request' => true]);
$modelUsuarios->setState('filter.id_tarea', $tarea->id);
$usuarios = $modelUsuarios->getItems();

?>
<div class="card-tarea">
    <div class="row">
        <span class="span6 text-success">
            <?php if ($tarea->task_type == SabullvialTableTarea::TASK_TYPE_LLAMADA) : ?>
                <span class="icon-phone" aria-hidden="true"></span>
                <b><?php echo Text::_('COM_SABULLVIAL_TAREA_LLAMAR'); ?></b>
            <?php elseif ($tarea->task_type == SabullvialTableTarea::TASK_TYPE_NOTIFICAR_POR_EMAIL) : ?>
                <span class="icon-mail" aria-hidden="true"></span>
                <b><?php echo Text::_('COM_SABULLVIAL_TAREA_NOTIFICAR_POR_EMAIL'); ?></b>
            <?php elseif ($tarea->task_type == SabullvialTableTarea::TASK_VALUE_ACTION_APROBAR_CLIENTE) : ?>
                <span class="icon-save" aria-hidden="true"></span>
                <b><?php echo Text::_('COM_SABULLVIAL_TAREA_APROBAR_CLIENTE'); ?></b>
            <?php elseif ($tarea->task_type == SabullvialTableTarea::TASK_TYPE_ACTION) : ?>
                <span class="icon-info" aria-hidden="true"></span>
                <b><?php echo Text::_('COM_SABULLVIAL_TAREA_ACCION'); ?></b>
            <?php endif; ?>
        </span>
        <span class="span6 text-right">
            <?php echo JLayoutHelper::render('joomla.content.tareas.badge-expiration-date', [
                'expirationDate' => $tarea->expiration_date,
                'daysToConsiderExpired' => $daysToConsiderExpired,
                'showLabel' => true,
            ]); ?>
        </span>
    </div>
    <div class="row">
        <div class="span12">
            <?php
            $hasCliente = !empty($tarea->cliente);
            $hasClienteSistema = !empty($tarea->cliente_sistema);
            $link = JRoute::_('index.php?option=com_sabullvial&task=tarea.edit&id=' . $tarea->id);
            ?>
            <?php if ($hasCliente && $hasClienteSistema) : ?>
                <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                    <?php echo $tarea->cliente; ?> <span class="muted small">[tango]</span>
                </a>
                <div>
                    <?php echo $tarea->cliente_sistema; ?> <span class="muted small">[sistema]</span>
                </div>
            <?php elseif ($hasCliente) : ?>
                <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                    <?php echo $tarea->cliente; ?> <span class="muted small">[tango]</span>
                </a>
            <?php else : ?>
                <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo Text::_('JACTION_EDIT'); ?>">
                    <?php echo $tarea->cliente_sistema; ?> <span class="muted small">[sistema]</span>
                </a>
            <?php endif; ?>
            <div class="tarea-task-value"><?php echo $tarea->task_value ?></div>
        </div>
    </div>
    <div class="row">
        <div class="text-right">
            <?php $countUsuarios = count($usuarios); ?>
            <?php if ($countUsuarios == 0) : ?>
                -
            <?php elseif ($countUsuarios == 1) : ?>
                <ul class="unstyled mb-0">
                    <li><span class="icon-user"></span> <?php echo $usuarios[0]->user; ?></li>
                </ul>
            <?php else : ?>
                <span class="icon-user"></span>
                <ul class="unstyled mb-0">
                    <?php foreach ($usuarios as $usuario) : ?>
                        <li><?php echo $usuario->user; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>