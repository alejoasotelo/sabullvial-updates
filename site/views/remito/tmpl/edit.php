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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidator');

$urlAction = JRoute::_('index.php?option=com_sabullvial&view=remito&layout=edit&id=' . $this->item->N_REMITO);
$wasEntregado = (bool)$this->item->entregado;

$doc = Factory::getDocument();

if (!$wasEntregado) {
    $doc->addScriptDeclaration("

        const setDisabled = function (disable) {
            const items = document.querySelectorAll('.uk-form-file-container input, .uk-form-file-container button, #jform_upload');
            items.forEach(function (item) {
                item.disabled = disable;
            });
        }

        const uploadForm = function() {
            const fileInput = document.getElementById('jform_image');
            const uploadButton = document.getElementById('jform_upload');
            const progressBar = document.getElementById('js-progressbar');

            const dropzone = new Dropzone('#adminForm', {
                url: '$urlAction', 
                autoProcessQueue: false, 
                maxFiles: 1, 
                acceptedFiles: 'image/*', 
                addRemoveLinks: true, 
                paramName: 'jform[image]', 
                init: function () {
                    const dz = this;

                    uploadButton.addEventListener('click', function () {

                        if (!document.formvalidator.isValid(document.getElementById('adminForm'))) {
                            UIkit.notification({message: 'Por favor revisa los campos del formulario', status: 'warning'});
                            return;
                        }

                        document.getElementById('adminForm').querySelector('input[name=task]').value = 'remito.marcarComoEntregado';

                        if (dz.getAcceptedFiles().length > 0) {
                            progressBar.hidden = false; 
                            setDisabled(true);
                            dz.processQueue(); 
                        } else {
                            UIkit.notification({message: 'Por favor selecciona un archivo', status: 'warning'});
                        }
                    });

                    dz.on('uploadprogress', function (file, progress) {
                        progressBar.value = progress; 
                    });

                    dz.on('success', function (file, response) {
                        progressBar.value = 100; 

                        UIkit.notification({message: 'Remito marcado como entregado', status: 'success'});
                        
                        setTimeout(function () {
                            window.location.reload();
                        }, 2000);
                    });

                    dz.on('error', function (file, errorMessage) {
                        UIkit.notification({message: 'Error al subir archivo', status: 'danger'});
                        
                        progressBar.value = 0;
                        progressBar.hidden = true;
                        setDisabled(false);
                        
                        dz.removeAllFiles();
                        UIkit.notification({message: 'Por favor intenta de nuevo', status: 'warning'});
                    });
                    
                    fileInput.addEventListener('change', function () {
                        dz.removeAllFiles(); 
                        if (fileInput.files.length > 0) {
                            const file = fileInput.files[0];
                            dz.addFile(file); 
                            uploadButton.disabled = false;
                        } else {
                            uploadButton.disabled = true;
                        }
                    });
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            uploadForm();
        });
    ");

    $doc->addStyleDeclaration("
        #jform_captcha > div {
            margin: 0 auto;
        }

        .uk-progress::-webkit-progress-value, .uk-progress::-moz-progress-bar {
            background-color:#3dc372 !important;
        }
    ");
}
?>
<div class="uk-panel">
    <?php if (!$wasEntregado): ?>
    
        <h1 class="uk-heading-small uk-text-center uk-margin-large">
            Remito <?php echo $this->item->N_REMITO; ?>
        </h1>

        <p class="uk-text-center uk-text-lead">Desea marcar el remito del cliente <span class="uk-text-bold"><?php echo $this->item->getCliente(); ?></span> como <span class="uk-text-success">entregado</span>?</p>

        <form class="form-validate uk-text-center uk-margin-medium uk-hidden"  action="<?php echo JRoute::_('index.php?option=com_sabullvial&view=remito&layout=edit&id=' . $this->item->N_REMITO); ?>" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data">
            
            <?php if ($this->captchaEnabled) : ?>
                <div class="uk-width-1-1 uk-margin-auto uk-margin-bottom">
                    <?php echo $this->form->renderField('captcha', null, null, ['hiddenLabel' => true]); ?>
                </div>
            <?php endif; ?>
            
            <input type="hidden" name="task" value="">
            <?php echo $this->form->renderField('id'); ?>
            <?php echo JHtml::_('form.token'); ?>
        </form>

        <div class="uk-text-center uk-margin-medium">
        
            <div class="uk-width-1-1 uk-margin-auto uk-margin-medium-bottom"">
                <label class="uk-form-label uk-display-block uk-margin-small-bottom"><?php echo Text::_('COM_SABULLVIAL_REMITO_FIELD_IMAGEN_LABEL'); ?></label>
                <div class="uk-form-file-container uk-form-controls">
                    <div uk-form-custom="target: true">
                        <input type="file" name="jform[image]" id="jform_image" accept="image/*" aria-invalid="false" aria-label="Custom controls">
                        <input class="uk-input uk-form-width-medium" type="text" placeholder="<?php echo Text::_('COM_SABULLVIAL_REMITO_FIELD_IMAGEN_DESC'); ?>" aria-label="Custom controls" disabled>
                        <button class="uk-button uk-button-default" type="button" tabindex="-1">
                            <span class="uk-margin-small-right" uk-icon="icon: image"></span> Seleccionar
                        </button>
                    </div>
                </div>
            </div>

            <progress id="js-progressbar" class="uk-progress uk-width-1-2 uk-width-1-3@m uk-margin-auto uk-margin-medium-top" value="0" max="100" hidden></progress>
                
            <button type="button" id="jform_upload" class="uk-button uk-button-primary uk-margin" disabled>
                <span class="icon-ok"></span> <span class="btn-label">Marcar como entregado</span>
            </button>

        </div>
    <?php else: ?>
        
        <div class="uk-margin uk-text-center">
            <span uk-icon="icon: check; ratio: 4" class="uk-text-success"></span>
        </div>
    
        <h1 class="uk-heading-small uk-text-center uk-margin">
            Remito <?php echo $this->item->N_REMITO; ?> <span class="uk-text-success">entregado</span>
        </h1>

        <p class="uk-text-center uk-text-lead">El remito del cliente <span class="uk-text-bold"><?php echo $this->item->getCliente(); ?></span> ya ha sido marcado como <span class="uk-text-success">entregado<span>.</p>
    <?php endif; ?>
</div>