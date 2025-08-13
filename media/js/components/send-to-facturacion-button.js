function sendToFacturacionButtonComponent() {

    const comOptions = Joomla.getOptions('com_sabullvial');

    const STATUS_IDLE = 'idle';
    const STATUS_SEND = 'send';
    const STATUS_SENDING = 'sending';
    const STATUS_SENT = 'sent';

    return {
        emits: ['on-sent'],
        props: {
            idCotizacion: {
                type: String,
                required: true
            },
            estadoTangoSrl: {
                type: Number,
                required: true
            },
            estadoTangoPrueba: {
                type: Number,
                required: true
            },
            hasCustomProducts: {
                type: Boolean,
                default: false
            },
        },
        data() {
            return {
                status: STATUS_IDLE
            }
        },
        methods: {
            changeStatus(newStatus) {
                this.status = newStatus;

                if (newStatus == STATUS_IDLE) {
                    this.reset();
                }
            },
            reset() {

            },
            sendToFacturacion(idEstadoTango) {
                const self = this;

                this.status = STATUS_SENDING;
                notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIANDO_A_FACTURACION'), 'info');

                jQuery.post('index.php?option=com_sabullvial&task=puntosdeventa.changeEstadoTango&' + comOptions.token + '=1',
                    {
                        id: this.idCotizacion,
                        id_estado_tango: idEstadoTango
                    }).then(function (response) {
                        if (response.success) {
                            self.status = STATUS_SENT;
                            notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_SUCCESS'), 'success');
                            self.$emit('on-sent');
                            return true;
                        }

                        notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_ERROR'), 'danger');

                        if (response.messages.error) {
                            for (let message of response.messages.error) {
                                notify(message, 'danger');
                            }
                        }

                        self.status = STATUS_SEND;
                    });

            },
            JText(key) {
                if (!Joomla) {
                    return '';
                }

                return Joomla.Text._(key);
            }
        },
        computed: {
            isIdle() {
                return this.status == STATUS_IDLE;
            },
            isSend() {
                return this.status == STATUS_SEND;
            },
            isSending() {
                return this.status == STATUS_SENDING;
            },
            isSent() {
                return this.status == STATUS_SENT;
            },
            STATUS_IDLE: () => STATUS_IDLE,
            STATUS_SEND: () => STATUS_SEND,
            STATUS_SENDING: () => STATUS_SENDING,
            STATUS_SENT: () => STATUS_SENT
        },
        template: `
            <button v-if="isIdle" type="button" class="btn btn-default btn-small" @click="changeStatus(STATUS_SEND)">
                <span class="icon-publish" aria-hidden="true"></span>
                {{JText('JACTION_SEND_TO_FACTURACION')}}
            </button>
            <span v-else-if="isSent">
                {{JText('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_SUCCESS')}}
            </span>
            <div v-else class="center">
                <span v-if="hasCustomProducts">{{JText('COM_SABULLVIAL_PUNTOS_DE_VENTA_MODAL_SEND_TO_FACTURACION_HAS_CUSTOM_PRODUCTS_DESC')}}</span>
                <div v-else>
                    <span>{{JText('COM_SABULLVIAL_PUNTOS_DE_VENTA_ELIJA_EL_TIPO_DE_FACTURACION')}}</span>
                    <div class="center">
                        <button type="button" class="btn btn-danger btn-small mr-1" @click="sendToFacturacion(estadoTangoSrl)" :disabled="isSending">
                            {{JText('JYES')}}
                        </button>
                        <button type="button" class="btn btn-default btn-small mr-1" @click="sendToFacturacion(estadoTangoPrueba)" :disabled="isSending">
                            {{JText('JACTION_TEST')}}
                        </button>
                         | 
                        <button type="button" class="btn btn-link btn-small" @click="changeStatus(STATUS_IDLE)" :disabled="isSending">
                            {{JText('JCANCEL')}}
                        </button>
                    </div>
                </div>
            </div>
            `,
    };
}