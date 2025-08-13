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

JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$vendedor = SabullvialHelper::getVendedor();
$isRevendedor = $vendedor->get('esRevendedor', false);
?>
<form action="index.php?option=com_sabullvial&view=clientestango" method="post" id="adminForm" name="adminForm">
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
                    <th width="1%" class="center">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_TANGO_COD_CLIENTE', 'codcli', $listDirn, $listOrder); ?>
                    </th>
                    <th style="min-width:100px" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_RAZON_SOCIAL', 'razon_social', $listDirn, $listOrder); ?>
                    </th>
                    <th width="5%" class="center">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_CODIGO_VENDEDOR', 'codigo_vendedor', $listDirn, $listOrder); ?>
                    </th>
                    <?php if (!$isRevendedor): ?>
                        <th width="10%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_CONDICION_VENTA', 'condicion_venta', $listDirn, $listOrder); ?>
                        </th>
                    <?php endif; ?>
                    <th width="5%" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_SALDO', 'saldo', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $row) : ?>
                        <?php
                            $link = JRoute::_('index.php?option=com_sabullvial&task=clientetango.edit&id=' . $row->codcli);
                        ?>
                        <tr>
                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $row->codcli); ?>
                            </td>
                            <td class="center">
                                <?php echo $row->codcli; ?>
                            </td>
                            <td>
                                <?php echo $this->escape($row->razon_social); ?>
                                <div class="small">
                                    <span class="muted"><?php echo JText::_('COM_SABULLVIAL_CLIENTES_CUIT'); ?>: </span>
                                    <span><?php echo $row->cuit; ?></span>
                                </div>
                            </td>
                            <td class="small center">
                                <?php echo $row->codigo_vendedor; ?>
                            </td>
                            <?php if (!$isRevendedor): ?>
                                <td class="small nowrap">
                                    <?php echo preg_replace('/(\s|[^\.\-\/A-Za-z0-9\-])+/', ' ', $row->condicion_venta); ?>
                                </td>
                            <?php endif; ?>
                            <td class="small">
                                <span class="product-price"><?php echo PriceHelper::format($row->saldo); ?></span>
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