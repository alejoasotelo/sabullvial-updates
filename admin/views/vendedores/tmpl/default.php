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

use Joomla\CMS\Language\Text;

JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_sabullvial&view=vendedores" method="post" id="adminForm" name="adminForm">
    <div id="j-sidebar-container" class="span2">
        <?php echo JHtmlSidebar::render(); ?>
    </div>
    <div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="span12">
                <?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
            </div>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th style="min-width:100px" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_VENDEDORES_NAME', 'name', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_VENDEDORES_COTIZACIONES', 'cotizaciones', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_VENDEDORES_COTIZACIONES_APROBADAS', 'cotizaciones_aprobadas', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_VENDEDORES_COTIZACIONES_SIN_CONCRETAR', 'cotizaciones_sin_concretar', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_VENDEDORES_TASA_EFECTIVIDAD', 'cotizaciones_efectividad', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="7">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $row) : ?>
                        <?php
                            $link = JRoute::_('index.php?option=com_sabullvial&task=vendedor.edit&id=' . $row->id);
                        ?>
                        <tr>
                            <td>
                                <?php echo $this->escape($row->name); ?>
                            </td>
                            <td class="small">
                                <span class="badge <?php echo $row->cotizaciones > 0 ? 'badge-info' : ''; ?>">
                                    <?php echo $row->cotizaciones; ?>
                                </span>
                            </td>
                            <td class="small">
                                <span class="badge <?php echo $row->cotizaciones_aprobadas > 0 ? 'badge-success' : ''; ?>">
                                    <?php echo $row->cotizaciones_aprobadas; ?>
                                </span>								
                            </td>
                            <td class="small">
                                <span class="badge <?php echo $row->cotizaciones_sin_concretar > 0 ? 'badge-warning' : ''; ?>">
                                    <?php echo $row->cotizaciones_sin_concretar; ?>
                                </span>
                            </td>
                            <td class="small">
                                <span class="badge <?php echo $row->cotizaciones_efectividad > 0 ? 'badge-info' : ''; ?>">
                                    <?php echo Text::sprintf('COM_SABULLVIAL_VENDEDORES_TASA_EFECTIVIDAD_VALUE', round($row->cotizaciones_efectividad * 100, 0)); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo JHtml::_('form.token'); ?>
</form>