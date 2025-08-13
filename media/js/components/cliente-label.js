function clienteLabelComponent() {
    return {
        props: {
            cliente: {
                type: String
            },
            razonSocial: {
                type: String,
                default: '',
            },
            title: {
                type: String,
                default: ''
            },
            idCliente: {
                type: String,
                default: '',
            },
            codcli: {
                type: String,
                default: ''
            },
            cuit: {
                type: String,
                default: ''
            },
            codigoVendedor: {
                type: String,
                default: ''
            },
            documentoTipo: {
                type: Number,
                default: 0
            },
            documentoNumero: {
                type: String,
                default: ''
            },
            canClick: {
                type: Boolean,
                default: false
            }
        },
        emits: ['click'],
        computed: {
            tagType() {
                return this.canClick ? 'a' : 'span';
            }
        },
        methods: {
            JText(key) {
                if (!Joomla) {
                    return '';
                }

                return Joomla.Text._(key);
            },
            emitOnClick(e) {
                if (!this.canClick) {
                    return;
                }

                this.$emit('click', e);
            }
        },
        template: `
                <component :is="tagType" @click.prevent="emitOnClick" class="hasTooltip" :title="title" href="#">
                    {{cliente ? cliente : razonSocial}}
                </component>
                <div class="small">
                    <template v-if="idCliente && idCliente != '000000'">
                        <span class="hasTooltip" :data-title="JText('COM_SABULLVIAL_CLIENTES_RAZON_SOCIAL')">RS</span>: <component :is="tagType" @click.prevent="emitOnClick" class="hasTooltip" :title="title" href="#">{{razonSocial}}</component> |
                        Cod: <component :is="tagType" @click.prevent="emitOnClick" class="hasTooltip" :title="title" href="#">{{codcli}}</component> |
                        Cuit: <component :is="tagType" @click.prevent="emitOnClick" class="hasTooltip" :title="title" href="#">{{cuit}}</component> |
                        CV: <component :is="tagType" @click.prevent="emitOnClick" class="hasTooltip" :title="title" href="#">{{codigoVendedor}}</component>
                    </template>
                    <template v-else>
                        <span class="hasTooltip" :data-title="JText('COM_SABULLVIAL_CLIENTES_CONSUMIDOR_FINAL')">CF</span>
                        <template v-if="documentoNumero.trim().length > 0">
                            | {{documentoTipo == 80 ? 'Cuit' : 'Dni'}}: <component :is="tagType" @click.prevent="emitOnClick" class="hasTooltip" :title="title" href="#">{{documentoNumero}}</component>
                        </template>
                    </template>
                </div>
            `,
    };
}
