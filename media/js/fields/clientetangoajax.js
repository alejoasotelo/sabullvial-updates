jQuery(document).ready(function($) {
    const options = Joomla.getOptions('com_sabullvial.clientetangoajax');

    function templateResult(item) {
        const cliente = item.razon_social;

        return `<div class="cliente-choices-item">
                <b>${cliente}</b>
                <br class="chzn-on-title-hide"/>
                <small class="chzn-on-title-hide">C.U.I.T: ${item.cuit}</small>
            </div>`;
    }

    function templateSelection(item) {

        if (item.element.dataset.id && item.element.dataset.razon_social) {
            item.id = item.element.dataset.id;
            item.razon_social = item.element.dataset.razon_social;
        }

        const cliente = parseInt(item.id) == 0 ? 'Consumidor final' : item.razon_social;

        return `<div class="cliente-choices-item">
                ${cliente}
            </div>`;
    }

    jQuery(options.selector).select2({
        placeholder: options.placeholder || '- Seleccione un cliente tango -',
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