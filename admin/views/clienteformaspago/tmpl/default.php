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

JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_sabullvial&view=clienteformaspago" method="post" id="adminForm" name="adminForm">
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
                    <th width="1%" class="nowrap center hidden-phone">
                        <?php echo JText::_('COM_SABULLVIAL_NUM'); ?>
                    </th>
                    <th width="1%" class="center">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="1%" class="nowrap center">					
                        <?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'published', $listDirn, $listOrder); ?>
                    </th>
                    <th style="min-width:100px" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTE_FORMAS_PAGO_NAME', 'name', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_AUTHOR', 'author', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $listDirn, $listOrder); ?>
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
                            $link = JRoute::_('index.php?option=com_sabullvial&task=clienteformapago.edit&id=' . $row->id);
                        ?>
                        <tr>
                            <td class="center hidden-phone">
                                <?php echo $this->pagination->getRowOffset($i); ?>
                            </td>
                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                            </td>
                            <td class="center">
                                <?php echo JHtml::_('jgrid.published', $row->published, $i, 'clienteformaspago.', true, 'cb'); ?>
                            </td>
                            <td>
                                <a href="<?php echo $link; ?>" class="hasTooltip" title="<?php echo JText::_('JACTION_EDIT'); ?>">
                                    <?php echo $this->escape($row->name); ?>
                                </a>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $row->author; ?>
                            </td>
                            <td class="nowrap small hidden-phone">
                                <?php
                                    echo $row->created > 0 ? JHtml::_('date', $row->created, JText::_('DATE_FORMAT_LC5')) : '-';
                                ?>
                            </td>
                            <td class="center hidden-phone">
                                <?php echo $row->id; ?>
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