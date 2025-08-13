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

JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('bootstrap.popover');

$canDo = JHelperContent::getActions('com_sabullvial');

$porcentajeDia = (float)SabullvialHelper::getConfig('PORC_DIA');
$cotizacionDolar = (float)SabullvialHelper::getConfig('COTI_DOL');

$conIva = (bool)$this->state->get('filter.iva', 1);
$conDolar = (bool)$this->state->get('filter.dolar', 0);

$filterCondicionVenta = (int)$this->state->get('filter.id_condicionventa', 49);
$condicionVenta = JTable::getInstance('SitCondicionesVenta', 'SabullvialTable');
$condicionVenta->loadByCondicionVenta($filterCondicionVenta);

$kInteres = (float)$condicionVenta->DIAS * $porcentajeDia / 100;

$vendedor = SabullvialHelper::getVendedor();

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

/** @var Joomla\CMS\Document\Document $doc */
$doc = JFactory::getDocument();

$doc->addScriptDeclaration("
    jQuery(document).ready(function() {
        jQuery('#table tbody tr td:not(:has(input))').off('click').click(function(e) {
            var el = jQuery(this);
            var \$input = el.parent().find('input');

            \$input.prop('checked', !\$input.prop('checked'));
            Joomla.isChecked(\$input.prop('checked'));
        });
        jQuery('.js-stools-container-filters').removeClass('hidden-phone hidden-tablet');
        jQuery('.js-stools-btn-filter').parent().removeClass('hidden-phone');

    });

    function showCarousel(data, event) {
        event.preventDefault();

        var body = `<br/>
            <div class='splide' role='group' aria-label='Splide Basic HTML Example'>
            <div class='splide__track'>
                    <ul class='splide__list'>`;

                    Object.entries(data).forEach(([key, value]) => {
                        body += '<li class=\'splide__slide\'><div class=\'splide__slide__container\'><img src=\'" . JUri::root() ."' + value.path + '\'  width=\'320\' height=\'320\' /></div></li>';
                    });

        body += `</ul>
            </div>
            <div class=\'my-carousel-progress\'>
            <div class=\'my-carousel-progress-bar\'></div>
          </div>
            </div><br/>
        `;

        var modalProductoCarousel = jQuery('#modalProductoCarousel');

        modalProductoCarousel.find('.modal-body').html(body);
        
        let modalSplide = new Splide( '.splide', {
            type: 'loop',
            autoHeight: true,
            gap: '12px',  
            focus    : 'center',			
        });

        modalProductoCarousel.on('hidden.bs.modal', function (e) {
            modalSplide.destroy('completely');
        }).on('shown.bs.modal', function (e) {
            modalSplide.mount();
        });

        modalProductoCarousel.modal('show');
    }
");

$doc->addStyleDeclaration("
.fade.right.in{
    opacity: 1;
}

@media (max-width: 667px) {
    .js-stools-field-filter {
        width: 48%;
        margin: 0px 0px 8px 0px !important;
    }
    .js-stools-field-filter > div, .js-stools-field-filter > select {
        width: 100%;
    }
}

#modalProductoCarousel {
    max-width: calc(100vw - 24px);
}

#modalProductoCarousel .splide {
    padding-left: 12px;
    padding-right: 12px;
}

.splide__pagination__page.is-active{
    background: #2384d3;
}
  .splide__slide__container{
    text-align: center;
  }
");
?>

