function select2AjaxComponent() {
    return {
        props: ['url', 'modelValue', 'trackBy', 'disabled', 'parent', 'placeholder', 'templateOption'],
        emits: ['update:model-value', 'update:disabled', 'change'],
        template: `
            <select :modelValue="modelValue" :disabled="disabled">
                <slot></slot>
            </select>
        `,
        mounted: function () {
            var self = this;

            var select2 = jQuery(this.$el).select2({
                placeholder: this.placeholder || 'Seleccione una opción',
                allowClear: true,
                theme: "bootstrap",
                language: "es",
                minimumInputLength: 1,
                dropdownParent: jQuery(this.parent),
                width: '100%',
                ajax: {
                    url: self.url,
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            query: params.term
                        };
                    },
                    processResults: function (response, params) {
                        params.page = params.page || 1;

                        return {
                            results: response.data
                        };
                    }
                },
                escapeMarkup: function (markup) { return markup; },
                templateResult: self.template,
                templateSelection: function formatState (state) {
                    if (!state.id) {
                        jQuery(self.$el).removeClass('select2-selected');
                      return state.text;
                    }

                    jQuery(self.$el).addClass('select2-selected');

                    let text = self.getTemplate(state);
    
                    return jQuery(text);
                  }
            });

            if (this.modelValue) {
                var newValue = this.hasTrackBy ? this.modelValue[this.getTrackBy] : this.modelValue;
                select2.val(newValue).trigger('change')
            }

            select2.on('select2:select', function (e) {
                var data = e.params.data;
                self.$emit('update:model-value', data);
                self.$emit('change', data);
            }).on('select2:clear', function (e) {
                self.$emit('update:model-value', '');
                self.$emit('change', '');
            });
        },
        methods: {
            template(item) {
                if (item.loading) {
                    return item.text;
                }

                return this.getTemplate(item);
            },
            getTemplate(item) {

                if (this.templateOption) {
                    return this.templateOption(item);
                }

                let itemClass = 'cliente-habilitado';
                let inhabilitadoHtml = '';

                if (item.habilitado == 0) {
                    itemClass = 'cliente-inhabilitado';
                    inhabilitadoHtml = '<br/><small class="cliente-choice-item-inhabilitado">Inhabilitado</small>';
                }

                return `
                        <div class="cliente-choices-item ${itemClass}">
                            <b>${item.razon_social}</b>
                            <br class="chzn-on-title-hide"/>
                            <small class="chzn-on-title-hide">${item.cod_client}</small> | 
                            <small class="chzn-on-title-hide">${item.cuit}</small> | 
                            <small class="chzn-on-title-hide">${item.codigo_vendedor}</small> | 
                            <small class="chzn-on-title-hide">${Tools.numberFormat(item.saldo, 2, '.', ',')}</small> | 
                            <small class="chzn-on-title-hide text-info">
                                <span>Prom. </span> 
                                <span>${item.PROMEDIO_ULT_REC}</span>
                            </small>
                            ${inhabilitadoHtml}
                        </div>
                    `;
            }
        },
        computed: {
            hasTrackBy: function () {
                return this.trackBy !== undefined
            },
            getTrackBy: function () {
                return this.trackBy
            }
        },
        watch: {
            modelValue: function (value) {
                let newValue = null;
                if (value != null) {
                    newValue = this.hasTrackBy ? value[this.getTrackBy] : value;
                }

                jQuery(this.$el).val(newValue).trigger('change');
            },
            options: function (options) {
                // update options
                jQuery(this.$el).empty().select2({
                    data: options
                })
            },
            disabled(val, oldVal) {
                jQuery(this.$el).attr('disabled', val);
            }
        },
        destroyed: function () {
            jQuery(this.$el).off().select2('destroy')
        }
    };
}