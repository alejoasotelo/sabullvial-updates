<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ListModel;

defined('_JEXEC') or die;

extract($displayData);
// Instancio para poder usar las constantes TASK_TYPE_X
Table::getInstance('Tarea', 'SabullvialTable');


$model = ListModel::getInstance('TareasUsuarios', 'SabullvialModel', ['ignore_request' => true]);
$model->setState('filter.id_tarea', $tarea->id);
$usuarios = $model->getItems();

$cmpParams = SabullvialHelper::getComponentParams();
$diasParaExpiracion = $cmpParams->get('tareas_dias_alerta_expiracion', 3);
$oneDayInSeconds = 24 * 60 * 60;
$todayTime = strtotime(date('Y-m-d'));
?>

<div class="row-fluid mb-sm2">
    <?php if ($tarea->task_type == SabullvialTableTarea::TASK_TYPE_LLAMADA) : ?>
        <?php if (empty($tarea->task_value)) : ?>
            <a href="#" class="btn btn-small hasTooltip disabled" title="<?php echo Text::_('COM_SABULLVIAL_TAREA_TELEFONO_VACIO_DESC'); ?>">
                <span class="icon-phone" aria-hidden="true"></span>
                <?php echo Text::_('COM_SABULLVIAL_TAREA_LLAMAR'); ?>
            </a>
            <div class="small mt-sm1">
                <span class="muted">Teléfono:</span>
                <?php echo Text::_('COM_SABULLVIAL_TAREA_TELEFONO_VACIO_DESC'); ?>
            </div>
        <?php else : ?>
            <a class="btn btn-default btn-small hasTooltip" href="tel:<?php echo $tarea->task_value; ?>" title="<?php echo Text::sprintf('COM_SABULLVIAL_TAREA_LLAMAR_A', $tarea->task_value); ?>">
                <span class="icon-phone text-success" aria-hidden="true"></span>
                <?php echo Text::_('COM_SABULLVIAL_TAREA_LLAMAR'); ?>
            </a>
            <div class="small mt-sm1">
                <span class="muted"><?php echo Text::_('COM_SABULLVIAL_TAREA_TELEFONO'); ?>:</span>
                <?php echo $tarea->task_value; ?>
            </div>
        <?php endif; ?>
    <?php elseif ($tarea->task_type == SabullvialTableTarea::TASK_TYPE_NOTIFICAR_POR_EMAIL) : ?>
        <a class="btn btn-default btn-small hasTooltip" href="mailto:<?php echo $tarea->task_value; ?>" title="<?php echo Text::sprintf('COM_SABULLVIAL_TAREA_NOTIFICAR_POR_EMAIL_A', $tarea->task_value); ?>">
            <span class="icon-mail text-info" aria-hidden="true"></span>
            <?php echo Text::_('COM_SABULLVIAL_TAREA_NOTIFICAR_POR_EMAIL'); ?>
        </a>
        <div class="small mt-sm1">
            <span class="muted"><?php echo Text::_('COM_SABULLVIAL_TAREA_EMAIL'); ?>:</span>
            <?php echo $tarea->task_value; ?>
        </div>
    <?php elseif ($tarea->task_type == SabullvialTableTarea::TASK_VALUE_ACTION_APROBAR_CLIENTE) : ?>
        <span class="icon-save text-success" aria-hidden="true"></span>
        <?php echo Text::_('COM_SABULLVIAL_TAREA_APROBAR_CLIENTE'); ?>
    <?php elseif ($tarea->task_type == SabullvialTableTarea::TASK_TYPE_ACTION) : ?>
        <span class="icon-info text-info" aria-hidden="true"></span> <?php echo $tarea->task_value; ?>
    <?php endif; ?>
</div>

<div class="row-fluid mb-sm2">
    <div class="mb-sm1">
        <b><?php echo Text::_('COM_SABULLVIAL_TAREA_POPOVER_FECHA_INICIO'); ?>:</b>
    </div>
    <?php echo $tarea->start_date > 0 ? JHtml::_('date', $tarea->start_date, Text::_('DATE_FORMAT_LC4')) : '-'; ?>
</div>

<div class="row-fluid mb-sm2">
    <div class="mb-sm1">
        <b><?php echo Text::_('COM_SABULLVIAL_TAREA_POPOVER_FECHA_EXPIRACION'); ?>:</b>
    </div>

    <?php
    $expirationDate = $tarea->expiration_date > 0 ? JHtml::_('date', $tarea->expiration_date, Text::_('DATE_FORMAT_LC4')) : '-';

    $expirationDateTime = strtotime(date('Y-m-d', strtotime($tarea->expiration_date))); // elimino la hora
    $daysToExpire = ($expirationDateTime - $todayTime) / $oneDayInSeconds;
    $isExpired = $daysToExpire <= 0;
    $isNearToExpire = $daysToExpire <= $diasParaExpiracion;
    ?>
    <?php if ($isExpired) : ?>
        <span class="label label-important">
            <span class="icon-clock" aria-hidden="true"></span>
            <?php echo $expirationDate; ?>
        </span>
        <span class="small muted">
            <?php echo Text::sprintf('COM_SABULLVIAL_TAREA_TAREA_EXPIRADA'); ?>
        </span>
    <?php elseif ($isNearToExpire) : ?>
        <?php $text = 'COM_SABULLVIAL_TAREA_TAREA_CERCA_DE_EXPIRAR' . ($daysToExpire > 1 ? '_PLURAL' : ''); ?>
        <span class="label label-warning">
            <span class="icon-clock" aria-hidden="true"></span>
            <?php echo $expirationDate; ?>
        </span>
        <span class="small muted">
            <?php echo Text::sprintf($text, $daysToExpire); ?>
        </span>
    <?php else : ?>
        <span class="label label-info">
            <span class="icon-clock" aria-hidden="true"></span>
            <?php echo $expirationDate; ?>
        </span>
    <?php endif; ?>
</div>

<div class="row-fluid">
    <?php $countUsuarios = count($usuarios); ?>
    <?php if ($countUsuarios == 0) : ?>
        -
    <?php elseif ($countUsuarios == 1) : ?>
        <div class="mb-sm1">
            <b><?php echo Text::_('COM_SABULLVIAL_TAREA_POPOVER_VENDEDOR'); ?>:</b>
        </div>
        <?php echo $usuarios[0]->user; ?>
    <?php else : ?>
        <div class="mb-sm1">
            <b><?php echo Text::_('COM_SABULLVIAL_TAREA_POPOVER_VENDEDORES'); ?>:</b>
        </div>
        <ul class="mb-0">
            <?php foreach ($usuarios as $usuario) : ?>
                <li><?php echo $usuario->user; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>