<form action="index.php?option=com_sabullvial&view=productos" method="post" id="adminForm" name="adminForm" enctype="multipart/form-data">
    <div id="j-sidebar-container" class="span2">
        <?php echo JHtmlSidebar::render(); ?>
    </div>
    <div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="span12">
                <?php echo JLayoutHelper::render('joomla.searchtools.default', ['view' => $this]); ?>
            </div>
        </div>
        <table id="table" class="table table-striped table-hover">
            <thead>
                <tr>
                    <th width="1%" class="center">
                        <?php echo JHtml::_('grid.checkall'); ?>
                    </th>
                    <th width="1%" class="center">
                        <?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_IMAGEN'); ?>
                    </th>
                    <th width="1%" class="nowrap center">					
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_CODIGO_ART', 'codigo_sap', $listDirn, $listOrder); ?>
                    </th>
                    <th style="min-width:100px" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_NOMBRE', 'nombre', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap hidden-phone">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_MARCA', 'marca', $listDirn, $listOrder); ?>
                    </th>
                    <th width="10%" class="nowrap">
                        <?php if ($conIva): ?>
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_PRECIO_CON_IVA', 'precio', $listDirn, $listOrder); ?>
                        <?php else: ?>
                            <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_PRECIO_SIN_IVA', 'precio', $listDirn, $listOrder); ?>
                        <?php endif; ?>
                    </th>
                    <th width="1%" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_CODIGO_LISTA', 'pp.COD_LISTA', $listDirn, $listOrder); ?>
                    </th>
                    <th width="1%" class="nowrap">
                        <?php echo JHtml::_('searchtools.sort', 'COM_SABULLVIAL_PRODUCTOS_STOCK', 'stock', $listDirn, $listOrder); ?>
                    </th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="8">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $row):
                        $action = $row->id_productoimagen > 0 ? 'edit&id='.$row->id_productoimagen : 'add&id_producto=' . $row->id;
                        $return = base64_encode(JUri::getInstance()->toString());
                        $link = JRoute::_('index.php?option=com_sabullvial&task=productoimagen.'. $action . '&return=' . $return);

                        $row->precio += (float)$row->precio * $kInteres;

                        if ($conDolar) {
                            $row->precio /= $cotizacionDolar;
                        }

                        if ($conIva) {
                            $row->precio *= (1 + (SabullvialHelper::IVA_21 / 100));
                        }

                        $precio = JText::_($conDolar ? 'COM_SABULLVIAL_DOLAR_SIMBOLO' : 'COM_SABULLVIAL_PESO_SIMBOLO') . number_format($row->precio, 2, ',', '.');
                        ?>
                        <tr>
                            <td class="center">
                                <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                            </td>
                            <td class="center img-container">
                                <?php if (count($row->images)): ?>
                                    <?php
                                        $image = array_values($row->images)[0];
                                        $src = JUri::root() . $image['path'];
                                    ?>
                                    <a href="#" onclick="showCarousel(<?php echo htmlspecialchars(json_encode($row->images)); ?>, event)">
                                        <img src="<?php echo $src; ?>" width="38" height="38" class="img-producto" loading="lazy" />
                                    </a>
                                <?php endif; ?>
                            </td>
                            <td class="center">
                                <?php echo $row->codigo_sap; ?>
                                <div class="visible-phone small left muted">
                                    <?php echo $row->marca; ?><br/>
                                    <?php /*<b class="muted"><?php echo $precio . ($conIva ? ' c/IVA' : ' s/IVA'); ?></b>*/ ?>
                                </div>
                            </td>
                            <td>
                                <a href="<?php echo $link; ?>" class="hasTooltip" data-toggle="tooltip" data-original-title="<?php echo JText::_('COM_SABULLVIAL_PRODUCTOS_SUBIR_EDITAR_IMAGEN'); ?>"><?php echo $this->escape($row->nombre); ?></a>
                                
                                <?php /*<div class="visible-phone small left">
                                    <span class="muted">Stock:
                                        <?php if (!$vendedor->get('ver.stockReal')): ?>
                                            <?php echo $row->stock > 10 ? '10+' : $row->stock; ?>
                                        <?php else: ?>
                                            <?php echo $row->stock; ?>
                                        <?php endif; ?>
                                    </span>
                                </div>*/ ?>
                            </td>
                            <td class="small hidden-phone">
                                <?php echo $row->marca; ?>
                            </td>
                            <td class="nowrap small" data-precio="<?php echo $row->precio; ?>">
                                <?php echo $precio; ?>
                            </td>
                            <td class="nowrap small">
                                <?php echo JText::sprintf('COM_SABULLVIAL_FIELD_CODIGO_LISTA_TEXT', $row->codigo_lista); ?>
                            </td>
                            <td class="center">
                                <?php if (!$vendedor->get('ver.stockReal')): ?>
                                    <?php
                                        $stock = $row->stock > 10 ? '10+' : (int)$row->stock;
                                        $stockDeposito1 = $row->stock_deposito_1 > 10 ? '10+' : (int)$row->stock_deposito_1;
                                        $stockDeposito2 = $row->stock_deposito_2 > 10 ? '10+' : (int)$row->stock_deposito_2;
                                        $stockDeposito3 = $row->stock_deposito_3 > 10 ? '10+' : (int)$row->stock_deposito_3;
                                        $popoverTitle = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_TITLE', $stock);
                                        $popoverContent = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_1', $stockDeposito1);
                                        $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_2', $stockDeposito2);
                                        $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_3', $stockDeposito3);
                                    ?>
                                <?php else: ?>
                                    <?php
                                        $stock = (int)$row->stock;
                                        $popoverTitle = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_TITLE', $stock);
                                        $popoverContent = JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_1', (int)$row->stock_deposito_1);
                                        $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_2', (int)$row->stock_deposito_2);
                                        $popoverContent .= JText::sprintf('COM_SABULLVIAL_PUNTO_DE_VENTA_POPOVER_STOCK_CONTENT_DEPOSITO_3', (int)$row->stock_deposito_3);
                                    ?>
                                <?php endif; ?>
                                <div class="hasPopover" title="<?php echo htmlspecialchars($popoverTitle); ?>" data-content="<?php echo htmlspecialchars($popoverContent); ?>" data-placement="left" data-trigger="hover focus click" data-html="true" data-container="container" data-toggle="popover">
                                    <?php echo $stock; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php
        echo JHtml::_(
            'bootstrap.renderModal',
            'modalProductoCarousel',
            [
                'title'       => 'Galería de Imágenes',
                'modalWidth'  => '50',
                'modalHeight' => '800',
                'height' => '800',
                'closeButton' => true,
                'footer' => $this->loadTemplate('carousel_footer'),
            ],
            ''
        );
