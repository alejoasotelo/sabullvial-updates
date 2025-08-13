<?php

/**
 * Layout file for the main body component of the modal showing the batch options
 * This layout displays the various html input elements relating to the batch processes
 */
defined('_JEXEC') or die;
$isAdministrador = SabullvialHelper::isUserAdministrador();
?>

<div class="container-fluid">

    <div class="row-fluid">

        <div class="span4 control-group mt-1 mb-3">
            <label class="h4" for="delivered_by_counter_delivery_date"><?php echo JText::_('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_FECHA_ENTREGA'); ?></label>
            <small class="text-muted mb-1"><?php echo JText::_('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_FECHA_ENTREGA_DESC'); ?></small>
            <div>
                <input type="date" v-model="deliveredByCounter.deliveryDate" id="delivered_by_counter_delivery_date" :disabled="loadingModal" />
            </div>
        </div>

        <div class="span8">
            <div class="well">
                <h3 class="font-normal"><?php echo JText::_('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_REMITOS_SELECCIONADOS'); ?></h3>
                <div class="table-responsive">
                    <table class="table table-striped table-condensed table-hover">
                        <thead>
                            <tr>
                                <th class="text-muted"><?php echo JText::_('COM_SABULLVIAL_REMITOS_NUMERO_REMITO'); ?></th>
                                <th class="text-muted hidden-phone"><?php echo JText::_('COM_SABULLVIAL_REMITOS_ESTADO'); ?></th>
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
                                    <div class="visible-phone">
                                        <span class="label" :style="'background-color: ' + remito.estadoremito_bg_color + '; color: '+  remito.estadoremito_color">
                                            {{remito.estadoremito}}
                                        </span>
                                    </div>
                                </td>
                                <td class="hidden-phone">
                                    <span class="label" :style="'background-color: ' + remito.estadoremito_bg_color + '; color: '+  remito.estadoremito_color">
                                        {{remito.estadoremito}}
                                    </span>
                                </td>
                                <td>{{ remito.cliente }}</td>
                                <?php if ($isAdministrador) : ?>
                                    <td class="nowrap smal">
                                        {{remito.montoRemitoFormated}}
                                    </td>
                                <?php endif; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>