jQuery(document).ready(function($) {
    const options = Joomla.getOptions('com_sabullvial.remitoajax');

    function templateResult(item) {

        return `<div class="cliente-choices-item">
                <b>#${item.id} -  ${item.cliente}</b>
                <br class="chzn-on-title-hide"/>
                <small class="chzn-on-title-hide label" style="background-color: ${item.estadoremito_bg_color}; color: ${item.estadoremito_color}">${item.estadoremito}</small> |
                <small class="chzn-on-title-hide">Expreso: ${item.expreso}</small> |
                <small class="chzn-on-title-hide">Dirección: ${item.direccion_entrega}</small>
            </div>`;
    }

    function templateSelection(item) {

        if (item.element.dataset.id && item.element.dataset.cliente) {
            item.id = item.element.dataset.id;
            item.cliente = item.element.dataset.cliente;
        }

        return `<div class="cliente-choices-item">
                <b>#${item.id}</b> - ${item.cliente}
            </div>`;
    }

    jQuery(options.selector).select2({
        placeholder: options.placeholder || '- Seleccione un remito -',
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

    const destroyJqueryChosen = function(options) {
        jQuery(options.selector).on('liszt:ready', function() {
            console.log('Eliminando chosen');
            setTimeout(_ => jQuery(options.selector).chosen('destroy'), 100);
        });

        const hasJqueryChosen = jQuery(options.selector).data('chosen') != undefined;
        if (hasJqueryChosen) {
            console.log('Tenia chosen y lo elimino');
            jQuery(options.selector).chosen('destroy');
        }
    }

    destroyJqueryChosen(options);
    $("body").on("subform-row-add", _ => {
        console.log('Eliminando chosen on add row');
        destroyJqueryChosen(options);
    });

});