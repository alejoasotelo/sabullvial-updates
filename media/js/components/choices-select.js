function choicesSelectComponent() {
    return {
        props: {
            modelValue: [String, Boolean, Object, Number, Array],
            disabled: [Boolean, Number],
            url: String,
            valueJson: Boolean
        },
        emits: ['update:modelValue', 'update:disabled', 'change'],
        template: `<select :modelValue="modelValue" :disabled="disabled"><slot></slot></select>`,
        data() {
            return {
                jqEl: null
            }
        },
        mounted() {
            var self = this;
            this.jqEl = jQuery(this.$el);

            var value = this.modelValue;
            if (this.modelValue.length > 0) {
                value = this.valueJson == "true" || this.valueJson === true ? JSON.stringify(this.modelValue) : this.modelValue;
            }

            this.jqEl.val(value);

            new Choices(this.$el, {
                allowHTML: true,
                placeholder: true,
                placeholderValue: this.jqEl.attr('placeholder'),
                maxItemCount: 10,
                searchResultLimit: 50,
                renderChoiceLimit: 50,
                fuseOptions: {
                  threshold: 0.3, // Strict search
                },
                itemSelectText: 'Seleccionar',
                searchFields: [
                    'customProperties.razon_social', 
                    'customProperties.cod_client', 
                    'customProperties.cod_client_int',
                    'customProperties.cuit', 
                    'customProperties.cuit_sin_guiones'
                ],
            }).setChoices(function () {
                return fetch(self.url)
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (response) {
                        return response.data.map(function (item) {

                            var itemClass = 'cliente-habilitado';
                            var inhabilitadoHtml = '';

                            if (item.habilitado == 0) {
                                itemClass = 'cliente-inhabilitado';
                                inhabilitadoHtml = '<br/><small class="cliente-choice-item-inhabilitado">Inhabilitado</small>';
                            }

                            var text = `
                                <div class="cliente-choices-item ${itemClass}">
                                    <b>${item.razon_social}</b>
                                    <br class="chzn-on-title-hide"/>
                                    <small class="chzn-on-title-hide">${item.cod_client}</small> | 
                                    <small class="chzn-on-title-hide">${item.cuit}</small> | 
                                    <small class="chzn-on-title-hide">${Tools.numberFormat(item.saldo, 2, '.', ',')}</small> | 
                                    <small class="chzn-on-title-hide text-info">
                                        <span>Prom. </span> 
                                        <span>${item.PROMEDIO_ULT_REC}</span>
                                    </small>
                                    ${inhabilitadoHtml}
                                </div>
                            `;

                            return {
                                value: self.valueJson == "true" || self.valueJson === true ? JSON.stringify(item) : item,
                                label: text,
                                customProperties: {
                                    razon_social: item.razon_social.trim(),
                                    cod_client: item.cod_client.trim(),
                                    cod_client_int: parseInt(item.cod_client),
                                    cuit: item.cuit.trim(),
                                    cuit_sin_guiones: parseInt(item.cuit.trim().replace(/-/g, '')),
                                }
                            };
                        });
                    });
            })
            .then(function (instance) {
                window.myChoice = instance;
                instance.setChoiceByValue(value);
            });

            this.jqEl.on('change', function (e) {
                var value = self.$el.value;

                if (value.length > 0) {
                    value = self.valueJson == "true" || self.valueJson === true ? JSON.parse(value) : value;
                }

                self.$emit('update:modelValue', value);
                self.$emit('change', value);

            });
        },
        watch: {
            modelValue(value, oldVal) {
                value = this.valueJson == "true" || this.valueJson === true ? JSON.stringify(value) : value;
                this.jqEl.val(value).trigger('chosen:updated');
            },
            disabled(val, oldVal) {
                this.jqEl.attr('disabled', val).trigger("liszt:updated");
            }
        }
    };
}