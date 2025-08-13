// Usage: <vue-select-chosen v-model="selected" :options="options" :multiple="true" :allow-all="true" :allow-empty="true" :on-value-return="onValueReturn"></vue-select-chosen>
// Options: options, label, trackBy, multiple, placeholder, searchable, searchableMin, allowEmpty, allowAll, disabled, onValueReturn

let VueSelectChosen = Vue.defineComponent({
    template: `
        <select :modelValue="modelValue" :data-placeholder="placeholder" :multiple="multiple" :disabled="disabled">
            <option v-if="showEmptyLabel" value="">{{ emptyLabel }}</option>
            <option v-for="option in localOptions" v-bind:value="option[trackBy]">
                {{ option[label] }}
            </option>
        </select>
    `,
    emits: ['update:modelValue', 'update:disabled', 'change'],
    props: {
        modelValue: {
            type: [String, Number, Array, Object],
            default: null
        },
        options: {
            type: [Array, Object],
            default: () => []
        },
        label: {
            type: String,
            default: 'label'
        },
        trackBy: {
            type: String,
            default: 'id'
        },
        multiple: {
            type: Boolean,
            default: false
        },
        placeholder: {
            type: String,
            default: 'Select'
        },
        searchable: {
            type: Boolean,
            default: true
        },
        searchableMin: {
            type: Number,
            default: 1
        },
        allowEmpty: {
            type: Boolean,
            default: true
        },
        allowAll: {
            type: Boolean,
            default: false
        },
        emptyLabel: {
            type: String,
            default: ''
        },
        showEmptyLabel: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        onValueReturn: {
            type: Object,
            default: () => ({})
        }
    },

    computed: {
        localOptions() {
            let vm = this,
                options = []

            if (this.allowAll) {
                options.push({
                    [this.trackBy]: -1,
                    [this.label]: 'All'
                })
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

        localValue() {
            let value = this.allowAll && this.modelValue === null ? -1 : this.modelValue

            this.$nextTick(function () {
                jQuery(this.$el).val(value).trigger("chosen:updated")
            })

            return value
        }
    },

    watch: {
        localValue() {
        },

        localOptions() {
            var $el = jQuery(this.$el);

            this.$nextTick(function () {
                let value = this.allowAll && this.modelValue === null ? '-1' : this.modelValue;
                $el.val(value).trigger("chosen:updated");
                $el.trigger("liszt:updated");
            })
        },
        disabled(val, oldVal) {
            jQuery(this.$el).attr('disabled', val).trigger("liszt:updated");
        }
    },

    mounted() {
        let self = this

        jQuery(this.$el).chosen({
            width: "100%",
            disable_search_threshold: this.searchable ? this.searchableMin : 100000
        }).change(function ($event) {
            const newValue = jQuery($event.target).val();
            
            if (typeof self.onValueReturn[newValue] !== 'undefined') {
                return self.$emit('update:modelValue', self.onValueReturn[newValue])
            }
            if (self.allowAll && (newValue === '-1' || newValue === -1)) {
                return self.$emit('update:modelValue', null)
            }
            
            self.$emit('update:modelValue', newValue);
            self.$emit('change', newValue);
        })
    }
})