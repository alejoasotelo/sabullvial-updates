function reload(element, subtask) {
    Joomla.loadingLayer('show');
    jQuery('input[name=task]').val('tarea.reload');
    jQuery('input[name=subtask]').val(subtask);
    Joomla.submitform('tarea.reload', element.form);
}

function cotizacionHasChanged(element) {
    let elVal = jQuery(element).val();
    elVal = elVal == '' || elVal == 0 ? 0 : elVal;
    const assignedIdCotizacion = Joomla.getOptions('com_sabullvial.tarea').assignedIdCotizacion;
    let currentIdCotizacion = assignedIdCotizacion;
    currentIdCotizacion = currentIdCotizacion == '' || currentIdCotizacion == 0 ? 0 : currentIdCotizacion;

    if (elVal == currentIdCotizacion) {
        return;
    }

    reload(element, 'tarea.changeCotizacion');
}

const changeEstadoCotizacion = async (element) => {
    const $el = jQuery(element);

    addButtonSaveEstadoCotizacion($el);
};

const addButtonSaveEstadoCotizacion = ($element) => {
    const { allowEditEstadoCotizacion } = Joomla.getOptions('com_sabullvial.tarea');

    if (!allowEditEstadoCotizacion) {
        return;
    }

    const buttonSaveEstadoCotizacion = `
        <button type="button" class="btn btn-small btn-save-estadocotizacion"><span class="icon-save" aria-hidden="true"></span> ${Joomla.Text._('JTOOLBAR_APPLY')}</button>
    `;

    const $parent = $element.parent();

    if ($parent.find('.btn-save-estadocotizacion').length) {
        const $btnSave = $parent.find('.btn-save-estadocotizacion');
        $btnSave.show();
        return $btnSave;
    }

    const $btnSave = jQuery(buttonSaveEstadoCotizacion);
    $parent.append($btnSave);

    $btnSave.on('click', async () => {
        const { assignedIdCotizacion: idCotizacion } = Joomla.getOptions('com_sabullvial.tarea');

        Joomla.loadingLayer('show');

        const newIdEstadoCotizacion = $element.val();

        if (!idCotizacion || !newIdEstadoCotizacion) {
            Joomla.loadingLayer('hide');
            return;
        }

        const response = await saveEstadoCotizacion(idCotizacion, newIdEstadoCotizacion);

        if (!response || !response.success) {
            Joomla.renderMessages(response.messages);
            Joomla.loadingLayer('hide');
            return;
        }

        Joomla.loadingLayer('hide');

        Joomla.renderMessages(response.messages);

        addEstadoToHistorico(response.data);

        $btnSave.hide();
    });

    return $btnSave;
}

/**
 * Envia una petición ajax para cambiar el estado de la cotización
 * 
 * @param int idCotizacion 
 * @param int idEstadoCotizacion 
 * @return boolean|object
 */
const saveEstadoCotizacion = async (idCotizacion, idEstadoCotizacion) => {
    const { url, token } = Joomla.getOptions('com_sabullvial.tarea');

    const urlAjax = `${url}index.php?option=com_sabullvial&task=cotizacion.changeEstadoCotizacionAjax&id=${idCotizacion}&${token}=1`;

    try {
        const response = await jQuery.post(urlAjax, {
            id_estadocotizacion: idEstadoCotizacion
        });

        return response;
    } catch (error) {
        console.error(error);

        return false;
    }
}

const addEstadoToHistorico = ({
    estado,
    bg_color,
    color,
    created,
    created_by_alias
}) => {
    const $tableHistorico = jQuery('#jform_cotizacionhistorico');

    if (!$tableHistorico.length) {
        return;
    }

    const row = `
        <tr>
            <td><span class="label" style="background-color: ${bg_color}; color: ${color}">${estado}</span></td>
            <td>${created}</td>
            <td>${created_by_alias}</td>
        </tr>
    `;

    $tableHistorico.find('tbody').prepend(row);
}