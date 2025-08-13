<?php
/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidator');
JHtml::_('behavior.keepalive');
JHtml::_('formbehavior.chosen', 'select');

$app = JFactory::getApplication();
$input = $app->input;

$doc = JFactory::getDocument();

$doc->addScriptDeclaration('
    const options = Joomla.getOptions("com_sabullvial");

	Joomla.submitbutton = function(task)
	{
		if (task == "hojaderuta.cancel" || document.formvalidator.isValid(document.getElementById("adminForm")))
		{
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};

    const onDeleteImagenRemito = async function(element) {

        if (!confirm(Joomla.JText._("COM_SABULLVIAL_HOJA_DE_RUTA_CONFIRM_DELETE_IMAGE"))) {
            return false;
        }

        const idRemito = element.closest("tr").querySelector(\'input[name$="[numero_remito]"]\').value.trim();

        const response = await deleteImagenRemitoAsync(idRemito);

        if (!response.success) {
            Joomla.renderMessages(response.messages);
            return false;
        }

        Joomla.renderMessages(response.messages);

        const $tdChildren = jQuery(element.closest("td")).children().first();

        $tdChildren.fadeOut(500, function() {
            $tdChildren.text("");
        });

        return true;
    };

    const deleteImagenRemitoAsync = async function (idRemito) {
        const url = `${options.url}index.php?option=com_sabullvial&task=remito.deleteImagen&id=${idRemito}&${options.token}=1`;

        let response = false;

        try {
            response = await jQuery.post(url);
        } catch (error) {
            console.error(error);
        }

        return response;
    }

    let fireChangeEstadoRemitoTrigger = false;

    const changeEstadoRemito = function (element) {
        if (fireChangeEstadoRemitoTrigger) {
            return;
        }

        const idRemito = element.closest("tr").querySelector(\'input[name$="[numero_remito]"]\').value.trim();
        const idEstado = element.value;

        const $el = jQuery(element);
        const $tbody = $el.closest("tbody");
        const $link = $tbody.find(".change-estado-remito");
        const $linkClose = $tbody.find(".change-estado-remito-cerrar");

        if ($link.length) {
            $link.remove();
        }

        if ($linkClose.length) {
            $linkClose.remove();
        }

        if (idEstado === "") {
            return;
        }

        const $linkChangeAll = jQuery("<a href=\"#\" class=\"change-estado-remito small\">Cambiar a todos</a>");
        const $linkCloseChangeAll = jQuery("<a href=\"#\" class=\"change-estado-remito-cerrar small muted\">Cerrar</a>");

        $linkChangeAll.on("click", async function (event) {
            event.preventDefault();

            const $estadosRemitos = element.closest("tbody").querySelectorAll(\'select[name$="[id_estadoremito]"]\');

            for (let i = 0; i < $estadosRemitos.length; i++) {
                const $estadoRemito = $estadosRemitos[i];

                if ($estadoRemito.value === idEstado) {
                    continue;
                }

                $estadoRemito.value = idEstado;

                // refresh ui chosen
                fireChangeEstadoRemitoTrigger = true;
                jQuery($estadoRemito).trigger("liszt:updated").trigger("change");
                fireChangeEstadoRemitoTrigger = false;
            }

            $linkChangeAll.remove();
            $linkCloseChangeAll.remove();
        });

        $linkCloseChangeAll.on("click", function (event) {
            event.preventDefault();

            $linkChangeAll.remove();
            $linkCloseChangeAll.remove();
        });

        const $parent = $el.closest("td");
        $parent.append($linkChangeAll);
        $parent.append($linkCloseChangeAll);
    };
');

$doc->addStyleDeclaration('
    .change-estado-remito,
    .change-estado-remito-cerrar {
	    display: inline-block;
        margin-top: 6px;
        cursor: pointer;

    }
    .change-estado-remito {
        color: #007bff;
    }

    .change-estado-remito-cerrar {
        margin-left: 8px;
    }
');

SabullvialHelper::loadEstadosRemitoStylesheet();

$fieldsNotToRender = [
    'publish_up',
    'publish_down',
    'created', 'created_time',
    'created_by', 'created_user_id',
    'created_by_alias',
    'modified', 'modified_time',
    'modified_by', 'modified_user_id',
    'version', 'version_note',
    'hits',
    'id',
    'rules',
    'cliente'
];

$isUserSuperAdministrador = SabullvialHelper::isUserSuperAdministrador();
?>
<form action="<?php echo JRoute::_('index.php?option=com_sabullvial&layout=edit&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">
    <div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', ['active' => 'general']); ?>

        <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'general', JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_DETAILS')); ?>
        <div class="row-fluid">
            <div class="span12">
                <?php
                foreach ($this->form->getFieldset() as $field) {
                    if (in_array($field->fieldname, $fieldsNotToRender)) {
                        continue;
                    }

                    echo $field->renderField();
                }
                ?>
            </div>
        </div>
        <?php echo JHtml::_('bootstrap.endTab'); ?>

        <?php if ($isUserSuperAdministrador): ?>
            <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('COM_SABULLVIAL_FIELDSET_PUBLISHING')); ?>
                <div class="row-fluid form-horizontal-desktop">
                    <div class="span6">
                        <?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
                        <div class="control-group">
                            <div class="control-label"><?php echo $this->form->getLabel('version_note'); ?></div>
                            <div class="controls"><?php echo $this->form->getInput('version_note'); ?></div>
                        </div>
                    </div>
                </div>
            <?php echo JHtml::_('bootstrap.endTab'); ?>
        <?php endif; ?>

        <?php echo JHtml::_('bootstrap.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="hojaderuta.edit" />
	<input type="hidden" name="return" value="<?php echo $input->get('return', null, 'BASE64'); ?>" />
    <?php echo JHtml::_('form.token'); ?>
</form>