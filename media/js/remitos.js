jQuery(document).ready(function() {

    var styles = ['success', 'danger', 'warning', 'info'];
    styles.forEach(style => {
        jQuery.notify.addStyle('alert-' + style, {
            html:
                `<div>
                    <div class="clearfix alert alert-${style}">
                        <button type="button" class="close" data-dismiss="alert">×</button>
                        <div class="notify-title" data-notify-html/>
                    </div>
                </div>`
        });
    });

    window.notify = function (text, style, options) {
        style = style || 'info';
        options = options || {};
        options = { style: 'alert-' + style, ...options };

        jQuery.notify(text, options);
    }

    var comOptions = Joomla.getOptions('com_sabullvial');

    const vehiculoEmptyOption = {
        id: 0,
        nombre: '',
        patente: Joomla.Text._('JOPTION_SELECT_VEHICULO'),
        id_chofer: 0
    };

    const choferEmptyOption = {
        id: 0,
        nombre: '',
        patente: Joomla.Text._('JOPTION_SELECT_CHOFER')
    };

    let vehiculosWithEmptyOption = [vehiculoEmptyOption, ...comOptions.vehiculos];
    let choferesWithEmptyOption = [choferEmptyOption, ...comOptions.choferes];

    var app = Vue.createApp({
        data() {
            return {
                loading: false,
                loadingModal: false,
                loadingModalDetail: false,
                remitos: [],
                activeFilters: {
                    search: '',
                    ordering: comOptions.ordering,
                    direction: comOptions.direction,
                    estado: '',
                    codigo_vendedor: '',
                    date_from: '',
                    date_to: ''
                },
                vendedor: comOptions.vendedor,
                pagination: comOptions.pagination,
                cart: {
                    remitos: [],
                },
                deliveredByCounter: {
                    deliveryDate: '',
                },
                generateRouteSheet: {
                    deliveryDate: '',
                    vehiculo: {},
                    patente: '',
                    chofer: {},
                    choferNombre: '',
                },
                addRemitosToRouteSheet: {
                    id_hojaderuta: null,
                },
                detailRemito: {},
                vehiculos: vehiculosWithEmptyOption,
                choferes: choferesWithEmptyOption,
                selectedAll: false
            }
        },
        components: {
            'label-estado': labelEstadoComponent()
        },
        created() {
            const self = this;

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
            window.changeEstado = function(el) {
                self.activeFilters.estado =  el.value;
                self.changeSearch();
            }
        },
        methods: {
            preventSubmit: function (e) {
                e.preventDefault();
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
                return this.listRemitos(self.pagination.limitstart, self.pagination.limit).then(function () {
                    self.refreshPaginationElement();
                    self.loading = false;

                    setTimeout(function () {
                        jQuery('.hasTooltipImg').tooltip();
                        jQuery('.table .hasTooltip').tooltip();
                    }, 50);
                    return true;
                });
            },
            reset: function () {
                this.activeFilters.search = '';
                this.activeFilters.ordering = comOptions.ordering;
                this.activeFilters.direction = comOptions.direction;
                this.activeFilters.estado = '';
                this.activeFilters.codigo_vendedor = '';
                this.activeFilters.date_from = '';
                this.activeFilters.date_to = '';
                jQuery('#filter_estado').val(this.activeFilters.estado).trigger('liszt:updated');
                jQuery('#filter_codigo_vendedor').val(this.activeFilters.codigo_vendedor).trigger('liszt:updated');
                this.refreshUIOrdering();
            },
            selectAll: function() {
                this.selectedAll = !this.selectedAll;
                this.remitos.forEach(function (remito) {
                    if (!remito.entregado) {
                        remito.selected = this.selectedAll;
                    }
                }, this);
            },
            toggleRemito: function (remito, e) {
                if (e) {
                    e.stopPropagation();
                }

                if (this.loading || remito.entregado) {
                    return false;
                }

                remito.selected = !remito.selected;

                var indexCart = this.cart.remitos.findIndex(function (p) {
                    return p.id == remito.id;
                });

                if (remito.selected) {
                    if (indexCart >= 0) {
                        return;
                    }

                    this.cart.remitos.push({ ...remito });
                } else {
                    var indexProduct = this.remitos.findIndex(function (p) {
                        return p.id == remito.id;
                    });

                    if (indexProduct >= 0) {
                        this.remitos[indexProduct].selected = false;
                    }

                    this.cart.remitos.splice(indexCart, 1);
                }
            },
            showModalGenerateRouteSheet: function () {
                if (!this.cart.remitos.length) {
                    notify(Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'danger');
                    return;
                }

                jQuery('#modalGenerateRouteSheet').modal('show');

                this.generateRouteSheet.deliveryDate = '';
                this.setVehiculo(0);
                this.generateRouteSheet.patente = '';
                this.setChofer(0);
                this.setChoferNombre('');
            },
            setChoferYPatente: function () {

                var idVehiculo = this.generateRouteSheet.vehiculo.id;

                var vehiculo = this.vehiculos.find(function (vehiculo) {
                    return vehiculo.id == idVehiculo;
                });

                if (!vehiculo) {
                    return;
                }

                this.generateRouteSheet.patente = vehiculo.id == 0 ? '' : vehiculo.patente;

                this.setChofer(vehiculo.id_chofer);
                this.setChoferNombre(this.generateRouteSheet.chofer.name);
            },
            setVehiculo: function (idVehiculo) {

                var vehiculo = this.vehiculos.find(function (vehiculo) {
                    return vehiculo.id == idVehiculo;
                });

                if (!vehiculo) {
                    return;
                }

                this.generateRouteSheet.vehiculo = vehiculo;
            },
            setChofer: function (idChofer) {

                var chofer = this.choferes.find(function (chofer) {
                    return chofer.id == idChofer;
                });

                if (!chofer) {
                    return;
                }

                this.generateRouteSheet.chofer = chofer;
            },
            setChoferNombre: function (nombre) {
                this.generateRouteSheet.choferNombre = nombre;
            },
            showModalDeliveredByCounter: function () {

                if (!this.cartRemitosSelected) {
                    notify(Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'danger');
                    //alert(Joomla.Text._('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'));
                    return;
                }

                jQuery('#modalDeliveredByCounter').modal('show');
                this.deliveredByCounter.deliveryDate = '';
            },
            showModalDetailRemito: function (remito, e) {
                if (e) {
                    e.stopPropagation();
                }

                let self = this;

                if (!remito || !remito.numero_remito) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_MODAL_REMITO_DETAIL_REMITO_REMITO_REQUERIDO'), 'danger');
                    return false;
                }

                this.detailRemito = remito;

                this.loadingModalDetail = true;

                this.getDetailRemito(remito.numero_remito).then(function (response) {
                    if (response) {
                        self.detailRemito.productos = response.productos;
                        self.detailRemito.historico = response.historico;
                    }
                    self.loadingModalDetail = false;
                });

                jQuery('#modalDetailRemito').modal('show');
            },
            showModalAddRemitoToRouteSheet: function() {

                if (!this.cartRemitosEnProcesoSelected) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PROCESO'), 'danger');
                    return;
                }

                if (this.cartRemitosSelected > this.cartRemitosEnProcesoSelected) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PROCESO_ONLY'), 'danger');
                    return;
                }

                this.loadingModal = false;
                this.addRemitosToRouteSheet.id_hojaderuta = null;

                jQuery('#modalAddRemitoToRouteSheet').modal('show');
            },
            templateOptionHojasDeRuta: function(item) {
                let text = `
                    <div class="cliente-choices-item">
                        <b>${item.id}</b>
                        <br class="chzn-on-title-hide"/>
                        <small class="chzn-on-title-hide text-info">
                            <span>F. de entrega:</span> 
                            <span>${this.formatDate(item.delivery_date)}</span>
                        </small> |
                        <small class="chzn-on-title-hide">${item.chofer}</small> | 
                        <small class="chzn-on-title-hide">${item.patente}</small>
                    </div>
                `;

                return text;
            },
            deleteRemitosFromRouteSheet: async function () {

                if (!this.cartRemitosEnPreparacionSelected) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PREPARACION'), 'danger');
                    return;
                }

                if (this.cartRemitosSelected > this.cartRemitosEnPreparacionSelected) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_REMITOS_EN_PREPARACION_ONLY'), 'danger');
                    return;
                }

                if (!confirm(Joomla.Text._('COM_SABULLVIAL_REMITOS_CONFIRM_DELETE_REMITOS_FROM_ROUTE_SHEET'))) {
                    return;
                }

                notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_DELETING_REMITOS_FROM_ROUTE_SHEET'), 'info');

                const data = {
                    cid: this.cart.remitos.map(remito => remito.id),
                };

                const response = await jQuery.post('index.php?option=com_sabullvial&task=remito.deleteRemitosFromRouteSheet&' + comOptions.token + '=1', data);
                
                if (!response.success) {
                    notify(response.message, 'danger');
                    return;
                }

                notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_DELETE_REMITOS_FROM_ROUTE_SHEET_SUCCESS'), 'success');
                this.cart.remitos = [];
                this.selectedAll = false;
                this.reload();
            },
            deleteRemitoImage: async function(idRemitoHistorico) {

                if (!idRemitoHistorico) {
                    return;
                }

                if (!confirm(Joomla.Text._('COM_SABULLVIAL_REMITOS_CONFIRM_DELETE_REMITO_IMAGE'))) {
                    return;
                }

                const response = await jQuery.post(`index.php?option=com_sabullvial&task=remito.deleteImagenByIdRemitoHistorico&id=${idRemitoHistorico}&${comOptions.token}=1`);

                if (!response.success) {
                    notify(response.message, 'danger');
                    return;
                }

                notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_DELETE_REMITO_IMAGE_SUCCESS'), 'success');

                const findRemitoHistorico = this.detailRemito.historico.find(function (item) {
                    return item.id == idRemitoHistorico;
                });

                if (findRemitoHistorico) {
                    findRemitoHistorico.image = '';
                }
            },
            getDetailRemito: function (numeroRemito) {
                let url = 'index.php?option=com_sabullvial&task=remito.getDetailRemito&' + comOptions.token + '=1';

                return jQuery.post(url, { numero_remito: numeroRemito }).then(function (response) {
                    return response.success ? response.data : false;
                });;
            },
            saveGenerateRouteSheet: function (e) {
                e.preventDefault();

                if (!this.generateRouteSheet.deliveryDate) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_ERROR_DELIVERY_DATE'), 'danger');
                    return false;
                }

                const self = this;

                const data = {
                    cid: this.cart.remitos.map(function (remito) {
                        return remito.id;
                    }),
                    delivery_date: this.generateRouteSheet.deliveryDate,
                    id_vehiculo: this.generateRouteSheet.vehiculo.id,
                    patente: this.generateRouteSheet.patente,
                    id_chofer: this.generateRouteSheet.chofer.id,
                    chofer_nombre: this.generateRouteSheet.choferNombre,
                };

                this.loadingModal = true;

                return jQuery.post('index.php?option=com_sabullvial&task=remito.generarHojaDeRuta&' + comOptions.token + '=1', data).then(function (response) {
                    if (response.success) {
                        notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_SUCCESS'), 'success');
                        self.loadingModal = false;
                        jQuery('#modalGenerateRouteSheet').modal('hide');
                        self.cart.remitos = [];
                        self.reload();
                    } else {
                        notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_GENERATE_ROUTE_SHEET_ERROR'), 'danger');
                        self.loadingModal = false;
                    }
                });
            },
            saveDeliveredByCounter: function (e) {
                e.preventDefault();

                if (!this.deliveredByCounter.deliveryDate) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_ERROR_DELIVERY_DATE'), 'danger');
                    return false;
                }

                var self = this;

                var data = {
                    cid: this.cart.remitos.map(function (remito) {
                        return remito.id;
                    }),
                    delivery_date: this.deliveredByCounter.deliveryDate,
                };

                this.loadingModal = true;

                return jQuery.post('index.php?option=com_sabullvial&task=remito.marcarComoEntregadoPorMostrador&' + comOptions.token + '=1', data).then(function (response) {
                    if (response.success) {
                        notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_DELIVERED_BY_COUNTER_SUCCESS'), 'success');
                        self.cart.remitos = [];
                        self.selectedAll = false;
                        self.reload();
                    } else {
                        notify(response.message, 'danger');
                    }

                    return response;
                }).always(function () {
                    self.loadingModal = false;
                    jQuery('#modalDeliveredByCounter').modal('hide');
                });
            },
            saveAddRemitosToRouteSheet: async function (e) {
                e.preventDefault();

                if (!this.addRemitosToRouteSheet.id_hojaderuta) {
                    notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_PLEASE_SELECT_ROUTE_SHEET'), 'danger');
                    return false;
                }

                this.loadingModal = true;

                const data = {
                    cid: this.cart.remitos.map(function (remito) {
                        return remito.id;
                    }),
                    id_hojaderuta: this.addRemitosToRouteSheet.id_hojaderuta.id,
                };

                const response = await jQuery.post('index.php?option=com_sabullvial&task=remito.addRemitosToRouteSheet&' + comOptions.token + '=1', data);
                
                if (!response.success) {
                    notify(response.message, 'danger');
                    this.loadingModal = false;
                    return;
                }

                notify(Joomla.Text._('COM_SABULLVIAL_REMITOS_ADD_REMITOS_TO_ROUTE_SHEET_SUCCESS'), 'success');
                this.cart.remitos = [];
                this.selectedAll = false;
                this.reload();
                
                this.loadingModal = false;
                jQuery('#modalAddRemitoToRouteSheet').modal('hide');
            },
            formatPrice: function (value) {
                var formatter = new Intl.NumberFormat("es-AR", {
                    style: "currency",
                    currency: "ARS",
                    maximumFractionDigits: 2,
                    minimumFractionDigits: 2
                });
            
                var formated = formatter.format(value);
            
                // replace whitespace by none
                return formated.replace(/\s/g, '');
            },
            numberFormat: function (value, decimals, decimalSeparator, thousandsSeparator) {
                if (!value && value !== 0) {
                    return '';
                }

                value = (value + '').replace(/[^0-9+\-Ee.]/g, '');
                var n = !isFinite(+value) ? 0 : +value,
                    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
                    sep = (typeof thousandsSeparator === 'undefined') ? ',' : thousandsSeparator,
                    dec = (typeof decimalSeparator === 'undefined') ? '.' : decimalSeparator,
                    s = '',
                    toFixedFix = function (n, prec) {
                        var k = Math.pow(10, prec);
                        return '' + Math.round(n * k) / k;
                    };
                // Fix for IE parseFloat(0.55).toFixed(0) = 0;
                s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
                if (s[0].length > 3) {
                    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
                }
                if ((s[1] || '').length < prec) {
                    s[1] = s[1] || '';
                    s[1] += new Array(prec - s[1].length + 1).join('0');
                }
                return s.join(dec);
            },

            refreshPaginationElement: function () {
                const self = this;
                const $pagination = jQuery('#vue-pagination');

                if ($pagination.twbsPagination('getTotalPages') != this.pagination.pagesTotal) {
                    $pagination.twbsPagination('destroy');
                }

                if (this.pagination.pagesTotal == 0) {
                    return;
                }

                $pagination.twbsPagination({
                    totalPages: this.pagination.pagesTotal,
                    visiblePages: 10,
                    startPage: this.pagination.pagesCurrent,
                    initiateStartPageClick: false,
                    first: '<span class="icon-backward icon-first"></span>',
                    prev: '<span class="icon-step-backward icon-previous"></span>',
                    next: '<span class="icon-step-forward icon-next"></span>',
                    last: '<span class="icon-forward icon-last"></span>',
                    onPageClick: function (event, page) {
                        self.pagination.limitstart = (page - 1) * self.pagination.limit;
                        self.reload();
                    }
                });
            },
            
            changeLimit: function (limit) {
                var self = this;
                this.pagination.limit = limit;
                self.reload();
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
            sortRemitos: function (ordering, direction) {
                this.activeFilters.ordering = ordering || this.activeFilters.ordering;

                direction = direction || this.activeFilters.direction;
                this.activeFilters.direction = direction.toUpperCase() == 'ASC' ? 'DESC' : 'ASC';

                this.refreshUIOrdering();

                this.reload();
            },

            listRemitos: async function (limitstart, limit) {
                limitstart = limitstart || this.pagination.limitstart;
                limit = limit || this.pagination.limit;

                var self = this;

                var data = {
                    limitstart: limitstart
                };

                data['list[limit]'] = limit;
                data['list[ordering]'] = this.activeFilters.ordering;
                data['list[direction]'] = this.activeFilters.direction;
                data['filter[search]'] = this.activeFilters.search;
                data['filter[estado]'] = this.activeFilters.estado;
                data['filter[codigo_vendedor]'] = this.activeFilters.codigo_vendedor;
                data['filter[date_from]'] = this.activeFilters.date_from;
                data['filter[date_to]'] = this.activeFilters.date_to;

                const response = await jQuery.post('index.php?option=com_sabullvial&view=remitos&format=json&' + comOptions.token + '=1', data);

                if (response.success) {
                    self.remitos = response.data.items.map(function (item) {
                        let index = self.cart.remitos.findIndex(function (p) {
                            return p.id == item.id;
                        });

                        item.selected = index >= 0;
                        item.entregado = item.entregado > 0;
                        item.montoRemitoFormated = self.formatPrice(item.monto_remito);
                        return item;
                    });
                    self.pagination = response.data.pagination;
                    return true;
                }

                return false;
            },
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
            formatDatetime: function (value) {
                if (!value) {
                    return '';
                }

                var parts = value.split(' ');
                var dateParts = parts[0].split('-');
                var timeParts = parts[1].split(':');
                return dateParts[2] + '-' + dateParts[1] + '-' + dateParts[0] + ' ' + timeParts[0] + ':' + timeParts[1] + 'hs';
            },
            sprintf: function () {
                return Tools.sprintf.apply(null, arguments);
            },
            refreshUIOrdering: function () {
                jQuery('#list_fullordering').val(this.activeFilters.ordering + ' ' + this.activeFilters.direction).trigger('liszt:updated');
            },
        },
        computed: {
            cartRemitosSelected: function () {
                return this.cart.remitos.length;
            },
            cartRemitosEnProcesoSelected: function () {
                return this.cart.remitos.filter(function (remito) {
                    return parseInt(remito.estadoremito_proceso) === 1;
                }).length;
            },
            cartRemitosEnPreparacionSelected: function() {
                return this.cart.remitos.filter(function (remito) {
                    return parseInt(remito.estadoremito_preparacion) === 1;
                }).length;
            },
            cartTotalRemitos: function () {

                if (!this.cart.remitos.length) {
                    return 0;
                }

                let total = 0;

                this.cart.remitos.forEach(function (remito) {
                    total += parseFloat(remito.monto_remito);
                });

                return total;
            },
            cartTotalRemitosFormated: function () {
                return this.formatPrice(this.cartTotalRemitos);
            },
            generateRouteSheetMontoSeguro: function () {

                if (this.generateRouteSheet.vehiculo.id == 0) {
                    return 0;
                }
                
                let vehiculo = this.vehiculos.find(v => v.id == this.generateRouteSheet.vehiculo.id);                

                if (!vehiculo) {
                    return 0;
                }

                return vehiculo.monto_seguro;
            },
            modalDetailRemitoProductosTotal: function () {

                if (this.loadingModalDetail) {
                    return 0;
                }

                if (!this.detailRemito || !this.detailRemito.productos.length) {
                    return 0;
                }

                let total = 0;

                this.detailRemito.productos.forEach(function (producto) {
                    total += parseFloat(producto.precio_unitario) * parseInt(producto.cantidad_remito);
                });

                return total;
            }
        },
        watch: {
            'activeFilters.search': function (newValue) {
                this.changeSearch(newValue);
            }
        }
    });

    app.component('chosen-select', chosenSelectComponent());
    app.component('select2-ajax', select2AjaxComponent());

    app.mount('.com_sabullvial');
});