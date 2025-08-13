jQuery(document).ready(function($) {
    const options = Joomla.getOptions('com_sabullvial.clienteajax');

    function templateResult(item) {
        const cliente = item.razon_social;

        const documentos = {
            80: 'C.U.I.T',
            86: 'C.U.I.L',
            96: 'D.N.I'
        };
        const documento = documentos[item.documento_tipo];

        return `<div class="cliente-choices-item">
                <b>#${item.id} -  ${cliente}</b>
                <br class="chzn-on-title-hide"/>
                <small class="chzn-on-title-hide label" style="background-color: ${item.estadocliente_bg_color}; color: ${item.estadocliente_color}">${item.estadocliente}</small> |
                <small class="chzn-on-title-hide">${documento}: ${item.documento_numero}</small> |
                <small class="chzn-on-title-hide">Vendedor: ${item.codigo_vendedor}</small>
            </div>`;
    }

    function templateSelection(item) {

        if (item.element.dataset.id && item.element.dataset.razon_social) {
            item.id = item.element.dataset.id;
            item.razon_social = item.element.dataset.razon_social;
        }

        const cliente = parseInt(item.id) == 0 ? 'Consumidor final' : (item.razon_social + ` - ` + item.id);

        return `<div class="cliente-choices-item">
                <b>#` + item.id + `</b> - ` + cliente  + `
            </div>`;
    }

    jQuery(options.selector).select2({
        placeholder: options.placeholder || '- Seleccione un cliente -',
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