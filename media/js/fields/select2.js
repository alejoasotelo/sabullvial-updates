jQuery(document).ready(function () {
    jQuery('.select2').select2({
        language: 'es',
        theme: 'bootstrap',
        matcher: function (params, data) {
            if (typeof params.term === 'undefined' || params.term.trim() === '') {
                return data;
            }

            if (typeof data.text === 'undefined') {
                return null;
            }

            const terms = params.term.toLowerCase().split(' ').filter(term => term.trim() !== '');
            const rowText = data.text.toLowerCase().replaceAll('-', ' ');
            let find = null;
            for (let i = 0; i < terms.length; i++) {
                const term = terms[i];
                if (rowText.indexOf(term) > -1) {
                    if (find == null) {
                        find = jQuery.extend({}, data, true);
                    }
                } else {
                    return null;
                }
            }

            return find;
        }
    });
});