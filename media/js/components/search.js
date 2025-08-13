function searchComponent() {
    return {
        props: {
            modalId: String,
            showBtnFilters: Boolean
        },
        emits: ['search', 'clear', 'toggle-filters'],
        template: document.querySelector('#search-template').innerHTML,
        data() {
            return {
                query: '',
                loading: true,
                error: null,
                modal: null,
                showFilters: false
            }
        },
        mounted() {
            const self = this;

            if (!this.modalId) {
                return;
            }

            this.modal = jQuery('#' + this.modalId);
        },
        methods: {
            search() {
                this.$emit('search', this.query);
            },
            clear() {
                this.query = '';
                this.$emit('clear', this.query);
            },
            toggleFilters() {
                this.showFilters = !this.showFilters;
                this.$emit('toggle-filters', this.showFilters);
            },
            onKeyUp(e) {
                // shift, ctrl, alt, caps, esc, page up, page down, end, home, left, up, right, down, insert, delete, command
                const notLetters = [13, 16, 17, 18, 20, 27, 37, 38, 39, 40, 91, 93, 224];

                if (notLetters.indexOf(e.keyCode) > -1) {
                    return;
                }

                this.search();
            }
        }
    }
}
