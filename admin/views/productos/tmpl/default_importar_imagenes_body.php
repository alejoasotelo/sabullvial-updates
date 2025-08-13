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
                <label id="import-images-lbl" 
                    for="import-images-csv" 
                    class="modalTooltip" 
                    data-placement="auto-dir top-left" 
                    data-original-title="<strong>Importar imágenes</strong><br />Examine un CSV con las imágenes para importar en los productos.">
                    Seleccione el archivo CSV con las imágenes
                </label>
                <!-- input file only images -->
                <input type="file" id="import-images-csv" name="import_images_csv" accept=".csv" />

                <p><small>Al subir las imágenes se sobreescribirán las imágenes existentes y se agregaran las nuevas.</small></p>
                <p><small>La primer fila del archivo CSV tiene que ser: codigo_articulo imagen1 imagen2 imagen3 imagen4 imagen5 imagen6 imagen7 imagen8 imagen9 imagen10</small></p>
                <p><small>Cómo minimo la primer fila tiene que ser: codigo_articulo imagen1</small></p>
			</div>
		</div>
	</div>
</div>