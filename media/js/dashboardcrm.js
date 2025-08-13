jQuery(document).ready(function() {

    const comOptions = Joomla.getOptions('com_sabullvial');

    const app = Vue.createApp({
        components: {
            'card-budget': cardBudgetComponent(),
            'chosen-select': chosenSelectComponent()
        },
        data() {
            return {
                loading: false,
                activeFilters: {
                    search: '',
                    ordering: comOptions.ordering,
                    direction: comOptions.direction,
                    codigo_vendedor: '',
                    codigo_cliente: '',
                    date_from: '',
                    date_to: '',
                    total_from: '',
                    total_to: ''
                },
                vendedor: comOptions.vendedor,
                cotizaciones: comOptions.cotizaciones,
                budgetValues: comOptions.budgetValues || {},
                cotizacionesTodas: comOptions.cotizaciones.todas || [],
                chartType: 'doughnut',
                sortField: 'estado',
                sortOrder: 'asc'
            };
        },
        created() {
            const self = this;

            this.charts = {
                todas: null,
                cotizaciones: null
            };

            this.changeSearch = Tools.debounce(function () {
                self.reload();
            }, 300);

            self.reload();

            window.changeLimit = function (el) { self.changeLimit(el.value) };
            window.changeOrdering = function (el) { self.changeOrdering(el.value) };
            window.changeCodigoVendedor = function (el) {
                self.activeFilters.codigo_vendedor = el.value;
                self.changeSearch();
            };
            window.changeCodigoCliente = function(el) {
                self.activeFilters.codigo_cliente =  el.value;
                self.changeSearch();
            }
            window.changeTotalFrom = function(el) {
                self.activeFilters.total_from = el.value;
                self.changeSearch();
            }
            window.changeTotalTo = function(el) {
                self.activeFilters.total_to = el.value;
                self.changeSearch();
            }
        },
        mounted() {
            this.initChart();
            this.initChartTodas();
        },
        computed: {
            cotizacionesRealizadasTotal() {
                if (!this.cotizaciones.realizadas || !this.cotizaciones.realizadas.total) {
                    return 0;
                }

                return this.cotizaciones.realizadas.total;
            },
            cotizacionesVendidasTotal() {
                if (!this.cotizaciones.vendidas || !this.cotizaciones.vendidas.total) {
                    return 0;
                }

                return this.cotizaciones.vendidas.total;
            },
            cotizacionesRechazadasTotal() {
                if (!this.cotizaciones.rechazadas || !this.cotizaciones.rechazadas.total) {
                    return 0;
                }

                return this.cotizaciones.rechazadas.total;
            },
            cotizacionesTodasTotal() {
                return this.cotizacionesTodas.reduce((sum, c) => sum + (parseFloat(c.total || 0) || 0), 0);
            },
            cotizacionesTodasCantidad() {
                return this.cotizacionesTodas.reduce((sum, c) => sum + (parseInt(c.cantidad || 0) || 0), 0);
            },
            sortedCotizaciones() {
                if (!this.cotizacionesTodas) {
                    return [];
                }
                
                const sortField = this.sortField;
                const sortOrder = this.sortOrder;
                
                return [...this.cotizacionesTodas].sort((a, b) => {
                    let valueA, valueB;
                    
                    if (sortField === 'estado') {
                        valueA = a.estado || '';
                        valueB = b.estado || '';
                    } else if (sortField === 'cantidad') {
                        valueA = parseInt(a.cantidad || 0) || 0;
                        valueB = parseInt(b.cantidad || 0) || 0;
                    } else { // total
                        valueA = parseFloat(a.total || 0) || 0;
                        valueB = parseFloat(b.total || 0) || 0;
                    }
                    
                    if (sortField === 'estado') {
                        return sortOrder === 'asc' 
                            ? valueA.localeCompare(valueB)
                            : valueB.localeCompare(valueA);
                    } else {
                        return sortOrder === 'asc'
                            ? valueA - valueB
                            : valueB - valueA;
                    }
                });
            }
        },
        methods: {
            preventSubmit: function (e) {
                e.preventDefault();
            },
            sortBy: function (field) {
                if (this.sortField === field) {
                    // Si ya estamos ordenando por este campo, cambiamos la dirección
                    this.sortOrder = this.sortOrder === 'asc' ? 'desc' : 'asc';
                } else {
                    // Si es un nuevo campo, establecemos el campo y ponemos la dirección en ascendente
                    this.sortField = field;
                    this.sortOrder = 'asc';
                }
            },
            submitFilter: function () {
                this.reload();
            },
            clearFilter: function () {
                this.reset();
                this.reload();
            },
            reload: function () {
                var self = this;
                this.loading = true;

                return this.listCotizaciones().then(function () {
                    self.loading = false;
                    return true;
                });
            },
            listCotizaciones: async function () {
                const self = this;

                let data = {};

                // data['list[limit]'] = limit;
                data['list[ordering]'] = this.activeFilters.ordering;
                data['list[direction]'] = this.activeFilters.direction;
                data['filter[search]'] = this.activeFilters.search;
                data['filter[codigo_vendedor]'] = this.activeFilters.codigo_vendedor;
                data['filter[codigo_cliente]'] = this.activeFilters.codigo_cliente;
                data['filter[date_from]'] = this.activeFilters.date_from;
                data['filter[date_to]'] = this.activeFilters.date_to;
                data['filter[total_from'] = this.activeFilters.total_from;
                data['filter[total_to'] = this.activeFilters.total_to;

                try {
                    const response = await jQuery.post('index.php?option=com_sabullvial&view=dashboardscrm&format=json&' + comOptions.token + '=1', data);

                    if (!response.success) {
                        return false;
                    }

                    this.cotizaciones = response.data.cotizaciones;
                    this.cotizacionesTodas = response.data.cotizaciones.todas || [];
                } catch (e) {
                    console.log('Error', e);
                    return false;
                }

                return true;
            },
            changeLimit: function (limit) {
                this.reload();
            },
            changeOrdering(orderAndDirection) {
                var parts = orderAndDirection.split(' ');
                var ordering = parts[0];
                var direction = parts[1];

                this.activeFilters.ordering = ordering || this.activeFilters.ordering;

                direction = direction || this.activeFilters.direction;
                this.activeFilters.direction = direction.toUpperCase();

                this.reload();
            },
            initChartTodas() {
                const ctx = document.getElementById('chartCotizacionesTodas');
                if (!ctx || this.charts.todas) {
                    return;
                }

                const self = this;

                let datasets = [{
                    data: this.cotizacionesTodas.map(c => c.total || 0),
                    backgroundColor: this.cotizacionesTodas.map(c => c.bg_color || '#31708f'),
                    borderWidth: 1
                }];

                this.charts.todas = new Chart(ctx, {
                    type: this.chartType,
                    data: {
                        labels: this.cotizacionesTodas.map(c => c.estado || 'Sin etiqueta'),
                        datasets: datasets
                    },
                    options: {
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label || '';
                                        const value = context.raw || 0;
                                        const cantidad = self.cotizacionesTodas[context.dataIndex].cantidad || 0;

                                        // Calcular porcentaje
                                        let totalGeneral = self.cotizacionesTodas.reduce((sum, c) => sum + (parseFloat(c.total || 0) || 0), 0);
                                        const percentage = totalGeneral > 0 ? ((value / totalGeneral) * 100).toFixed(1) : 0;

                                        return [
                                            `${label}: ${self.priceFormat(value)}`,
                                            `Cantidad: ${cantidad}`,
                                            `Porcentaje: ${percentage}%`
                                        ];
                                    }
                                }
                            }
                        }
                    }
                });
            },
            initChart() {
                const ctx = document.getElementById('chartCotizaciones');
                if (!ctx || this.charts.cotizaciones) {
                    return;
                }

                const self = this;
                
                // Configurar datasets según el tipo de gráfico
                let datasets = [{
                    data: [this.cotizacionesRealizadasTotal, this.cotizacionesVendidasTotal, this.cotizacionesRechazadasTotal],
                    backgroundColor: ['#31708f', '#3c763d', '#a94442'],
                    borderWidth: 1
                }];

                this.charts.cotizaciones = new Chart(ctx, {
                    type: this.chartType,
                    data: {
                        labels: ['Realizadas', 'Vendidas', 'Rechazadas'],
                        datasets: datasets
                    },
                    options: {
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.label;
                                        const value = context.raw || 0;
                                        let cantidad = 0;
                                        
                                        if (context.dataIndex === 0) {
                                            cantidad = self.cotizaciones.realizadas ? self.cotizaciones.realizadas.cantidad : 0;
                                        } else if (context.dataIndex === 1) {
                                            cantidad = self.cotizaciones.vendidas ? self.cotizaciones.vendidas.cantidad : 0;
                                        } else if (context.dataIndex === 2) {
                                            cantidad = self.cotizaciones.rechazadas ? self.cotizaciones.rechazadas.cantidad : 0;
                                        }
                                        

                                        // Calcular total solo de elementos visibles (funciona para todos los tipos)
                                        let totalVisible = 0;
                                        let totalGeneral = 0;                                        
                                        
                                        // Para pie/doughnut/polarArea: usar método más robusto
                                        const meta = context.chart.getDatasetMeta(0);
                                        context.dataset.data.forEach((dataValue, index) => {
                                            totalGeneral += dataValue || 0;

                                            // Verificar múltiples formas de detectar visibilidad
                                            let isVisible = true;
                                            
                                            // Método 1: Verificar si el elemento está oculto
                                            if (meta.data[index] && meta.data[index].hidden === true) {
                                                isVisible = false;
                                            }

                                            // Método 2: Verificar usando el método getVisibleDatasetCount (Chart.js 3+)
                                            if (context.chart.legend && context.chart.legend.legendItems) {
                                                const legendItem = context.chart.legend.legendItems[index];
                                                if (legendItem && legendItem.hidden === true) {
                                                    isVisible = false;
                                                }
                                            }

                                            // Método 3: Verificar usando isDatasetVisible con el mismo dataset pero índice diferente
                                            try {
                                                if (typeof context.chart.getDataVisibility === 'function') {
                                                    isVisible = context.chart.getDataVisibility(index);
                                                }
                                            } catch (e) {
                                                // Fallback si el método no existe
                                            }
                                            
                                            if (isVisible) {
                                                totalVisible += dataValue || 0;
                                            }
                                        });

                                        const percentageVisible = totalVisible > 0 ? ((value / totalVisible) * 100).toFixed(1) : 0;
                                        const percentageGeneral = totalGeneral > 0 ? ((value / totalGeneral) * 100).toFixed(1) : 0;

                                        // Crear el tooltip con ambos porcentajes
                                        const tooltipLines = [
                                            `${label}: ${self.priceFormat(value)}`,
                                            `Cantidad: ${cantidad}`
                                        ];

                                        // Solo mostrar porcentajes diferentes si hay elementos ocultos
                                        if (totalVisible !== totalGeneral) {
                                            tooltipLines.push(`Porcentaje: ${percentageVisible}%`);
                                        }

                                        tooltipLines.push(`Porcentaje general: ${percentageGeneral}%`);

                                        return tooltipLines;
                                    }
                                }
                            }
                        }
                    }
                });
            },
            sprintf: function () {
                return Tools.sprintf.apply(null, arguments);
            },
            priceFormat: function (value, decimals = 2) {
                const intl = new Intl.NumberFormat('es-AR', {
                    style: 'currency',
                    currency: 'ARS',
                    minimumFractionDigits: decimals,
                    maximumFractionDigits: decimals
                });

                return intl.format(value);
            },
            getPercentage: function (current, total, decimals = 1) {
                if (!total || total === 0) {
                    return '0.0';
                }
                
                const percentage = (current / total) * 100;
                return percentage.toFixed(decimals);
            },
            JText: function(key) {
                return Joomla.JText._(key);
            }
        },
        watch: {
            cotizaciones(newData) {
                if (!this.charts.cotizaciones) {
                    this.initChart();
                    return;
                }

                this.charts.cotizaciones.data.datasets[0].data = [
                    this.cotizacionesRealizadasTotal,
                    this.cotizacionesVendidasTotal,
                    this.cotizacionesRechazadasTotal
                ];

                this.$nextTick(() => {
                    this.charts.cotizaciones.update();
                });
            },
            cotizacionesTodas(newData) {
                if (!this.charts.todas) {
                    this.initChartTodas();
                    return;
                }

                this.charts.todas.data.datasets[0].data = this.cotizacionesTodas.map(c => c.total || 0);
                this.charts.todas.data.datasets[0].backgroundColor = this.cotizacionesTodas.map(c => c.bg_color || '#31708f');
                this.charts.todas.data.labels = this.cotizacionesTodas.map(c => c.estado || 'Sin etiqueta');
                
                this.$nextTick(() => {
                    this.charts.todas.update();
                });
            }
        }
    });

    app.mount('.com_sabullvial');
});