<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

$vendedor = SabullvialHelper::getVendedor();
$canCreateOrdenDeTrabajo = $vendedor->get('crear.ordenDeTrabajo', false);

defined('_JEXEC') or die;
?>
<button :disabled="saving" type="button" class="btn" data-dismiss="modal"><?php echo JText::_('JCANCEL'); ?></button>
<button v-if="!quotation.id || (quotation.id > 0 && !quotation.is_orden_de_trabajo)" :disabled="!isCotizacionFormValid || saving" type="button" class="btn btn-success" @click="save(TYPE_COTIZACION)" :disabled="">
    <span v-if="saving == TYPE_COTIZACION">
        Guardando...
    </span>
    <span v-else>
    <?php echo JText::_('JAPPLY'); ?>
    </span>
</button>

<?php if ($canCreateOrdenDeTrabajo): ?>
    <button v-if="!quotation.id || (quotation.id > 0 && quotation.is_orden_de_trabajo)" :disabled="!isCotizacionFormValid || saving" type="button" class="btn btn-primary" @click="save(TYPE_ORDEN_DE_TRABAJO)">
        <span v-if="saving == TYPE_ORDEN_DE_TRABAJO">
            <?php echo JText::_('JACTION_SAVING_ORDEN_DE_TRABAJO'); ?>
        </span>
        <span v-else>
            <?php echo JText::_('JACTION_SAVE_ORDEN_DE_TRABAJO'); ?>
        </span>
    </button>
<?php endif; ?>