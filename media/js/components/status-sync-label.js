function statusSyncLabelComponent() {
    return {
        props: {
            idCotizacion: {
                type: String,
                required: true
            },
            tangoEnviar: {
                type: Number,
                required: true
            },
            tangoFechaSincronizacion: {
                type: String,
                required: true
            },
            tangoFechaSincronizacionNulls: {
                type: Array,
                default: [null, '0000-00-00 00:00:00', '1800-01-01 00:00:00']
            }
        },
        methods: {
            JText(key) {
                if (!Joomla) {
                    return '';
                }

                return Joomla.Text._(key);
            }
        },
        computed: {
            isTangoEnviar() {
                return this.tangoEnviar > 0;
            },
            isSyncWithTango() {
                return !this.tangoFechaSincronizacionNulls.includes(this.tangoFechaSincronizacion);
            },
            className() {
                let _class = "label hasTooltip";

                if (this.isTangoEnviar && this.isSyncWithTango) {
                    _class += ' label-success';
                } else if (this.isTangoEnviar && !this.isSyncWithTango) {
                    _class += ' label-warning';
                }
                return _class;
            },
            title() {
                if (this.isTangoEnviar && this.isSyncWithTango) {
                    return this.JText('COM_SABULLVIAL_COTIZACIONES_TANGO_SINCRONIZADO').replace('%s', this.tangoFechaSincronizacion);
                } else if (this.isTangoEnviar && !this.isSyncWithTango) {
                    return this.JText('COM_SABULLVIAL_COTIZACIONES_TANGO_ENVIADO');
                }
                
                return this.JText('COM_SABULLVIAL_COTIZACIONES_TANGO_SIN_ENVIAR');
            }
        },
        template: `<span :class="className" :title="title">
            {{idCotizacion}}
        </span>`,
    };
}
