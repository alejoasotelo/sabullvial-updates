function chosenSelectComponent() {
    return {
        props: ["modelValue", 'trackBy', "disabled", 'options', 'label'],
        emits: ['update:model-value', 'update:disabled', 'change'],
        template: `<select :modelValue="modelValue" :disabled="disabled">
            <slot></slot>
            <option v-for="option in localOptions" v-bind:value="option[trackBy]">
                {{ option[label] }}
            </option>
            </select>`,
        mounted() {
            const self = this;
            const $jqEl = jQuery(this.$el);

            if (this.modelValue !== undefined) {
                if (this.trackBy) {
                    $jqEl.val(this.modelValue[this.trackBy]);
                } else {
                    $jqEl.val(this.modelValue);
                }
            }

            $jqEl.chosen({
                search_contains: true
            }).on("change", function (e) {
                if (self.trackBy) {
                    const item = self.options.find(i => i[self.trackBy] == self.$el.value);
                    self.$emit('update:model-value', item);
                    self.$emit('change', item);
                } else {
                    self.$emit('update:model-value', self.$el.value);
                    self.$emit('change', self.$el.value);
                }
            });
        },
        computed: {
            localOptions() {
                let vm = this,
                    options = [];

                if (!this.options) {
                    return options;
                }

                if (Array.isArray(this.options)) {
                    return options.concat(this.options)
                }

                Object.keys(this.options).forEach(function (key) {
                    options.push({
                        [vm.trackBy]: key,
                        [vm.label]: vm.options[key]
                    })
                })

                return this.allowEmpty
                    ? [{ [this.trackBy]: null, [this.label]: '' }].concat(options)
                    : options
            },
        },
        watch: {
            modelValue(val, oldVal) {
                const $el = jQuery(this.$el);

                if (this.trackBy) {
                    $el.val(val[this.trackBy]);
                } else {
                    $el.val(val);
                }

                $el.trigger('chosen:updated');
                $el.trigger("liszt:updated");
            },
            disabled(val, oldVal) {
                const $el = jQuery(this.$el);
                $el.attr('disabled', val);
                $el.trigger("liszt:updated");
                $el.trigger('chosen:updated');
            }
        },
        destroyed() {
            jQuery(this.$el).chosen('destroy');
        }
    };
}