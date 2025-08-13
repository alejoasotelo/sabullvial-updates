jQuery(document).ready(function() {
    var options = Joomla.getOptions('com_sabullvial');

    const ESTADO_PRODUCTO = {
        INCOMPLETO: -1,
        PENDIENTE: 0,
        COMPLETO: 1
    }
    
    Vue.createApp({
        data: function() {
            return {
                productos: options.cotizacion.cotizaciondetalle.map(function(item) {
                    item.estado = item.cantidad <= 0 ? ESTADO_PRODUCTO.COMPLETO : ESTADO_PRODUCTO.PENDIENTE;
                    item.cantidad_disponible = 0;
                    item.reviewed = item.cantidad <= 0 ? true : false;
                    return item;
                })
            }
        },
        mounted() {
            window.parent.setCotizacionId(options.cotizacion.id);

            // Para el caso en que son todos productos con cantidad 0
            if (this.reviewedCompleted) {
                window.parent.setReviewData(this.productos);
                window.parent.enableButtonReview(options.cotizacion.id);
            }
        },
        methods: {
            completo: function(producto) {
                producto.estado = ESTADO_PRODUCTO.COMPLETO;
                producto.cantidad_disponible = producto.cantidad;
                producto.reviewed = true;

                window.parent.setReviewData(this.productos);
                this.checkButtonReview();
            },
            incompleto: function(producto) {
                producto.estado = ESTADO_PRODUCTO.INCOMPLETO;
                producto.cantidad_disponible = 0;
                producto.reviewed = true;

                window.parent.setReviewData(this.productos);
                this.checkButtonReview();
            },
            updateData: function(producto) {

                if (producto.cantidad_disponible > producto.cantidad) {
                    producto.cantidad_disponible = producto.cantidad;
                }

                window.parent.setReviewData(this.productos);
                this.checkButtonReview();
            },
            checkButtonReview: function() {

                if (this.reviewedCompleted) {
                    window.parent.enableButtonReview(options.cotizacion.id);
                }

            }
        },
        computed: {
            reviewedCompleted: function() {
                
                var i = this.productos.findIndex(function(item) {
                    return item.reviewed == false;
                });

                return i >= 0 ? false: true;

            }
        }
    }).mount('#modalReviewApp');
    
})