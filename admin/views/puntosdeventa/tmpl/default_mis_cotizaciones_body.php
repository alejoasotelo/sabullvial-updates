<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\ListModel;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

$vendedor = SabullvialHelper::getVendedor();
$showButtonFilterByMyQuotes = !$vendedor->get('esRevendedor', false) && $vendedor->get('ver.presupuestos', false);

?>
<div class="contentpane component">
    <div class="container-popup">
        <quotes modal-id="myCotizaciones" @edit="edit" @duplicate="duplicate">
            <template v-slot:header="{ search, searchMyQuotes, searchFilters, clear, clearFilters, queryFilters, toggleFilters, showFilters, loading }">
                <div class="row-fluid mb-1">
                    <div class="span12">
                        <div class="js-stools clearfix js-stools-container-bar ">
                            <search modal-id="myCotizaciones" class="span9" @search="search" @clear="clear" show-btn-filters @toggle-filters="toggleFilters"></search>
                            <?php if ($showButtonFilterByMyQuotes) : ?>
                                <div class="span3">
                                    <div class="visible-phone mt-1"></div>
                                    <button-yesno name="filter[myCotizaciones][myQuotes]" :show-label="false" class="text-right" :class="{disabled: loading}" @change="searchMyQuotes" 
                                        label-yes="Mis cotizaciones"  label-yes-class="btn-info"
                                        label-no="Todas" label-no-class="btn-success" 
                                        controls-class="btn-yesno"
                                        :model-value="0">
                                        Mostrar
                                    </button-yesno>
                                </div>
                            <?php endif ;?>
                        </div>
                        
                        <search-filters v-show="showFilters" v-model="queryFilters" modal-id="myCotizaciones" @change-fields="searchFilters" :clear-filters="clearFilters"></search-filters>
                    </div>
                </div>
            </template>
        </quotes>
    </div>
</div>

<?php echo JLayoutHelper::render('vue.components.search'); ?>
<?php 
    $fields = ListModel::getInstance('Cotizaciones', 'SabullvialModel')
        ->getFilterForm([], false)
        ->getGroup('filter');

    echo JLayoutHelper::render('vue.components.search-filters', [ 'fields' => $fields ]); 
?>
<?php
    /** @var SabullvialTableEstadoCotizacion $estadoCotizacion */
    $estadoCotizacion = Table::getInstance('EstadoCotizacion', 'SabullvialTable');

    $estadosParaEditar = array_merge([
        $this->state->params->get('cotizacion_estado_creado'),
        $this->state->params->get('orden_de_trabajo_estado_creado')
    ], $estadoCotizacion->getEstadosRechazadosIds());

    echo JLayoutHelper::render('vue.components.quotes', 
    [
        'estadosParaEditar' => $estadosParaEditar,
        'estadosParaDuplicar' => $estadoCotizacion->getEstadosAprobadosIds(),
        'estadoAprobadoAutomatico' => $this->state->params->get('cotizacion_estado_aprobado_automatico'),
        'estadoOrdenDeTrabajo' => $this->state->params->get('orden_de_trabajo_estado_creado')
    ]);
?>