<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

$data = $displayData;

// Receive overridable options
$data['options'] = !empty($data['options']) ? $data['options'] : [];

if (is_array($data['options'])) {
    $data['options'] = new Registry($data['options']);
}

$modalId = 'selectCliente' . $data['options']->get('id', '0');
$vModel = $data['options']->get('v-model', '');
$vModelConsumidorFinal = $data['options']->get('v-model-consumidor-final', '');
$onClickConsumidorFinal = $data['options']->get('onClickConsumidorFinal', '');
$onClickClear = $data['options']->get('onClickClear', '');
$disabled = $data['options']->get('disabled', '');

$disabled = empty($disabled) ? '' : ':disabled="' . $disabled . '"';

?>
<div class="input-append vue-form-cliente">
    <input v-if="<?php echo $vModel; ?> == ''" type="text" v-model="<?php echo $vModelConsumidorFinal; ?>" class="width-auto" <?php echo $disabled; ?> />
    <input v-else type="text" v-model="<?php echo $vModel; ?>.razon_social" :disabled="<?php echo $vModel; ?>.id" class="width-auto"  <?php echo $disabled; ?> />
    <button type="button" class="btn btn-primary button-select hasTooltip" data-title="Seleccionar cliente" data-toggle="modal" data-target="#<?php echo $modalId; ?>" <?php echo $disabled; ?>>
        <span class="icon-user" aria-hidden="true"></span>
    </button>
    <button type="button" @click="<?php echo $onClickConsumidorFinal; ?>" class="btn button-consumidor-final hasTooltip" data-title="Consumidor final" <?php echo $disabled; ?>>
        C.F.
    </button>
    <button type="button" @click="<?php echo $onClickClear; ?>" class="btn button-clear hasTooltip" aria-label="Limpiar" data-title="Limpiar" <?php echo $disabled; ?>>
        <span class="icon-remove" aria-hidden="true"></span>
    </button>
</div>