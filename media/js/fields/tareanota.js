jQuery(document).ready(function ($) {
    const options = Joomla.getOptions('com_sabullvial.tareanota');

    const $wrapper = $('#' + options.idWrapper);
    const $textarea = $('#' + options.idTextarea);
    const $btnAdd = $('#' + options.idBtnAdd);

    const saveChat = async function (idTarea, body) {
        const url = `${options.url}index.php?option=com_sabullvial&task=tarea.saveNota&id=${idTarea}&${options.token}=1`;

        const data = { body };

        let response = false;

        try {
            response = await $.post(url, data);
        } catch (error) {
            console.error(error);
        }

        return response;
    }

    const deleteNota = async function (idNota) {
        const url = `${options.url}index.php?option=com_sabullvial&task=tareanotas.deleteAjax&cid=${idNota}&${options.token}=1`;

        let response = false;

        try {
            response = await $.post(url);
        } catch (error) {
            console.error(error);
        }

        return response;
    }

    const lockChat = function () {
        $textarea.prop('disabled', true);
        $btnAdd.prop('disabled', true);
    }

    const unlockChat = function () {
        $textarea.prop('disabled', false);
        $btnAdd.prop('disabled', false);
    }

    const addChatBubble = async function () {

        const body = $textarea.val();

        if (!body) {
            return;
        }

        lockChat();

        const response = await saveChat(options.itemId, body);

        if (!response.success) {
            unlockChat();
            return false;
        }

        const $bubbles = $wrapper.find('.tareanota-notas-chat-bubbles');

        // replace all \n with <br>
        const text = response.data.body.replace(/\n/g, '<br>');

        let tpl = options.chatBubbleTemplate
            .replace('{{id}}', response.data.id)
            .replace('{{author}}', response.data.created_by_alias)
            .replace('{{text}}', text)
            .replace('{{created}}', response.data.createdFormatted);

        const $newMessage = $(tpl);
        $newMessage.hide();

        $bubbles.prepend($newMessage);

        $newMessage.slideDown('slow');

        $textarea.val('');

        unlockChat();

        return true;
    }

    const deleteChatBubble = async function (idNota) {

        if (!confirm('¿Está seguro de eliminar esta nota?')) {
            return false;
        }

        const response = await deleteNota(idNota);

        if (!response.success) {
            Joomla.renderMessages(response.messages);
            return false;
        }

        const $bubble = $(`#tareanota-nota-${idNota}`);

        $bubble.slideUp('slow', function () {
            $bubble.remove();
        });

        Joomla.renderMessages(response.messages);
        return true;
    }

    $btnAdd.on('click', function (e) {
        e.preventDefault();
        addChatBubble();
    });

    $textarea.on('keydown', function (e) {
        if (e.keyCode === 13 && !e.shiftKey) {
            e.preventDefault();
            addChatBubble();
        }
    });

    $(document).on('click', '.chat-bubble-btn-delete', function () {
        const $button = $(this);
        const $chatBubble = $button.closest('.chat-bubble');
        const idNota = $chatBubble.attr('id').replace('tareanota-nota-', '');

        if (!idNota) {
            return;
        }

        deleteChatBubble(idNota);
    });
});