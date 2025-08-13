jQuery(document).ready(function($) {
    const options = Joomla.getOptions('com_sabullvial.cotizacionajax');

    function templateResult(item) {
        const cliente = parseInt(item.id_cliente) == 0 ? 'Consumidor final' : (item.cliente + ` - ` + item.id_cliente);

        return `<div class="cliente-choices-item">
                <b>#` + item.id + ` - ` + cliente  + `</b>
                <br class="chzn-on-title-hide"/>
                <small class="chzn-on-title-hide label" style="background-color: ` + item.estadocotizacion_bg_color +`; color: `+item.estadocotizacion_color+`">` + item.estadocotizacion + `</small> |
                <small class="chzn-on-title-hide">Total: $` + Tools.numberFormat(item.total, 2, '.', ',') + `</small>
            </div>`;
    }

    function templateSelection(item) {

        if (item.element.dataset.id_cliente && item.element.dataset.cliente) {
            item.id_cliente = item.element.dataset.id_cliente;
            item.cliente = item.element.dataset.cliente;
        }

        const cliente = parseInt(item.id_cliente) == 0 ? 'Consumidor final' : (item.cliente + ` - ` + item.id_cliente);

        return `<div class="cliente-choices-item">
                <b>#` + item.id + `</b> - ` + cliente  + `
            </div>`;
    }

    jQuery(options.selector).select2({
        placeholder: 'Selecciona una cotización',
        allowClear: true,
        theme: "bootstrap",
        language: "es",
        minimumInputLength: 1,
        width: '100%',
        ajax: {
            url: options.url,
            dataType: 'json',
            delay: 400,
            data: function (params) {
                return {
                    filter_search: params.term,
                    list: {
                        limit: 50
                    }
                };
            },
            processResults: function (response, params) {
                params.page = params.page || 1;
                let results = [];

                if (!response.success) {
                    return { results };
                }

                return {
                    results: response.data.items,
                }
            }
        },
        escapeMarkup: function (markup) { return markup; }, // let our custom formatter work               
        templateResult: function (item) {
            if (item.loading) {
                return item.text;
            }

            const text = templateResult(item);

            return text;
        },
        templateSelection: function formatState(state) {
            if (!state.id) {
                return state.text;
            }

            const text = templateSelection(state);

            return jQuery(text);
        }
    });
});