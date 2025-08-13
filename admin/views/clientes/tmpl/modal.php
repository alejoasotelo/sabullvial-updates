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

JHtml::_('behavior.core');
JHtml::_('bootstrap.tooltip');
JHtml::_('formbehavior.chosen', 'select');

// Special case for the search field tooltip.
$searchFilterDesc = $this->filterForm->getFieldAttribute('search', 'description', null, 'filter');
JHtml::_('bootstrap.tooltip', '#filter_search', ['title' => JText::_($searchFilterDesc), 'placement' => 'bottom']);

$input           = JFactory::getApplication()->input;
$field           = $input->getCmd('field');
$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$onClick = '';//"window.parent.jSelectUser(this);window.parent.jQuery('.modal.in').modal('hide');";
?>
<div class="container-popup">
    <form action="index.php?option=com_sabullvial&view=clientes&layout=modal&tmpl=component" method="post" id="adminForm" name="adminForm">
        <div id="j-main-container">
            <div class="row-fluid">
                <div class="span12">
                    <?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
                </div>
            </div>
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th style="min-width:100px" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_RAZON_SOCIAL', 'razon_social', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_CODIGO_VENDEDOR', 'codigo_vendedor', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_SALDO', 'saldo', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'JGLOBAL_CREATED_DATE', 'fecha_alta', $listDirn, $listOrder); ?>
                        </th>
                        <th width="1%" class="nowrap hidden-phone">
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_CLIENTES_COD_CLIENTE', 'codcli', $listDirn, $listOrder); ?>
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
                        <?php foreach ($this->items as $i => $row) :
                            $link = JRoute::_('index.php?option=com_sabullvial&task=cliente.edit&id=' . $row->codcli);
                            ?>
                            <tr>
                                <td>
                                    <a class="pointer button-select" href="#" 
                                        data-cliente-value="<?php echo $row->id; ?>" 
                                        data-cliente-name="<?php echo $this->escape($row->razon_social); ?>"
                                        data-cliente-cuit="<?php echo $this->escape($row->documento_numero); ?>"
                                        data-cliente-codigo_vendedor="<?php echo $this->escape($row->codigo_vendedor); ?>"
                                        data-cliente-cod="<?php echo $this->escape($row->codcli); ?>"
                                        data-cliente-saldo="<?php echo PriceHelper::format($row->saldo);?>"
                                        data-cliente-field="<?php echo $this->escape($field); ?>" onclick="<?php echo $onClick; ?>">
                                        <?php echo $this->escape($row->razon_social); ?>
                                    </a>
                                    <div class="small">
                                        <span class="muted"><?php echo JText::_('COM_SABULLVIAL_CLIENTES_DOCUMENTO_NUMERO'); ?>: </span>
                                        <span><?php echo $row->documento_numero; ?></span>
                                    </div>
                                </td>
                                <td class="small">
                                    <?php echo $row->codigo_vendedor; ?>
                                </td>
                                <td class="small">
                                    <span class="product-price"><?php echo PriceHelper::format($row->saldo); ?></span>
                                </td>
                                <td class="center">
                                    <?php echo $row->codcli; ?>
                                </td>
                                <td class="center">
                                    <?php echo $row->id; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="field" value="<?php echo $this->escape($field); ?>" />
        <input type="hidden" name="boxchecked" value="0"/>
        <?php echo JHtml::_('form.token'); ?>
    </form>
</div>