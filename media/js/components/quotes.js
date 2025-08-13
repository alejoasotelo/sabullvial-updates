function quotesComponent() {
    return {
        props: ['modalId'],
        emits: ['edit', 'duplicate'],
        template: document.querySelector('#quotes-template').innerHTML,
        components: {
            'label-estado': labelEstadoComponent()
        },
        data() {
            return {
                comOptions: {},
                quotes: [],
                loading: true,
                error: null,
                query: '',
                queryFilters: {},
                filterByMyQuotes: 0,
                showFilters: false,
                pagination: {
                    limitstart: 0,
                    limit: 20,
                    total: 0
                },
                modal: null,
                clearFilters: false,
            }
        },
        created() {
            this.comOptions = Joomla.getOptions('com_sabullvial.quotes');
        },
        mounted() {
            const self = this;
            this.modal = jQuery('#' + this.modalId);

            this.modal.on('show.bs.modal', (e) => {
                if (e.target.id != self.modalId) {
                    return;
                }
                this.fetchData();
            }).on('hide.bs.modal', function () {
                self.modal.find('.hasTooltip').tooltip('destroy');
            });

            this.search = Tools.debounce((query) => {
                this.query = query;
                this.fetchData();
            }, 300);
        },
        methods: {
            reset() {
                this.query = '';
                this.queryFilters = {};
                this.clearFilters = true;
                this.$nextTick(() => {
                    this.clearFilters = false;
                  });
            },
            clear() {
                this.reset();
                this.search(this.query);
            },
            edit(quote) {
                let canDuplicate = this.canDuplicate(quote.id_estadocotizacion);
                
                if (canDuplicate) {
                    this.$emit('duplicate', quote);
                    return;
                }

                this.$emit('edit', quote);
            },
            toggleFilters(show) {
                this.showFilters = show;
            },
            canDuplicate(id_estadocotizacion) {
                return this.comOptions.estadosParaDuplicar.indexOf(id_estadocotizacion) > -1;
            },
            canEdit(id_estadocotizacion) {
                return this.comOptions.estadosParaEditar.indexOf(id_estadocotizacion) > -1;
            },
            async fetchData() {
                const self = this;
                this.loading = true;
                this.error = null;

                const limitstart = this.pagination.limitstart;
                const limit = this.pagination.limit;
                const query = this.query.trim() == '' ? '' : encodeURIComponent(this.query);
                const filterByMyQuotes = this.filterByMyQuotes ? '&filter[author_id]=' + this.comOptions.idVendedor : '';

                let filter = [];
                Object.keys(this.queryFilters).forEach((filterKey) => {
                    const filterValue = this.queryFilters[filterKey];
                    if (!filterValue) {
                        return;
                    }
                    
                    if (Array.isArray(filterValue)) {
                        filterValue.forEach((value) => {
                            filter.push(`filter[${filterKey}][]=${value}`);
                        });
                        return;
                    } else {
                        filter.push(`filter[${filterKey}]=${filterValue}`);
                    }
                });

                const baseUrl = 'index.php?option=com_sabullvial&task=puntosdeventa.listQuotes';
                const url = baseUrl + '&filter[search]=' + query + filterByMyQuotes + '&limitstart=' + limitstart + '&list[limit]=' + limit + '&' + this.comOptions.token + '=1' + (filter.length ? '&' + filter.join('&') : '');
                const result = await jQuery.get(url);

                if (result.success) {
                    this.quotes = result.data.items.map((quote) => {
                        quote.canEdit = this.canEdit(quote.id_estadocotizacion);
                        quote.canDuplicate = this.canDuplicate(quote.id_estadocotizacion);
                        return quote;
                    });
                    this.pagination = result.data.pagination;
                    setTimeout(() => {
                        let tooltips = self.modal.find('.hasTooltip');
                        tooltips.each(function () {
                            var attr = jQuery(this).attr('data-placement');
                            if (attr === undefined || attr === false) jQuery(this).attr('data-placement', 'auto-dir top-left')
                        });
                        tooltips.tooltip({
                            html: true,
                            container: '#' + self.modal.attr('id')
                        });
                
                        self.refreshPaginationElement();
                    }, 100);
                } else {
                    this.error = result.message;
                }

                this.loading = false;
            },
            refreshPaginationElement() {
                const self = this;
                let $pagination = jQuery(this.$el).find('.pagination-list');

                if ($pagination.twbsPagination('getTotalPages') != this.pagination.pagesTotal) {
                    $pagination.twbsPagination('destroy');
                }

                $pagination.twbsPagination({
                    totalPages: this.pagination.pagesTotal > 1 ? this.pagination.pagesTotal : 1,
                    visiblePages: 10,
                    startPage: this.pagination.pagesCurrent,
                    initiateStartPageClick: false,
                    first: '<span class="icon-backward icon-first"></span>',
                    prev: '<span class="icon-step-backward icon-previous"></span>',
                    next: '<span class="icon-step-forward icon-next"></span>',
                    last: '<span class="icon-forward icon-last"></span>',
                    onPageClick: function (event, page) {
                        self.pagination.limitstart = (page - 1) * self.pagination.limit;
                        self.fetchData();
                    }
                });
            },
            searchMyQuotes(byMyQuotes) {
                this.filterByMyQuotes = byMyQuotes;
                this.search(this.query);
            },
            searchFilters({ fields }) {
                this.queryFilters = fields;
                this.search(this.query);
            },
            numberFormat: numberFormat,
            // date is in format YYYY-MM-DD H:i:s
            // and is formated to DD-MM-YYYY
            formatDate: function (value) {
                if (!value) {
                    return '';
                }

                var parts = value.split(' ');
                var dateParts = parts[0].split('-');
                return dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0];
            },
        },
        watch: {
            filterByMyQuotes() {
                this.search(this.query);
            }
        }
    }
}
