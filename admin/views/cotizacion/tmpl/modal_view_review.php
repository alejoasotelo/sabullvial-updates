<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\HTML\HTMLHelper;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');

$document = JFactory::getDocument();
$document->addScriptOptions('com_sabullvial', [
    'cotizacion' => $this->item
]);

$isVueProd = JComponentHelper::getParams('com_sabullvial')->get('vue_production', false);
HTMLHelper::script('com_sabullvial/vue.' . ($isVueProd ? 'global.prod.js' : 'global.js'), ['version' => 'auto', 'relative' => true]);
HTMLHelper::script('com_sabullvial/modal_review.js', ['version' => 'auto', 'relative' => true]);

$app = JFactory::getApplication();
$input = $app->input;

JFactory::getDocument()->addStyleDeclaration('
    .width-64px{
        width: 64px;
    }
    .box-shadow-remove{-moz-box-shadow: none; -webkit-box-shadow: none; box-shadow: none;}
    .b-0{border: 0px;}
    .p-0{padding: 0px !important;}
');

$faltantes = $this->getModel('Cotizacion')->getFaltantes($this->item->id);
?>
<div id="modalViewReviewApp" class="container-popup">

    <div class="form-horizontal">

        <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'general']); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_SABULLVIAL_COTIZACION_DETAILS')); ?>

        <div class="row-fluid form-horizontal-desktop">
            <div class="span12">
                <p><?php echo JText::_('COM_SABULLVIAL_COTIZACION_MODAL_VIEW_REVIEW_DETAILS_DESC'); ?></p>
                <table class="adminlist table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th width="1%"><?php echo JText::_('COM_SABULLVIAL_COTIZACION_ESTADO'); ?></th>
                            <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CODIGO'); ?></th>
                            <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_NOMBRE'); ?></th>
                            <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CANTIDAD_DISPONIBLE'); ?></th>
                            <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CANTIDAD_REQUERIDA'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($this->item->revisiondetalle as $detalle) : ?>
                            <?php
                                $hasFaltantes = $detalle['cantidad'] < $detalle['cantidad_requerida'];
                            $icon = $hasFaltantes ? 'icon-warning' : 'icon-publish';
                            $suffix = ($hasFaltantes ? 'CON' : 'SIN') . '_FALTANTES';
                            $title = JText::_('COM_SABULLVIAL_COTIZACION_MODAL_VIEW_REVIEW_PRODUCTO_'.$suffix);
                            ?>
                            <tr <?php echo $hasFaltantes ? 'class="warning"' : '';?> >
                                <td class="center">
                                    <a href="javascript::void();" class="<?php echo $icon; ?> hasTooltip" title="<?php echo $title; ?>"></a>
                                </td>
                                <td class="small"><?php echo $detalle['codigo_sap']; ?></td>
                                <td><?php echo $detalle['nombre']; ?></td>
                                <td class="center small"><?php echo $detalle['cantidad']; ?></td>
                                <td class="center small"><?php echo $detalle['cantidad_requerida']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php if (count($faltantes)) : ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'pending', JText::_('COM_SABULLVIAL_COTIZACION_PRODUCTOS_FALTANTES')); ?>

            <div class="row-fluid form-horizontal-desktop">
                <div class="span12">
                    <table class="adminlist table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CODIGO'); ?></th>
                                <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_NOMBRE'); ?></th>
                                <th><?php echo JText::_('COM_SABULLVIAL_COTIZACION_CANTIDAD'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faltantes as $detalle) : ?>
                                <tr>
                                    <td><?php echo $detalle['codigo_sap']; ?></td>
                                    <td><?php echo $detalle['nombre']; ?></td>
                                    <td class="center"><?php echo $detalle['cantidad_faltante']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <button type="button" class="btn btn-primary" onclick="window.parent.createWithFaltantes(<?php echo $this->item->id;?>);"><?php echo JText::_('COM_SABULLVIAL_COTIZACION_MODAL_VIEW_REVIEW_GENERAR_COTIZACION_CON_FALTANTES'); ?></button>
                </div>
            </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

</div>