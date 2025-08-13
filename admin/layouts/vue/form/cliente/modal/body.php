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

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : [];

if (is_array($data['options'])) {
    $data['options'] = new Registry($data['options']);
}

$vModel = 'cliente' . $data['options']->get('id', '0');
$onChangeCliente = $data['options']->get('onChangeCliente', '');

if (!empty($onChangeCliente)) {
    $onChangeCliente = 'v-on:change="'.$onChangeCliente.'(modalCliente)"';
}

$modalId = 'selectCliente' . $data['options']->get('id', '0');
?>
<div class="contentpane component">
    <div class="container-popup">

        <div class="form form-vertical">

            <div class="row-fluid">
                <div class="control-group">
                    <div class="control-label">
                        <label>Cliente</label>
                    </div>
                    <div class="controls">
                        <select2-ajax data-test-id="cliente" v-model="modalCliente" parent="<?php echo '#' . $modalId; ?>" track-by="id" url="index.php?option=com_sabullvial&task=puntosdeventa.listClientes" class="no-chosen reduce-br modal-cliente" placeholder="- Seleccione un cliente -" <?php echo $onChangeCliente; ?>>
                            <option value=''><?php echo JText::_('COM_SABULLVIAL_CONSUMIDOR_FINAL'); ?></option>
                        </select2-ajax>
                    </div>
                </div>

                <div v-if="modalCliente == ''" class="control-group">
                    <div class="control-label">
                        <label><?php echo JText::_('COM_SABULLVIAL_CONSUMIDOR_FINAL'); ?></label>
                    </div>
                    <div class="controls">
                        <input type="text" v-model="quotation.consumidorFinal" class="span12" placeholder="Nombre" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>