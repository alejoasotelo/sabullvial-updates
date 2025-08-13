jQuery(document).ready(function () {

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

    var VENDEDOR_CONDICIONES_DE_VENTA = comOptions.condicionesVenta.map(function(i) { return parseInt(i.id); });
    var CONDICION_VENTA_CONTADO_PUBLICO = 49;
    var CONDICION_VENTA_DEFAULT = VENDEDOR_CONDICIONES_DE_VENTA.length == 0 ? CONDICION_VENTA_CONTADO_PUBLICO :
        (VENDEDOR_CONDICIONES_DE_VENTA.indexOf(CONDICION_VENTA_CONTADO_PUBLICO) >= 0 ? CONDICION_VENTA_CONTADO_PUBLICO : VENDEDOR_CONDICIONES_DE_VENTA.filter(i => i != '')[0]);

    var COTIZACION_TIPO_SRL = 1;
    var COTIZACION_TIPO_PRUEBA = 6;

    var TRANSPORTE_BULLVIAL_RAMA = comOptions.transportes.find(i => i.COD_TRANSP == '00147');

    let DEPOSITO_CONDITIONS = [...comOptions.config.depositoConditions];

    const CONDICIONES_DE_VENTA_STEP_CONSTANT = 15;

    const PREFIX_CUSTOM = 'CUSTOM_';

    const TYPE_COTIZACION = 'cotizacion';
    const TYPE_ORDEN_DE_TRABAJO = 'orden_de_trabajo';

    const getCotizacion = async (quoteId) => {

        const url = `index.php?option=com_sabullvial&task=puntosdeventa.getCotizacion&${comOptions.token}=1&id=${quoteId}`;

        try {
            const response = await fetch(url);
        
            if (!response.ok) {
                console.error('Error en la solicitud');
                return null;
            }
        
            const jsonData = await response.json();
        
            if (jsonData.success) {
              return jsonData.data;
            } 
        } catch (error) {
            console.error(error);
        }

        return null;
    }

    const hideModal = async (modalSelector) => {
        return new Promise((resolve) => {
            const $modal = jQuery(modalSelector);

            // Define el handler para que podamos eliminarlo luego
            const handler = function (event) {
                if (event.target !== this) {
                    return;
                }

                // Elimina el evento después de que se ejecuta correctamente
                $modal.off('hidden', handler);
                resolve();
            };

            // Adjunta el evento
            $modal.on('hidden', handler);

            // Oculta el modal
            $modal.modal('hide');
        });
    }

    var app = Vue.createApp({
        components: ['chosen-select', 'button-yesno'],
        data: function () {
            return {
                test: '',
                busy: false,
                loading: false,
                saving: false,
                productos: [],
                direcciones: [],
                transportes: comOptions.transportes.map(t => {
                    return {
                        id: t.id,
                        text: t.name
                    }
                }),
                depositos: comOptions.depositos.map(d => {
                    return {
                        id: d.id,
                        id_tango: d.id_tango,
                        text: d.name
                    }
                }),
                quotation: {
                    id: 0,
                    consumidorFinal: Joomla.Text._('COM_SABULLVIAL_CONSUMIDOR_FINAL'),
                    cliente: '',
                    id_direccion: '',
                    id_transporte: '',
                    id_deposito: comOptions.vendedor.id_deposito,
                    id_deposito_tango: comOptions.vendedor.id_deposito_tango,
                    documentoTipo: '80',
                    documentoNumero: '',
                    dolar: 0,
                    iva: 1,
                    id_condicionventa: CONDICION_VENTA_DEFAULT,
                    id_condicionventa_fake: CONDICION_VENTA_DEFAULT,
                },
                quotationCreated: {},
                activeFilters: {
                    search: '',
                    ordering: 'codigo_sap',
                    direction: 'ASC',
                    cliente: '',
                    id_cliente: 0,
                    codigo_cliente: '',
                    direccion: {
                        codcli: '',
                        porc_ib: 0
                    }
                },
                vendedor: comOptions.vendedor,
                cart: {
                    productos: [],
                    ordenCompraFile: null,
                    ordendecompra_numero: '',
                    delivery_term: '',
                    observations: '',
                    solicitante: '',
                    note: '',
                    email: '',
                    esperar_pagos: 0
                },
                pagination: comOptions.pagination,
                config: comOptions.config,
                condicionesVenta: comOptions.condicionesVenta,
                modalCliente: '',
                afterCreatedCotizacionStep: 1,
                COTIZACION_TIPO_SRL: COTIZACION_TIPO_SRL,
                COTIZACION_TIPO_PRUEBA: COTIZACION_TIPO_PRUEBA
            }
        },
        created: function () {
            var self = this;

            this.changeSearch = Tools.debounce(function () {
                self.reload();
            }, 300);

            self.reload();

            window.changeLimit = function (el) { self.changeLimit(el.value) };
        },
        mounted: function () {
            var self = this;

            Joomla.submitbutton = function (task) {
                if (task == "puntodeventa.add" && confirm('Se perderan los cambios, seguro que desea crear una nueva cotización?')) {
                    self.reset();
                }
            }

            jQuery('#afterCreatedCotizacion').on('hide', function () {
                self.quotationCreated = {};
                self.afterCreatedCotizacionStep = 1;
            });

        },
        watch: {
            'activeFilters.search': function (newValue) {
                this.changeSearch(newValue);
            },
            'quotation.id_transporte': function (newValue) {
                this.onChangeTransporte(newValue);
            }
        },
        methods: {
            async edit(quote) {
                this.busy = true;
                this.loading = true;

                jQuery('#myCotizaciones').modal('hide');

                notify('Cargando cotizacion...', 'info', {
                    autoHideDelay: 5000
                });

                const newQuote = await getCotizacion(quote.id);

                if (newQuote == null) {
                    notify('No se pudo cargar la cotizacion', 'danger', {
                        autoHideDelay: 5000
                    });

                    this.busy = false;
                    this.loading = false;

                    return;
                }

                await this.loadQuote(newQuote);

                notify('Cotización lista para editar', 'success', {
                    autoHideDelay: 5000
                });

                this.busy = false;
                this.loading = false;
            },
            async duplicate(quote) {
                this.busy = true;
                this.loading = true;

                jQuery('#myCotizaciones').modal('hide');

                notify('Duplicando cotizacion...', 'info', {
                    autoHideDelay: 5000
                });

                const newQuote = await getCotizacion(quote.id);

                if (newQuote == null) {
                    notify('No se pudo cargar la cotizacion', 'danger', {
                        autoHideDelay: 5000
                    });

                    this.busy = false;
                    this.loading = false;

                    return;
                }

                newQuote.cotizacion.id = 0;

                await this.loadQuote(newQuote);

                notify('Cotización duplicada', 'success', {
                    autoHideDelay: 5000
                });

                this.busy = false;
                this.loading = false;
            },
            async loadQuote({ cotizacion, config, direcciones, cliente }) {
                const newCliente = cliente === null ? '' : { ...cliente };
                
                this.config = config;
                this.direcciones = direcciones.map(function (direccion) {
                    return {
                        id: direccion.ID_DIRECCION_ENTREGA,
                        text: direccion.DIR_ENTREGA,
                        porc_ib: parseFloat(direccion.PORC_IB),
                    }
                });
                this.modalCliente = newCliente;

                this.quotation.id = parseInt(cotizacion.id);
                this.quotation.consumidorFinal = cotizacion.cliente;
                this.quotation.cliente = newCliente;
                this.quotation.id_direccion = cotizacion.id_direccion;
                this.quotation.id_transporte = cotizacion.id_transporte;
                this.quotation.id_deposito = cotizacion.id_deposito;
                this.quotation.id_deposito_tango = cotizacion.id_deposito_tango;

                if (this.quotation.id_transporte > 0 && (!this.quotation.id_deposito || this.quotation.id_deposito == 0)) {
                    this.quotation.id_deposito = this.vendedor.id_deposito;
                    this.quotation.id_deposito_tango = this.vendedor.id_deposito_tango;
                    this.onChangeTransporte(this.quotation.id_transporte);
                }

                this.quotation.documentoTipo = cotizacion.documento_tipo;
                this.quotation.documentoNumero = cotizacion.documento_numero;
                this.quotation.dolar = parseInt(cotizacion.dolar);
                this.quotation.iva = parseInt(cotizacion.iva);
                this.quotation.id_condicionventa = cotizacion.id_condicionventa;
                this.quotation.id_condicionventa_fake = cotizacion.id_condicionventa_fake;
                this.quotation.is_orden_de_trabajo = cotizacion.is_orden_de_trabajo;

                this.cart.productos = cotizacion.cotizaciondetalle.map((producto) => {
                    return {
                        id: producto.id_producto,
                        codigo_sap: producto.codigo_sap,
                        nombre: producto.nombre,
                        marca: producto.marca,
                        precio: parseFloat(producto.precio),
                        precioFinal: parseFloat(producto.precio),
                        cantidad: parseInt(producto.cantidad),
                        descuento: parseFloat(producto.descuento),
                        subtotal: parseFloat(producto.subtotal),
                        custom: producto.id_producto.indexOf(PREFIX_CUSTOM) >= 0,
                        selected: true
                    }
                });

                this.cart.ordenCompraFile = null;
                this.cart.ordendecompra_numero = cotizacion.ordendecompra_numero;
                this.cart.delivery_term = cotizacion.delivery_term;
                this.cart.observations = cotizacion.observations;
                this.cart.note = cotizacion.note;
                this.cart.email = cotizacion.email;
                this.cart.solicitante = cotizacion.solicitante;
                this.cart.esperar_pagos = parseInt(cotizacion.esperar_pagos);

                jQuery('input[type="file"]').val(null);

                await this.reload({
                    recalcularPreciosCarrito: false
                });

            },
            save: function (type = TYPE_COTIZACION) {
                type = [TYPE_COTIZACION, TYPE_ORDEN_DE_TRABAJO].includes(type) ? type : TYPE_COTIZACION;

                const self = this;
                var formData = new FormData();

                let listaPrecio = this.quotation.iva ? 1 : 2;
                if (this.quotation.dolar) {
                    listaPrecio = this.quotation.iva ? 10 : 7;
                }

                let porcentajeIIBB = 0;
                if (this.quotation.cliente != '' && this.direccion_porc_ib) {
                    porcentajeIIBB = this.direccion_porc_ib;
                }

                if (!this.isProductsCartValid) {
                    notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_CART_PRODUCTS_INVALID'), 'danger');

                    return false;
                }

                if (this.quotation.id > 0) {
                    formData.append('jform[id]', this.quotation.id);
                }

                let idEstadoCotizacion = comOptions.config.cotizacion_estado_creado;
                if (type == TYPE_ORDEN_DE_TRABAJO) {
                    idEstadoCotizacion = comOptions.config.orden_de_trabajo_estado_creado;
                }

                formData.append('jform[ordendecompra_file]', this.ordenCompraFile);
                formData.append('jform[ordendecompra_numero]', this.cart.ordendecompra_numero);

                formData.append('jform[id_cliente]', this.quotation.cliente == '' ? '000000' : this.quotation.cliente.id);
                formData.append('jform[id_estadocotizacion]', idEstadoCotizacion);
                formData.append('jform[id_condicionventa]', this.quotation.id_condicionventa);
                formData.append('jform[id_condicionventa_fake]', this.quotation.id_condicionventa_fake);
                formData.append('jform[id_direccion]', this.quotation.id_direccion);
                formData.append('jform[id_transporte]', this.quotation.id_transporte);
                formData.append('jform[id_deposito]', this.quotation.id_deposito);
                formData.append('jform[id_deposito_tango]', this.quotation.id_deposito_tango);
                formData.append('jform[cliente]', this.quotation.consumidorFinal);
                formData.append('jform[documento_tipo]', this.quotation.cliente ? '' : this.quotation.documentoTipo);
                formData.append('jform[documento_numero]', this.quotation.cliente ? '' : this.quotation.documentoNumero);
                formData.append('jform[email]', this.cart.email);
                formData.append('jform[delivery_term]', this.cart.delivery_term);
                formData.append('jform[iva]', this.quotation.iva);
                formData.append('jform[dolar]', this.quotation.dolar);
                formData.append('jform[total]', this.total);
                formData.append('jform[productos]', JSON.stringify(this.cart.productos));
                formData.append('jform[note]', this.cart.note);
                formData.append('jform[observations]', this.cart.observations);
                formData.append('jform[solicitante]', this.cart.solicitante);
                formData.append('jform[id_lista_precio]', listaPrecio);
                formData.append('jform[porcentaje_iibb]', porcentajeIIBB);
                formData.append('jform[esperar_pagos]', this.cart.esperar_pagos);
                formData.append('jform[id_estadocotizacionpago]', comOptions.config.cotizacion_estado_esperar_pagos_en_espera);

                formData.append('jform[subtotal]', this.subtotal);
                formData.append('jform[iva_21]', this.iva);
                formData.append('jform[iibb]', this.iibb);

                formData.append('jform[type]', type);

                self.saving = type;

                jQuery.ajax({
                    url: 'index.php?option=com_sabullvial&task=puntosdeventa.saveCotizacion&' + comOptions.token + '=1',
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (result) {
                        self.saving = false;

                        if (result.success) {

                            let quotationCreated = result.data.item;
                            quotationCreated.isNew = self.quotation.id == 0;
                            quotationCreated.products = JSON.parse(quotationCreated.products);

                            self.quotationCreated = quotationCreated;

                            if (result.messages) {
                                Joomla.renderMessages(result.messages);
                                const msg = self.quotation.id > 0 ? 'COM_SABULLVIAL_PUNTOS_DE_VENTA_UPDATE_SUCCESS' : 'COM_SABULLVIAL_PUNTOS_DE_VENTA_SAVE_SUCCESS';

                                notify(Joomla.Text._(msg), 'success');
                            }

                            self.reset();

                            hideModal('#previewCotizacion').then(function() {
                                jQuery('#afterCreatedCotizacion').modal('show');
                            });
                        }
                        else if (result.message) {
                            alert(result.message);
                        }
                    },
                    error: function (result) {
                        self.saving = false;
                        notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_SAVE_ERROR'), 'danger');
                    }
                });

            },
            openCotizacion: function () {
                window.open('index.php?option=com_sabullvial&view=cotizacion&layout=edit&format=pdf&layout=print&id=' + this.quotationCreated.id);
            },
            downloadCotizacion: function () {
                window.location = 'index.php?option=com_sabullvial&view=cotizacion&layout=edit&format=pdf&layout=print&download=1&id=' + this.quotationCreated.id;
            },

            /**
             * 
             * @param int estadoTango puede ser COTIZACION_TIPO_SRL o COTIZACION_TIPO_PRUEBA
             */
            sendToFacturacion: function (estadoTango) {

                jQuery.post('index.php?option=com_sabullvial&task=puntosdeventa.changeEstadoTango&' + comOptions.token + '=1',
                    {
                        id: this.quotationCreated.id,
                        id_estado_tango: estadoTango
                    }).then(function (response) {
                        if (response.success) {
                            jQuery('#afterCreatedCotizacion').modal('hide');
                            Joomla.renderMessages(response.messages);
                            notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_SUCCESS'), 'success');
                            return true;
                        }

                        notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ENVIADA_A_FACTURACION_ERROR'), 'danger');

                        if (response.messages.error) {
                            for (let message of response.messages.error) {
                                notify(message, 'danger');
                            }
                        }
                    });

            },
            reset: function () {
                this.quotation.id = 0;
                this.quotation.consumidorFinal = Joomla.Text._('COM_SABULLVIAL_CONSUMIDOR_FINAL');
                this.quotation.cliente = '';
                this.quotation.id_direccion = '';
                this.quotation.id_transporte = '';
                this.quotation.id_deposito = this.vendedor.id_deposito;
                this.quotation.id_deposito_tango = this.vendedor.id_deposito_tango;
                this.quotation.documentoTipo = '80';
                this.quotation.documentoNumero = '';
                this.quotation.dolar = 0;
                this.quotation.iva = 1;
                this.quotation.id_condicionventa = CONDICION_VENTA_DEFAULT;
                this.quotation.id_condicionventa_fake = CONDICION_VENTA_DEFAULT;

                this.activeFilters.search = '';
                this.activeFilters.ordering = 'codigo_sap';
                this.activeFilters.direction = 'ASC';
                this.activeFilters.cliente = '';
                this.activeFilters.id_cliente = 0;
                this.activeFilters.codigo_cliente = '';
                this.activeFilters.direccion = {
                    codcli: '',
                    porc_ib: 0
                };

                this.cart.productos = [];
                this.cart.ordenCompraFile = null;
                this.cart.ordendecompra_numero = '';
                this.cart.delivery_term = '';
                this.cart.observations = '';
                this.cart.note = '';
                this.cart.email = '';
                this.cart.solicitante = '';
                this.cart.esperar_pagos = 0;

                this.modalCliente = '';

                this.recalcularPrecios();

                jQuery('input[type="file"]').val(null);
            },
            reload: function ({ recalcularPreciosCarrito = true } = {}) {
                var self = this;
                this.loading = true;
                return this.listProductos(self.pagination.limitstart, self.pagination.limit).then(function () {
                    self.refreshPaginationElement();
                    if (recalcularPreciosCarrito) {
                        self.recalcularPreciosCarrito();
                    }
                    self.loading = false;

                    setTimeout(function () {
                        jQuery('.hasTooltip').tooltip();
                    }, 50);
                    return true;
                });
            },

            submitFilter: function () {
                this.reload();
            },
            clearFilter: function () {
                this.activeFilters.search = '';
                this.reload();
            },
            sortProducts: function (ordering, direction) {
                this.activeFilters.ordering = ordering || this.activeFilters.ordering;

                direction = direction || this.activeFilters.direction;
                this.activeFilters.direction = direction.toUpperCase() == 'ASC' ? 'DESC' : 'ASC';

                this.reload();
            },
            refreshPaginationElement: function () {
                var self = this;

                var $pagination = jQuery('#vue-pagination');

                if ($pagination.twbsPagination('getTotalPages') != this.pagination.pagesTotal) {
                    $pagination.twbsPagination('destroy');
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
            changeDocumentoTipo: function (tipo) {
                this.quotation.documentoTipo = tipo;
            },
            changeLimit: function (limit) {
                var self = this;
                this.pagination.limit = limit;
                self.reload();
            },
            changeLimitstart: function (limitstart) {
                this.pagination.limitstart = limitstart;
                self.reload();
            },
            listProductos: function (limitstart, limit) {
                limitstart = limitstart || this.pagination.limitstart;
                limit = limit || this.pagination.limit;

                var self = this;
                
                const queryParams = new URLSearchParams();
                queryParams.append('limitstart', limitstart);
                queryParams.append('list[limit]', limit);
                queryParams.append('list[ordering]', this.activeFilters.ordering);
                queryParams.append('list[direction]', this.activeFilters.direction);
                queryParams.append('filter[search]', this.activeFilters.search);
                queryParams.append('filter[id_condicionventa]', this.quotation.id_condicionventa);

                if (this.quotation.cliente != '') {
                    queryParams.append('filter[id_cliente]', this.quotation.cliente.id);
                }

                const url = `index.php?option=com_sabullvial&view=puntosdeventa&format=json&${comOptions.token}=1&${queryParams.toString()}`;

                return fetch(url).then(response => response.json()).then(function (response) {

                    if (response.success) {
                        self.productos = response.data.items.map(function (producto) {

                            var indexCart = self.cart.productos.findIndex(function (p) {
                                return p.id == producto.id;
                            });

                            producto.selected = indexCart >= 0;
                            producto.cantidad = 1;
                            producto.precioFinal = self.calcPrecioFinal(producto.precio).toFixed(2);
                            producto.descuento = 0;
                            producto.images = producto.images == null ? [] : Object.values(JSON.parse(producto.images));
                            producto.images = producto.images.map(function (image) {
                                image.path = comOptions.uriRoot + image.path;
                                return image;
                            });

                            const ocultarStockDeposito1 = !self.vendedor.ver.stockReal && producto.stock_deposito_1 > 10;
                            const ocultarStockDeposito2 = !self.vendedor.ver.stockReal && producto.stock_deposito_2 > 10;
                            const ocultarStockDeposito3 = !self.vendedor.ver.stockReal && producto.stock_deposito_3 > 10;

                            let popoverHtml = `
                                ${self.JText('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_1', ocultarStockDeposito1 ? '10+' : Tools.numberFormat(producto.stock_deposito_1, 0))}
                                ${self.JText('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_2', ocultarStockDeposito2 ? '10+' : Tools.numberFormat(producto.stock_deposito_2, 0))}
                                ${self.JText('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_3', ocultarStockDeposito3 ? '10+' : Tools.numberFormat(producto.stock_deposito_3, 0))}
                            `;

                            producto.popover = popoverHtml;
                            return producto;
                        });

                        self.pagination = response.data.pagination;
                        self.config.porcentajeDia = response.data.config.porcentajeDia;
                        self.config.cotizacionDolar = response.data.config.cotizacionDolar;
                        return true;
                    }

                    return false;
                });
            },
            recalcularPrecios: function () {
                var self = this;

                this.productos.map(function (producto) {

                    var indexCart = self.cart.productos.findIndex(function (p) {
                        return p.id == producto.id;
                    });

                    producto.selected = indexCart >= 0;
                    producto.cantidad = 1;
                    producto.precioFinal = self.calcPrecioFinal(producto.precio).toFixed(2);
                    return producto;
                });
            },
            recalcularPreciosCarrito: function () {
                var self = this;

                this.cart.productos.map(function (producto) {
                    if (!producto.custom) {
                        producto.precioFinal = self.calcPrecioFinal(producto.precio).toFixed(2);
                    }
                    return producto;
                });

            },
            onChangeIva: function (newIva) {
                this.recalcularPrecios();
                this.recalcularPreciosCarrito();
            },
            onChangeDolar: function (newDolar) {
                this.recalcularPrecios();
                this.recalcularPreciosCarrito();
            },
            onChangeClienteModal: function (cliente) {
                // console.log('Cambió el cliente', cliente);
            },
            onSelectClienteModal: function (cliente) {
                // si es consumidor final
                if (cliente == '') {
                    this.quotation.consumidorFinal = Joomla.Text._('COM_SABULLVIAL_CONSUMIDOR_FINAL');
                    this.quotation.cliente = cliente;

                } else {
                    this.quotation.consumidorFinal = cliente.razon_social;
                    this.quotation.cliente = cliente;
                }

                this.onChangeCliente(cliente);

                jQuery('#selectClientecliente').modal('hide');
            },
            onClickConsumidorFinal: function () {
                this.quotation.consumidorFinal = Joomla.Text._('COM_SABULLVIAL_CONSUMIDOR_FINAL');
                this.quotation.cliente = '';
                this.modalCliente = '';

                this.onChangeCliente('');
            },
            onClickClear: function () {
                this.quotation.consumidorFinal = '';
                this.quotation.cliente = '';
                this.modalCliente = '';

                this.onChangeCliente('');
            },
            onChangeCondicionVentaFake: async function (idCondicionventa) {
                this.busy = true;
                var response = await this.changeCondicionVenta(idCondicionventa);

                if (response.success) {
                    this.quotation.id_condicionventa = idCondicionventa;
                    this.quotation.iva = response.data.iva;
                    this.config = response.data.config;
                    await this.reload();
                }

                this.busy = false;
            },
            onChangeCondicionVenta: function (idCondicionventaReal) {
                var self = this;
                this.busy = true;
                this.changeCondicionVenta(idCondicionventaReal).then(function (response) {

                    if (response.success) {
                        self.quotation.iva = response.data.iva;
                        self.config = response.data.config;
                        self.reload().then(function () {
                            self.busy = false;
                        });
                    } else {
                        self.busy = false;
                    }

                });

            },
            onChangeCliente: async function (cliente) {

                this.busy = true;

                // si es un limpieza o consumidor final
                if (cliente == '' || cliente.id == 0) {
                    this.direcciones = [];
                    this.quotation.id_direccion = '';
                    this.quotation.id_transporte = '';
                    this.quotation.id_deposito = this.vendedor.id_deposito;
                    this.quotation.id_deposito_tango = this.vendedor.id_deposito_tango;
                    this.busy = false;
                    return true;
                }

                var response = await this.changeCliente(cliente.id);

                if (response.success) {
                    // Verifico que la condición de venta del nuevo cliente selccionado exista 
                    // en las condiciones de venta que puede ver el vendedor
                    var id_condicionventa = response.data.id_condicionventa;
                    if (VENDEDOR_CONDICIONES_DE_VENTA.length > 0 && VENDEDOR_CONDICIONES_DE_VENTA.indexOf(response.data.id_condicionventa) == -1) {
                        id_condicionventa = CONDICION_VENTA_DEFAULT;
                        notify(Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_CLIENTE_NO_TIENE_COND_VENTA_VENDEDOR'), 'danger', {
                            autoHideDelay: 10000
                        });
                        Joomla.renderMessages({error: [Joomla.Text._('COM_SABULLVIAL_PUNTOS_DE_VENTA_ERROR_CLIENTE_NO_TIENE_COND_VENTA_VENDEDOR')]});
                    }

                    this.quotation.id_condicionventa_fake = id_condicionventa;
                    this.quotation.id_condicionventa = id_condicionventa;
                    this.quotation.iva = response.data.iva;
                    this.config = response.data.config;
                    this.direcciones = response.data.direcciones.map(function (direccion) {
                        return {
                            id: direccion.ID_DIRECCION_ENTREGA,
                            text: direccion.DIR_ENTREGA,
                            porc_ib: parseFloat(direccion.PORC_IB),
                        }
                    });

                    if (this.direcciones.length) {
                        this.quotation.id_direccion = this.direcciones[0].id;
                    }
                    
                    this.quotation.id_transporte = TRANSPORTE_BULLVIAL_RAMA.id;
                    this.quotation.id_deposito = this.vendedor.id_deposito;
                    this.quotation.id_deposito_tango = this.vendedor.id_deposito_tango;

                    await this.reload();
                }

                this.busy = false;
            },
            onChangeOrdenCompraFile: function (event) {
                this.ordenCompraFile = event.target.files[0];

            },
            changeCliente: function (idCliente) {
                var data = {
                    id_cliente: idCliente,
                    id_condicionventa: this.quotation.id_condicionventa,
                    iva: this.quotation.iva,
                    dolar: this.quotation.dolar,
                };

                return jQuery.post('index.php?option=com_sabullvial&task=puntosdeventa.changeCliente&' + comOptions.token + '=1', data);
            },
            onChangeTransporte: function (idTransporte) {

                const self = this;
                let find = false;

                DEPOSITO_CONDITIONS.forEach((condition) => {

                    if (find || !condition.id_transporte || condition.id_transporte != idTransporte) {
                        return;
                    }
                    
                    this.quotation.id_deposito = condition.id_deposito;
                    this.quotation.id_deposito_tango = self.depositos.find(d => d.id == condition.id_deposito).id_tango;
                    find = true;
                });

            },
            changeCondicionVenta: function (idCondicionventa) {
                var data = {
                    id_cliente: this.quotation.cliente.id,
                    id_condicionventa: idCondicionventa,
                    iva: this.quotation.iva,
                    dolar: this.quotation.dolar,
                };

                return jQuery.post('index.php?option=com_sabullvial&task=puntosdeventa.changeCondicionVenta&' + comOptions.token + '=1', data);
            },
            toggleProducto: function (producto) {
                if (this.loading || this.busy) {
                    return false;
                }

                producto.selected = !producto.selected;

                var indexCart = this.cart.productos.findIndex(function (p) {
                    return p.id == producto.id;
                });

                if (producto.selected) {
                    if (indexCart >= 0) {
                        return;
                    }

                    let newProduct = { ...producto, custom: false };

                    this.cart.productos.push(newProduct);
                } else {
                    var indexProduct = this.productos.findIndex(function (p) {
                        return p.id == producto.id;
                    });

                    if (indexProduct >= 0) {
                        this.productos[indexProduct].selected = false;
                    }

                    this.cart.productos.splice(indexCart, 1);
                }
            },
            addProductoPersonalizado: function () {
                if (this.loading || this.busy) {
                    return false;
                }

                const id = PREFIX_CUSTOM + (Math.floor(Math.random() * Date.now()).toString(16));

                this.cart.productos.push({
                    id: id,
                    nombre: '',
                    codigo_sap: id,
                    marca: 'BULLVIAL',
                    precio: 0,
                    precioFinal: 0,
                    descuento: 0,
                    cantidad: 0,
                    selected: true,
                    custom: true
                });
            },
            numberFormat: Tools.numberFormat,
            calcPrecioFinal(precio) {
                var self = this;

                precio = parseFloat(precio);

                var condicionVenta = this.condicionesVenta.find(i => i.id == self.quotation.id_condicionventa);

                var kInteres = parseInt(condicionVenta.dias) * this.config.porcentajeDia / 100;
                var precioFinal = precio + (precio * kInteres);

                if (this.quotation.dolar) {
                    precioFinal /= parseFloat(this.config.cotizacionDolar);
                }

                if (this.quotation.iva) {
                    precioFinal *= 1.21;
                }

                return precioFinal;
            },
            JText(key, value) {
                return Joomla ? Joomla.Text._(key).replace('%s', value) : key;
            },
            formatDocument(documentoTipo) {
                if (documentoTipo == 80) {
                    return 'CUIT';
                } else {
                    return 'DNI'; // 96
                }
            },
            limitDescuento(event, producto) {
                if (producto.descuento < 0 || event.target.value.trim() == '') {
                    producto.descuento = 0;
                } else if (producto.descuento > 100) {
                    producto.descuento = 100;
                } else {
                    producto.descuento = parseFloat(producto.descuento);
                }
            }
        },
        computed: {
            direccion_porc_ib() {
                let self = this;

                if (!this.quotation.id_direccion) {
                    return 0;
                }

                var direccion = this.direcciones.find(function (direccion) {
                    return direccion.id == self.quotation.id_direccion;
                });

                return direccion ? direccion.porc_ib : 0;
            },
            subtotal() {
                this.quotation.iva; // hack para que cambien los precios al cambiar el IVA

                return this.cart.productos.length > 0 ? this.cart.productos.reduce(function (acum, item) {

                    return acum + (item.precioFinal * item.cantidad * (1 - item.descuento / 100))

                }, 0) : 0;
            },
            iva() {
                var iva = 0;

                if (!this.quotation.iva) {
                    iva = this.subtotal * 0.21;
                }

                return iva;
            },
            iibb() {
                let iibb = 0;

                if (this.direccion_porc_ib > 0) {
                    const porcentajeIIBB = parseFloat(this.direccion_porc_ib);

                    iibb = this.quotation.iva ? this.subtotal / 1.21 : this.subtotal;
                    iibb *= (porcentajeIIBB / 100); // 0.1%
                }

                return iibb;
            },
            total() {
                return this.subtotal + this.iva + this.iibb;
            },
            condicionesVentaReales() {
                // Muestra las condiciones de venta seleccionada, las que estan por encima y las que son multiplos de 15
                
                const idCondicionventaFake = parseInt(this.quotation.id_condicionventa_fake);
                const condicionVentaFake = this.condicionesVenta.find(i => i.id == idCondicionventaFake);
                const nombreCondicionventaFake = condicionVentaFake.nombre.trim();

                const keysToExit = [
                    'MERCADOLIBRE',
                    'TRANSFERENCIA'
                ];

                if (keysToExit.includes(nombreCondicionventaFake)) {
                    return [condicionVentaFake];
                }

                let lastDias = -1;
                let isUpDirection = true;

                const isMultiploFromStep = function (currentDias) {
                    return currentDias % CONDICIONES_DE_VENTA_STEP_CONSTANT === 0;
                }

                let mayor = false;
                return this.condicionesVenta.filter((item) => {
                    const currentId = parseInt(item.id);

                    if (mayor || currentId == idCondicionventaFake) {
                        mayor = true;
                        return true;
                    }

                    return false;
                }).filter((item) => {
                    const currentId = parseInt(item.id);
                    const currentDias = parseInt(item.dias);

                    if (!isUpDirection) {
                        return false;
                    }

                    if (currentId == idCondicionventaFake) {
                        lastDias = currentDias;
                        return true;
                    }

                    const isZero = currentDias === 0 && lastDias === 0;

                    if (isZero) {
                        lastDias = currentDias;
                        return true;
                    }

                    if (currentDias > lastDias && isMultiploFromStep(currentDias)) {
                        lastDias = currentDias;
                        return true;
                    }
                    
                    isUpDirection = false;
                    return false;
                });
            }, 
            isProductsCartValid() {
                var isValid = true;

                if (this.cart.productos.length == 0) {
                    isValid = false;
                }

                // Si un producto tiene cantidad < 0 o no tiene nombre es invalido
                this.cart.productos.forEach(function (producto) {
                    if (isNaN(producto.cantidad) || producto.cantidad === '' || parseInt(producto.cantidad) < 0 || 
                        isNaN(producto.precioFinal) || producto.precioFinal === '' || producto.precioFinal < 0 || 
                        producto.nombre == '' || producto.descuento < 0 || producto.descuento > 100) {
                        isValid = false;
                    }
                });

                return isValid;
            },
            isCartEmailValid() {

                if (this.cart.email.length > 0 && !document.getElementById('cotizacion_email').validity.valid) {
                    return false;
                }

                return true;

            },
            isCotizacionFormValid() {
                if (this.total < 0) {
                    return false;
                }

                if (!this.isCartEmailValid) {
                    return false;
                }

                if (!this.isProductsCartValid) {
                    return false;
                }

                return true;
            },
            haveCartCustomProducts() {
                // if quotationCreated is null, {} or undefined, return false
                if (!this.quotationCreated || !this.quotationCreated.products) {
                    return false;
                }

                return this.quotationCreated.products.filter(function (producto) {
                    return producto.custom;
                }).length > 0;
            },
            canSendToFacturacion() {

                if (!this.quotationCreated || !this.quotationCreated.id) {
                    return false;
                }

                const idEstadoCotizacion = parseInt(this.quotationCreated.id_estadocotizacion);
                const isEstadoCreado = idEstadoCotizacion == comOptions.config.cotizacion_estado_creado || idEstadoCotizacion == comOptions.config.orden_de_trabajo_estado_creado;

                if (!isEstadoCreado) {
                    return false;
                }

                return true;

            },
            TYPE_COTIZACION: _ => TYPE_COTIZACION,
            TYPE_ORDEN_DE_TRABAJO: _ => TYPE_ORDEN_DE_TRABAJO
        }
    })

    app.component('popover', popoverComponent());
    app.component('button-yesno', buttonYesNoComponent());
    app.component('choices-select', choicesSelectComponent());
    app.component("chosen-select", chosenSelectComponent());
    app.component('select2-ajax', select2AjaxComponent());
    app.component('select2', select2Component());
    app.component('search', searchComponent());
    app.component('search-filters', searchFiltersComponent());
    app.component('status-sync-label', statusSyncLabelComponent());
    app.component('cliente-label', clienteLabelComponent());
    app.component('send-to-facturacion-button', sendToFacturacionButtonComponent());
    app.component('quotes', quotesComponent());
    app.component('carousel', carouselComponent());

    app.mount('.com_sabullvial');

})