<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
use Joomla\CMS\Language\Text;

JHtml::_('behavior.core');

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
?>
<h1><?php echo Text::_('COM_SABULLVIAL_REMITOS_OPTIMIZE_IMAGES'); ?></h1>
<div id="optimize-images-progress">
    <button id="start-optimize" class="btn btn-success">Iniciar optimización</button>
    <button id="reset-optimize" class="btn btn-warning">Reiniciar desde 0</button>
    <button id="pause-optimize" class="btn btn-secondary">Pausar</button>
    <div id="progress-info"></div>
    <table class="table table-striped" id="images-table" style="margin-top:20px; display:none;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Remito</th>
                <th>Ruta</th>
                <th>Ruta Bullvial</th>
                <th>Existe</th>
                <th>Peso (MB)</th>
                <th>Dimensiones</th>
                <th>Formato</th>
                <th>Peso redimensionada (MB)</th>
                <th>Nuevas dimensiones</th>
                <th>Redimensionada</th>
                <th>Optimizada</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
<script>
(function(){
    var btnStart = document.getElementById('start-optimize');
    var btnReset = document.getElementById('reset-optimize');
    var btnPause = document.getElementById('pause-optimize');
    var info = document.getElementById('progress-info');
    var table = document.getElementById('images-table');
    var tbody = table.querySelector('tbody');
    var running = false;
    var paused = false;
    var total = 0;
    var processed = 0;
    function processChunk() {
        if (paused) {
            info.innerHTML = 'Pausado.';
            running = false;
            return;
        }
        running = true;
        info.innerHTML = 'Procesando...';
        fetch('index.php?option=com_sabullvial&task=remitos.optimizarImagenesChunk', {
            method: 'POST',
            headers: {'X-CSRF-Token': Joomla.getOptions('csrf.token') || ''}
        })
        .then(r => r.json())
        .then(responseJson => {
            const data = responseJson.data || {};
            if (!data || !data.results) {
                info.innerHTML = 'Error al procesar el chunk.';
                running = false;
                return;
            }

            if (data.results && data.results.length) {
                table.style.display = '';
                data.results.forEach(function(img){
                    var tr = document.createElement('tr');
                    tr.innerHTML = '<td>'+img.id+'</td>'+
                        '<td>'+img.numero_remito+'</td>'+
                        '<td><a href="'+img.image+'" target="_blank">'+img.image+'</a></td>'+
                        '<td><a href="https://ventas.bull-vial.com.ar/'+img.image+'" target="_blank">bull-vial</a></td>'+
                        '<td>'+(img.exists ? 'Sí' : 'No')+'</td>'+
                        '<td>'+(img.size !== null ? img.size+' MB' : '-')+'</td>'+
                        '<td>'+(img.dimensions || '-')+'</td>'+
                        '<td>'+(img.format || '-')+'</td>'+
                        '<td>'+(img.estimated_resized !== null ? img.estimated_resized+' MB' : '-')+'</td>'+
                        '<td>'+(img.new_dimensions || '-')+'</td>'+
                        '<td>'+(img.resized ? '<span style="color:green">Sí</span>' : '<span style="color:#888">No</span>')+'</td>'+
                        '<td>'+(img.optimizada == 1 ? '<span style="color:green">1</span>' : '<span style="color:#888">0</span>')+'</td>';
                    tbody.appendChild(tr);
                });
                processed += data.results.length;
                total = data.total;
                info.innerHTML = 'Procesados: '+processed+' de '+total+' imágenes. Índice actual: ' + data.chunkIndex;
                if (data.hasMore && !paused) {
                    setTimeout(processChunk, 500);
                } else if (paused) {
                    info.innerHTML += '<br>Pausado en el índice: ' + (typeof data.chunkIndex !== 'undefined' ? data.chunkIndex : '...');
                    running = false;
                } else {
                    info.innerHTML += '<br>¡Proceso finalizado!';
                    if (data.resetIndex) {
                        info.innerHTML += '<br>Índice reiniciado automáticamente.';
                    }
                    running = false;
                }
            } else {
                info.innerHTML = 'No hay imágenes para procesar.';
                running = false;
            }
        })
        .catch(function(e){
            info.innerHTML = 'Error en la petición.';
            running = false;
            console.error('Error al procesar el chunk:', e);
        });
    }
    btnStart.onclick = function(){
        if (running) return;
        paused = false;
        tbody.innerHTML = '';
        processed = 0;
        processChunk();
    };
    btnPause.onclick = function(){
        paused = true;
        info.innerHTML += '<br>Pausado por el usuario.';
    };
    btnReset.onclick = function(){
        if (running) return;
        info.innerHTML = 'Reiniciando...';
        fetch('index.php?option=com_sabullvial&task=remitos.resetOptimiceImagesChunkIndex', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'jform[remitos_optimizar_imagenes_index]=0&' + Joomla.getOptions('csrf.token') + '=1'
        }).then(function(){
            info.innerHTML = 'Índice reiniciado. Listo para comenzar.';
            tbody.innerHTML = '';
            processed = 0;
        });
    };
})();
</script>