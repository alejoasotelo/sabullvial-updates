function searchFiltersComponent() {
    return {
        props: {
            modalId: {
                type: String,
                required: true
            },
            clearFilters: {
                type: Boolean,
                default: false
            },
        },
        emits: ['change-fields'],
        template: document.querySelector('#search-filters-template').innerHTML,
        data() {
            return {
                filters: {},
            }
        },
        mounted() {
            const self = this;

            if (!this.modalId) {
                console.error('modalId is required');
                return;
            }

            window.onChangeField = function (el, fieldId, fieldName) {
                this.onChangeField(el, fieldId, fieldName);
            }.bind(this);
        },
        watch: {
            clearFilters: function (val) {
                if (val) {
                    const $el = jQuery(this.$el);
                    $el.find('input, select').val(null);
                    $el.find('select').trigger('liszt:updated');
                    this.filters = {};
                }
            }
        },
        methods: {
            onChangeField(el, fieldId, fieldName) {

                let fieldValue = el.value;
                if (el.multiple) {
                    fieldValue = Array.from(el.selectedOptions).map(option => option.value);
                }

                this.filters[fieldName] = fieldValue;

                const fields = {...this.filters};

                this.$emit('change-fields', { fields });
            },
        }
    }
}
