<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
?>

<div class="container-fluid">
	<div class="row-fluid">
		<div class="control-group span6">
			<div class="controls">
                <label id="batch-upload-images-lbl" 
                    for="batch-upload-images-images" 
                    class="modalTooltip" 
                    data-placement="auto-dir top-left" 
                    data-original-title="<strong>Asignar imágenes</strong><br />Examine las imágenes a agregar en los productos seleccionados.">
                    Asignar imágenes
                </label>
                <!-- input file only images -->
                <input type="file" id="batch-upload-images-images" name="batch[upload_images][images][]" accept="image/*" /></br>
                <input type="file" id="batch-upload-images-images" name="batch[upload_images][images][]" accept="image/*" /></br>
                <input type="file" id="batch-upload-images-images" name="batch[upload_images][images][]" accept="image/*" /></br>
                <input type="file" id="batch-upload-images-images" name="batch[upload_images][images][]" accept="image/*" /></br>
                <input type="file" id="batch-upload-images-images" name="batch[upload_images][images][]" accept="image/*" />

                <input type="hidden" id="batch-upload-images-action" name="batch[upload_images][action]" value="uploadImages" />
                <p><small>Al subir las imágenes se sobreescribirán las imágenes existentes y se agregaran las nuevas.</small></p>
			</div>
		</div>
	</div>
</div>