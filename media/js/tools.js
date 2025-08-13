const numberFormat = function (value, decimals, decimalSeparator, thousandsSeparator) {
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
}

const debounce = (callback, wait) => {
    let timerId;
    return (...args) => {
        clearTimeout(timerId);
        timerId = setTimeout(() => {
            callback(...args);
        }, wait);
    };
};

const sprintf = function () {

    const args = Array.from(arguments);

    if (args.length == 0) {
        return '';
    }

    if (args.length == 1) {
        return args[0];
    }

    const key = args[0];
    let text = Joomla.Text._(key);

    for (let i = 1; i < args.length; i++) {
        text = text.replace('%s', args[i]);
    }

    return text;
}

const Tools = {
    numberFormat: numberFormat,
    debounce: debounce,
    sprintf: sprintf
}