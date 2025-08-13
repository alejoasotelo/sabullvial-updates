<?php

/**
 * Layout file for the main body component of the modal showing the batch options
 * This layout displays the various html input elements relating to the batch processes
 */
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

Text::script('JOPTION_SELECT_VEHICULO');
Text::script('JOPTION_SELECT_CHOFER');

$isAdministrador = SabullvialHelper::isUserAdministrador();
?>

<div class="container-fluid">

    <div class="row-fluid">
        <div class="span6 control-group">
            <div class="controls">
                <div class="control-group">
                    <label><?php echo JText::_('COM_SABULLVIAL_REMITOS_BATCH_FECHA_ENTREGA'); ?></label>
                    <div>
                        <input type="date" name="batch[hojaderuta][delivery_date]" v-model="generateRouteSheet.deliveryDate" id="batch_delivery_date" />
                    </div>
                </div>
                <div class="control-group">
                    <label><?php echo JText::_('COM_SABULLVIAL_REMITOS_BATCH_VEHICULO'); ?></label>
                    <small><?php echo JText::_('COM_SABULLVIAL_REMITOS_BATCH_VEHICULO_DESC'); ?></small>
                    <div class="row-fluid">
                        <chosen-select v-model="generateRouteSheet.vehiculo" 
                            @change="setChoferYPatente" :options="vehiculos" 
                            track-by="id" label="patente" 
                            data-placeholder="<?php echo Text::_('JOPTION_SELECT_VEHICULO'); ?>">
                        </chosen-select>
                        ó 
                        <input type="text" v-model="generateRouteSheet.patente" @keydown="setVehiculo(0)" name="batch[hojaderuta][patente]" id="batch_patente" placeholder="ABC123" class="input-xlarge margin-remove" />
                        <br/>
                        <div v-if="generateRouteSheetMontoSeguro" id="batch_monto_seguro" class="alert alert-info span12 small ml-0-i mb-0-i" style="margin-top: 8px;">
                            Seguro {{generateRouteSheet.patente}}: {{formatPrice(generateRouteSheetMontoSeguro)}}
                        </div>
                        <div v-if="generateRouteSheetMontoSeguro && generateRouteSheetMontoSeguro < cartTotalRemitos" class="alert alert-danger span12 small ml-0-i mb-0-i" style="margin-top: 8px;">
                            Seguro del vehículo inferior al monto de los remitos seleccionados
                        </div>
                    </div>
                </div>

                <div class="control-group">
                    <label><?php echo JText::_('COM_SABULLVIAL_REMITOS_BATCH_CHOFER'); ?></label>
                    <small><?php echo JText::_('COM_SABULLVIAL_REMITOS_BATCH_CHOFER_DESC'); ?></small>
                    <div class="row-fluid">                        
                        <chosen-select v-model="generateRouteSheet.chofer" :options="choferes" track-by="id" label="name" data-placeholder="<?php echo Text::_('JOPTION_SELECT_CHOFER'); ?>" @change="setChoferNombre(generateRouteSheet.chofer.name)"></chosen-select>
                        <br/>
                        ó 
                        <input type="text" v-model="generateRouteSheet.choferNombre" @keydown="setChofer(0)" id="batch_chofer" placeholder="Nombre del chofer" class="input-xlarge margin-remove" />
                    </div>
                </div>
            </div>
        </div>

        <div class="span6">
            <div class="well">
                <h3 class="font-normal"><?php echo JText::_('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_REMITOS_SELECCIONADOS'); ?></h3>
                <div class="table-responsive">
                    <table class="table table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <th class="text-muted"><?php echo JText::_('COM_SABULLVIAL_REMITOS_NUMERO_REMITO'); ?></th>
                                <th class="text-muted"><?php echo JText::_('COM_SABULLVIAL_REMITOS_CLIENTE'); ?></th>                                
                                <?php if ($isAdministrador) : ?>
                                    <th class="text-muted"><?php echo JText::_('COM_SABULLVIAL_REMITOS_MONTO_REMITO'); ?></th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="remito in cart.remitos">
                                <td>
                                    {{ remito.numero_remito }}
								    <div class="small">
                                        <?php echo JText::_('COM_SABULLVIAL_REMITOS_ESTADO'); ?>: 
                                        <span class="label" :style="'background-color: ' + remito.estadoremito_bg_color + '; color: '+  remito.estadoremito_color">
                                            {{remito.estadoremito}}
                                        </span>
                                    </div>
                                </td>
                                <td>{{ remito.cliente }}</td>
                                <?php if ($isAdministrador) : ?>
                                    <td class="nowrap small">
                                        {{remito.montoRemitoFormated}}
                                    </td>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                        <?php if ($isAdministrador) : ?>
                        <tfoot>
                            <tr>
                                <th colspan="2">Total:
                                </th>
                                <th class="nowrap small">
                                    {{ cartTotalRemitosFormated }}
                                </th>
                            </tr>
                        </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>