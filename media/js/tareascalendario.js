document.addEventListener('DOMContentLoaded', function () {
    const comOptions = Joomla.getOptions('com_sabullvial');
    const $calendar = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar($calendar, {
        themeSystem: 'bootstrap',
        initialView: 'dayGridMonth',
        locale: 'es',
        timeZone: 'America/Argentina/Buenos_Aires',
        contentHeight: 800,
        expandRows: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
        },
        eventSources: {
            url: `index.php?option=com_sabullvial&task=tareascalendario.findTareas&${comOptions.token}=1`,
            method: 'GET',
        },
        eventSourceSuccess: function (content, response) {
            return content.data.map(function (tarea) {

                let title = '';
                if (tarea.id_cotizacion > 0) {
                    title = `Cotización #${tarea.id_cotizacion}`;
                }

                if (tarea.cliente) {
                    title += ` - ${tarea.cliente}`;
                }

                if (tarea.cliente_sistema) {
                    title += ` - ${tarea.cliente_sistema}`;
                }

                return {
                    id: tarea.id,
                    title,
                    start: tarea.start_date,
                    // end: tarea.expiration_date,
                    className: getClassName({ expiration_date: tarea.expiration_date, diasParaExpiracion: comOptions.diasParaExpiracion }),
                    extendedProps: {
                        id: tarea.id,
                        content: tarea.content
                    }
                }
            });
        },
        loading: function (isLoading) {
            if (isLoading) {
                $calendar.classList.add('disabled');
                return;
            }

            $calendar.classList.remove('disabled');
            jQuery('.btn-group button').tooltip();
        },
        eventMouseEnter: function ({ el, event, jsEvent, view }) {
            if (isMobile()) {
                return;
            }

            const $el = jQuery(el);
            const hasPopover = $el.data('bs.popover');
            if (!hasPopover) {
                $el.popover({
                    trigger: 'manual',
                    html: true,
                    content: event._def.extendedProps.content,
                    title: event.title,
                    container: '#j-main-container',
                    placement: 'top',
                });
            }

            $el.popover('show');
        },
        eventMouseLeave: function ({ el, event, jsEvent, view }) {
            if (isMobile()) {
                return;
            }

            const idTarea = event._def.extendedProps.id;

            const $el = jQuery(el);

            const $tip = jQuery($el.data('popover').$tip);

            $tip.on('mouseenter', () => {
                $tip.find('.hasTooltip').tooltip();
                showTipDelay($el, idTarea);
            });

            $tip.on('mouseleave', () => {
                hideTipDelay($el, idTarea);
            });

            hideTipDelay($el, idTarea);
        },
        eventClick: function ({ el, event, jsEvent, view }) {
            if (!isMobile()) {
                return;
            }

            const $eventModal = jQuery('#eventModal');

            $eventModal.addClass('modal-fullscreen');
            $eventModal.find('.modal-header h3').text('Tarea');
            $eventModal.find('.modal-body').html(`<div class="row-fluid mb-sm2"><b>${event.title}</b></div>${event._def.extendedProps.content}`);

            $eventModal.modal('show');
        }
    });

    calendar.render();
});

let tipsHide = [];
function hideTipDelay($el, idTarea) {
    if (!tipsHide[idTarea]) {
        tipsHide[idTarea] = true;
    }

    setTimeout(() => {
        if (!tipsHide[idTarea]) {
            return;
        }
        $el.popover('hide');
    }, 300);
}

function showTipDelay($el, idTarea) {
    tipsHide[idTarea] = false;
}

const isMobile = () => /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

function getClassName({ expiration_date, diasParaExpiracion }) {
    const expirationDate = new Date(expiration_date.split(" ")[0]);
    const today = new Date();
    today.setHours(0, 0, 0, 0); // establece la hora a medianoche para ignorar la hora

    const differenceInTime = expirationDate.getTime() - today.getTime();
    const differenceInDays = parseInt(differenceInTime / (1000 * 3600 * 24));

    const isExpired = differenceInDays <= 0;
    const isNearToExpire = differenceInDays <= diasParaExpiracion;

    return isExpired ? 'event-important' : isNearToExpire ? 'event-warning' : 'event-info';
}