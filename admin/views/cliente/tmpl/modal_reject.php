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
use Joomla\CMS\HTML\HTMLHelper;

JLoader::register('SabullvialTableCliente', JPATH_COMPONENT . '/tables/cliente.php');

HTMLHelper::stylesheet('com_sabullvial/default.css', ['version' => 'auto', 'relative' => true]);
?>
<div id="modalApproveApp" class="container-popup">
    <div class="form">
        <div class="row-fluid form-horizontal-desktop">
            <div class="span6">
                <div class="well">
                    <h3 class="font-normal">Cliente</h3>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_RAZON_SOCIAL_LABEL'); ?></th>
                                <td><?php echo $this->item->razon_social; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_NOMBRE_COMERCIAL_LABEL'); ?></th>
                                <td><?php echo $this->item->nombre_comercial; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_CODIGO_VENDEDOR_LABEL'); ?></th>
                                <td><?php echo $this->item->codigo_vendedor; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_VENDEDOR_LABEL'); ?></th>
                                <td><?php echo !empty($this->item->codigo_vendedor) ? SabullvialTableCliente::CODIGO_VENDEDOR[$this->item->codigo_vendedor] : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_ACTIVIDAD_COMERCIAL_LABEL'); ?></th>
                                <td><?php echo $this->item->actividad_comercial; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_CODIGO_RUBRO_LABEL'); ?></th>
                                <td><?php echo $this->item->codigo_rubro ? SabullvialTableCliente::CODIGO_RUBRO[$this->item->codigo_rubro] : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_CODIGO_CATEGORIA_IVA_LABEL'); ?></th>
                                <td><?php echo $this->item->codigo_categoria_iva ? SabullvialTableCliente::CODIGO_CATEGORIA_IVA[$this->item->codigo_categoria_iva] : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_DOCUMENTO_TIPO_LABEL'); ?></th>
                                <td><?php echo $this->item->documento_tipo ? SabullvialTableCliente::DOCUMENTO_TIPO[$this->item->documento_tipo] : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_DOCUMENTO_NUMERO_LABEL'); ?></th>
                                <td><?php echo $this->item->documento_numero; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_CONDICION_DE_PAGO_LABEL'); ?></th>
                                <td>
                                    <?php if ($this->item->id_condicionventa) : ?>
                                        <?php
                                            echo JTable::getInstance('SitCondicionesVenta', 'SabullvialTable')->getLabelById($this->item->id_condicionventa)
                                        ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_CONDICION_DE_PAGO_DESEADO_LABEL'); ?></th>
                                <td><?php echo $this->item->condicionventa_deseada; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_FORMA_DE_PAGO_LABEL'); ?></th>
                                <td>
                                    <?php if (count($this->item->formapago)) : ?>
                                        <?php foreach ($this->item->formapago as $formapago) : ?>
                                            <?php
                                                $table = JTable::getInstance('FormaPago', 'SabullvialTable');
                                                $table->load($formapago);
                                                echo $table->nombre . '<br/>';
                                            ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_CUPO_DE_CREDITO_LABEL'); ?></th>
                                <td><?php echo $this->item->cupo_credito; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_CODIGO_ZONA_LABEL'); ?></th>
                                <td><?php echo $this->item->codigo_zona ? SabullvialTableCliente::CODIGO_ZONA[$this->item->codigo_zona] : ''; ?></td>
                            </tr>
                            <tr>
                                <th class="text-left"><?php echo Text::_('COM_SABULLVIAL_CLIENTE_FECHA_ALTA_LABEL'); ?></th>
                                <td><?php echo JHtml::_('date', $this->item->created, JText::_('DATE_FORMAT_LC5')); ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>