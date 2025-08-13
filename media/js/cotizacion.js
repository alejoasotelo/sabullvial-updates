function reload(element, subtask){
    Joomla.loadingLayer('show');
    jQuery('input[name=task]').val('cotizacion.reload');
    jQuery('input[name=subtask]').val(subtask);
    Joomla.submitform('cotizacion.reload', element.form);
}

function clienteHasChanged(element) {
    let elVal = jQuery(element).val();
    elVal = elVal == '' || elVal == 0 ? 0 : elVal;
    const assignedIdCliente = Joomla.getOptions('com_sabullvial.cotizacion').assignedIdCliente;
    let currentIdCliente = assignedIdCliente;
    currentIdCliente = currentIdCliente == '' || currentIdCliente == 0 ? 0 : currentIdCliente;
    
    if (elVal == currentIdCliente) {
        return;
    }

    reload(element, 'cotizacion.changeCliente');
}

function isNumeric(num) {	
    return num >= 0;
}

function getSubtotal() {
    return this.cotizacionDetalles.length > 0 ? this.cotizacionDetalles.reduce(function (acum, item) {

        let descuento = item.descuento;
        if (descuento == '' || isNaN(descuento) || !isNumeric(descuento)) {
            descuento = 0;
        }

        if (!isNumeric(item.precio) || !isNumeric(item.cantidad)) {
            return acum;
        }

        return acum + (parseFloat(item.precio) * parseInt(item.cantidad)) * (1 - (parseFloat(descuento) / 100));
    }, 0) : 0;
}

function getIVA() {
    var iva = 0;

    if (!hasIVA()) {
        iva = getSubtotal() * 0.21;
    }

    return iva;
}

function getIIBB() {
    let iibb = 0;

    let porcentajeIIBB = Joomla.getOptions('com_sabullvial.cotizacion').porcentajeIIBB;

    if (porcentajeIIBB > 0) {
        porcentajeIIBB = parseFloat(porcentajeIIBB);
        const subtotal = getSubtotal();

        iibb = hasIVA() ? subtotal / 1.21 : subtotal;
        iibb *= (porcentajeIIBB / 100); // 0.1%
    }

    return iibb;
}

function hasIVA() {
    return jQuery('input[name=\"jform[iva]\"]:checked').val() === '1';
}

function calcCotizacionDesgloce()
{
    const elSubtotal = jQuery('input[name=\"jform[subtotal]\"]');
    const elIva21 = jQuery('input[name=\"jform[iva_21]\"]');
    const elIibb = jQuery('input[name=\"jform[iibb]\"]');
    const elTotal = jQuery('input[name=\"jform[total]\"]');
    const elIIBB = jQuery('input[name=\"jform[iibb]\"]');

    const subtotal = getSubtotal();

    const iva = getIVA();
    const iibb = getIIBB();
    const total = subtotal + iva + iibb;

    elSubtotal.val(subtotal);
    elIibb.val(iibb);
    elIva21.val(iva);
    elTotal.val(total);
    elIIBB.val(iibb);
}

function calcCotizacionDetalle(element) {		
    var row = jQuery(element).parents('tr');
    var precio = row.find('td[data-fieldname=\"precio\"] input').val();
    var descuento = row.find('td[data-fieldname=\"descuento\"] input').val();
    var cantidad = row.find('td[data-fieldname=\"cantidad\"] input').val();
    var subtotal = row.find('td[data-fieldname=\"subtotal\"] input');

    if (descuento == '' || isNaN(descuento)) {
        descuento = 0;
    }

    if (precio >= 0 && descuento >= 0 && cantidad >= 0) {
        subtotal.val(parseFloat(precio) * parseInt(cantidad) * (1 - (parseFloat(descuento) / 100)));
        window.loadProducts();
        calcCotizacionDesgloce();
    }
}

var doc = jQuery(document);

doc.on('products-loaded', calcCotizacionDesgloce);

doc.on('ready', function() {
    var modal = jQuery('#ModalSelectCotizacionDetalle_sr-0');
    
    modal.on('show.bs.modal', function(event) {
        var modal = jQuery(this);
        var dolar = jQuery('input[name=\"jform[dolar]\"]:checked').val();
        var iva = jQuery('input[name=\"jform[iva]\"]:checked').val();
        var id_condicionventa = jQuery('select[name=\"jform[id_condicionventa]\"]').val();
    
        var url = modal.data('url') + '&filter_iva='+iva+'&filter_dolar='+dolar+'&filter_id_condicionventa='+id_condicionventa;
        modal.find('iframe').attr('src', url);
    });
    window.loadProducts();

    var porcentajeIIBB = Joomla.getOptions('com_sabullvial.cotizacion').porcentajeIIBB;
    jQuery('#jform_iibb-lbl').text('IIBB '+porcentajeIIBB+'%');
});

Joomla.submitbutton = function(task)
{
    if (task == 'cotizacion.cancel' || document.formvalidator.isValid(document.getElementById('adminForm')))
    {
        Joomla.submitform(task, document.getElementById('adminForm'));
    }
};