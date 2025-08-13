<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

JLoader::register('SabullvialModelHojaDeRuta', JPATH_COMPONENT . '/models/hojaderuta.php');

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_sabullvial&view=hojasderuta" method="post" id="adminForm" name="adminForm">
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
                    <th width="1%" class="center">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="1%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="center">
                        <?php echo JText::_('JACTION_PRINT'); ?>
                    </th>
                    <th width="3%" class="nowrap center">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_HOJAS_DE_RUTA_ESTADO', 'published', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_HOJAS_DE_RUTA_DELIVERY_DATE', 'delivery_date', $listDirn, $listOrder); ?>
                    </th>
                    <th class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_HOJAS_DE_RUTA_CHOFER', 'chofer', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_HOJAS_DE_RUTA_PATENTE', 'patente', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_HOJAS_DE_RUTA_CANTIDAD_REMITOS', 'count_remitos', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_AUTHOR', 'author', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
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
                            $link = JRoute::_('index.php?option=com_sabullvial&task=hojaderuta.edit&layout=view&id=' . $row->id);
                            $linkEdit = JRoute::_('index.php?option=com_sabullvial&task=hojaderuta.edit&id=' . $row->id);
                            $linkPrint = JRoute::_('index.php?option=com_sabullvial&view=hojaderuta&format=pdf&layout=print&id=' . $row->id);
                        ?>
                        <tr>
                            <td class="center">
                                <?php if ($row->published == SabullvialModelHojaDeRuta::ESTADO_CREADO): ?>
                                    <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                                <?php endif; ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo $row->id; ?>
                            </td>
                            <td class="center">
                                <div class="btn-group">
                                    <a class="btn btn-micro hasTooltip" href="<?php echo $linkPrint; ?>" target="_blank" data-original-title="<?php echo JText::_('JACTION_PRINT'); ?>">
                                        <span class="icon-print" aria-hidden="true"></span>
                                    </a>
                                </div>
                            </td>
                            <td class="center">
                                <?php
                                    if ($row->published == 1) {
                                        $estadoLabel = JText::_('COM_SABULLVIAL_HOJAS_DE_RUTA_ESTADO_PUBLICADA');
                                        $estadoColor = '#ffffff';
                                        $estadoBgColor = '#339c03';
                                    } else {
                                        $estadoLabel = JText::_('COM_SABULLVIAL_HOJAS_DE_RUTA_ESTADO_ANULADA');
                                        $estadoColor = '#ffffff';
                                        $estadoBgColor = '#bd362f';
                                    }
                                ?>
                                <span class="label" style="background-color: <?php echo $estadoBgColor; ?>; color: <?php echo $estadoColor; ?>">
                                    <?php echo $estadoLabel; ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?php echo $linkEdit; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_VIEW_DETAIL'); ?>">
                                    <?php echo HtmlHelper::date($row->delivery_date, Text::_('DATE_FORMAT_LC4'), null); ?>
                                </a>
                            </td>
                            <td>
                                <?php echo $row->chofer; ?>
                            </td>
                            <td class="small">
                                <?php echo $row->patente; ?>
                            </td>
                            <td class="small center">
                                <span class="badge badge-info"><?php echo $row->count_remitos; ?></span>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $row->author; ?>
                            </td>
                            <td class="nowrap small hidden-phone">
                                <?php echo $row->created > 0 ? JHtml::_('date', $row->created, JText::_('DATE_FORMAT_LC5')) : '-'; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" class="center muted">
                            <?php echo JText::_('COM_SABULLVIAL_HOJAS_DE_RUTA_SIN_HOJAS_DE_RUTA'); ?>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <input type="hidden" name="task" value="" />
    <input type="hidden" name="boxchecked" value="0" />
    <?php echo JHtml::_('form.token'); ?>
</form>