<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$idCotizacionEstadoCreado = $this->state->params->get('cotizacion_estado_creado');
$idCotizacionOrdenDeTrabajoEstadoCreado = $this->state->params->get('orden_de_trabajo_estado_creado');
?>
<template v-if="afterCreatedCotizacionStep == 1">
    <button type="button" class="btn font-small@xs btn-danger" @click="downloadCotizacion" :disabled="!quotationCreated.id">
        <span class="icon-download"></span>
        <?php echo Text::_('JACTION_DOWNLOAD'); ?>
    </button>
    <button type="button" class="btn font-small@xs btn-info" @click="openCotizacion" :disabled="!quotationCreated.id">
        <span class="icon-file-2"></span>
        <?php echo Text::_('JACTION_OPEN'); ?>
    </button>
    <?php if (SabullvialButtonsHelper::canEnviarAFacturacion($idCotizacionEstadoCreado) || SabullvialButtonsHelper::canEnviarAFacturacion($idCotizacionOrdenDeTrabajoEstadoCreado)): ?>
        <button v-if="canSendToFacturacion && !haveCartCustomProducts" type="button" class="btn font-small@xs btn-default" @click="afterCreatedCotizacionStep = 2">
            <span class="icon-share"></span>
            <?php echo Text::_('JACTION_SEND_TO_FACTURACION'); ?>
        </button>
    <?php endif; ?>
</template>
<template v-else-if="afterCreatedCotizacionStep == 2">
    <button type="button" class="btn font-small@xs btn-default pull-left" @click="afterCreatedCotizacionStep = 1">
        <span class="icon-back"></span>
        <?php echo Text::_('JTOOLBAR_BACK'); ?>
    </button>
</template>