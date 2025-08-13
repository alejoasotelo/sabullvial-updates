function select2Component() {
    return {
        name: 'Select2',
        data() {
            return {
                select2: null
            };
        },
        emits: ['update:modelValue', 'update:model-value'],
        props: {
            modelValue: [String, Array, Number], // previously was `value: String`
            id: {
                type: String,
                default: ''
            },
            name: {
                type: String,
                default: ''
            },
            placeholder: {
                type: String,
                default: ''
            },
            options: {
                type: Array,
                default: () => []
            },
            disabled: {
                type: Boolean,
                default: false
            },
            required: {
                type: Boolean,
                default: false
            },
            settings: {
                type: Object,
                default: () => { }
            },
            parent: {
                type: String,
                default: ''
            }
        },
        template: '<select :id="id" :name="name" :disabled="disabled" :required="required"></select>',
        watch: {
            options: {
                handler(val) {
                    this.setOption(val);
                },
                deep: true
            },
            modelValue: {
                handler(val) {
                    this.setValue(val);
                },
                deep: true
            },
        },
        methods: {
            setOption(val = []) {
                const self = this;
                this.select2.empty();

                let options = {
                    placeholder: this.placeholder,
                    ...this.settings,
                    data: val,
                    matcher: this.matchCustom
                };

                if (this.parent) {
                    options.dropdownParent = jQuery(this.parent);
                }

                this.select2.select2(options);
                this.setValue(this.modelValue);
            },
            setValue(val) {
                if (val instanceof Array) {
                    this.select2.val([...val]);
                } else {
                    this.select2.val([val]);
                }
                this.select2.trigger('change');
            },
            matchCustom(params, data) {
                // If there are no search terms, return all of the data
                if (typeof params.term === 'undefined' || params.term.trim() === '') {
                  return data;
                }
            
                // Do not display the item if there is no 'text' property
                if (typeof data.text === 'undefined' || data.text === null) {
                  return null;
                }

                const terms = params.term.toLowerCase().split(' ').filter(term => term.trim() !== '');
                const rowText = data.text.toLowerCase().replaceAll('-', ' ');
                let find = null;
                for (let i = 0; i < terms.length; i++) {
                    const term = terms[i];
                    if (rowText.indexOf(term) > -1) {
                        if (find == null) {
                            find = jQuery.extend({}, data, true);
                        }
                        // const patron = new RegExp(term, 'gi');
                        // find.text = find.text.replace(patron, function(match) {
                        //     return '<strong>' + match + '</strong>';
                        //   });
                    } else {
                        return null;
                    }
                }

                // Return `null` if the term should not be displayed
                return find;
            }
        },
        mounted() {

            let options = {
                placeholder: this.placeholder,
                ...this.settings,
                data: this.options,
                matcher: this.matchCustom
            };

            if (this.parent) {
                options.dropdownParent = jQuery(this.parent);
            }

            this.select2 = jQuery(this.$el)
                .select2(options)
                .on('select2:select select2:unselect', ev => {
                    this.$emit('update:modelValue', this.select2.val());
                    this.$emit('select', ev['params']['data']);
                });
            this.setValue(this.modelValue);
        },
        beforeUnmount() {
            this.select2.select2('destroy');
        }
    }
}