?>
                                    
    <?php
        echo JHtml::_(
            'bootstrap.renderModal',
            'modalSubirImagenes',
            [
                'title'  => JText::_('COM_SABULLVIAL_PRODUCTOS_SUBIR_IMAGENES_TITLE'),
                'footer' => $this->loadTemplate('subir_imagenes_footer'),
            ],
            $this->loadTemplate('subir_imagenes_body')
        );

        $doc->addScriptDeclaration("
            
            jQuery(document).ready(function() {
                
                var modalSubirImagenes = jQuery('#modalSubirImagenes');
                modalSubirImagenes.on('shown.bs.modal', function (e) {
                    document.getElementById('batch-upload-images-action').value = 'uploadImages';
                    modalSubirImagenes.find('button:disabled').removeAttr('disabled');
                }).on('hidden.bs.modal', function (e) {
                    document.getElementById('batch-upload-images-action').value='';
                    document.getElementById('batch-upload-images-images').value=null;
                });
            });
        ");
    ?>
                                    
    <?php
        echo JHtml::_(
            'bootstrap.renderModal',
            'modalImportarImagenes',
            [
                'title'  => JText::_('COM_SABULLVIAL_PRODUCTOS_IMPORTAR_IMAGENES_TITLE'),
                'footer' => $this->loadTemplate('importar_imagenes_footer'),
            ],
            $this->loadTemplate('importar_imagenes_body')
        );

        $doc->addScriptDeclaration("
            
            jQuery(document).ready(function() {
                
                var modalImportarImagenes = jQuery('#modalImportarImagenes');
                modalImportarImagenes.on('shown.bs.modal', function (e) {
                    modalImportarImagenes.find('button:disabled').removeAttr('disabled');
                }).on('hidden.bs.modal', function (e) {
                    document.getElementById('import-images-csv').value=null;
                });
            });
        ");
    ?>

    <input type="hidden" name="task" value=""/>
    <input type="hidden" name="boxchecked" value="0"/>
    <?php echo JHtml::_('form.token'); ?>
</form>