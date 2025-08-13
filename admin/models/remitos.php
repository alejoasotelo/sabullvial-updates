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
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;
use Joomla\Image\Image as JImage;

/**
 * SabullvialList Model
 *
 * @since  0.0.1
 */
class SabullvialModelRemitos extends ListModel
{
    public const MAX_SIZE = 2048; // Tamaño máximo de una dimensión para redimensionar imágenes

    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     JController
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id',
                'r.N_REMITO', 'numero_remito',
                'f.NCOMP_FAC','numero_factura',
                'r.FECHA_REM', 'fecha_remito',
                'er.id', 'er.nombre', 'estadoremito',
                'expreso',
                'r.DIRECCION', 'direccion_entrega',
                'localidad_entrega',
                'provincia_entrega', 'estado',
                'monto_remito', 'cliente',
                'date_from', 'date_to',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return      string  An SQL query
     */
    protected function getListQuery()
    {
        $vendedor = SabullvialHelper::getVendedor();

        // Initialize variables.
        $db    = JFactory::getDbo();
        $query = $db->getQuery(true);

        // Id, num remito, num factura, cliente, expreso, dirección de entrega, estado y monto del remito (sin iva)

        // Create the base select statement.
        $query->select('r.N_REMITO id, r.N_REMITO numero_remito, r.FECHA_REM fecha_remito') // r.N_FACTURA numero_factura
            ->select('r.NOMBRE_TRA expreso, r.DIRECCION direccion_entrega, r.LOCALIDAD localidad_entrega')
            ->select('r.NOMBRE_PRO provincia_entrega, sum(r.IMPORTE_SIN_IVA) monto_remito')
            ->from($db->qn('SIT_PEDIDOS_REMITOS', 'r'))
            ->group('r.N_REMITO');

        $query->select('co.id id_cotizacion')
            ->leftJoin($db->qn('#__sabullvial_cotizacion', 'co') . ' ON (co.id = r.ID_PEDIDO_WEB)');

        $query->select('c.RAZON_SOCI cliente')
            ->leftJoin($db->qn('SIT_CLIENTES', 'c') . ' ON (c.COD_CLIENT = r.COD_CLIENT)');

        $query->select('hr.id_hojaderuta, h.delivery_date hojaderuta_delivery_date, h.chofer hojaderuta_chofer, h.patente hojaderuta_patente')
            ->leftJoin($db->qn('#__sabullvial_hojaderutaremito', 'hr') . ' ON (hr.numero_remito = r.N_REMITO)')
            ->leftJoin($db->qn('#__sabullvial_hojaderuta', 'h') . ' ON (h.id = hr.id_hojaderuta)');

        $query->select('f.NCOMP_FAC numero_factura')
             ->leftJoin($db->qn('SIT_PEDIDOS_FACTURAS', 'f') . ' ON (f.ID_PEDIDO_WEB = r.ID_PEDIDO_WEB)');

        $query
            ->select('re.delivery_date, IF(er.id > 0, IF(er.entregado > 0 OR er.entregado_mostrador > 0, 1, 0), 0) entregado')
            ->leftJoin($db->qn('#__sabullvial_remitoestado', 're') . ' ON (re.numero_remito = r.N_REMITO)');

        $cmpParams = SabullvialHelper::getComponentParams();
        $idEstadoDefault = $cmpParams->get('remitos_estados_default_sin_estado', 0);

        $sqlIfHasHojaDeRuta = 'IF(re.id_estadoremito > 0, re.id_estadoremito, IF(hr.id_hojaderuta > 0, 
            (SELECT id FROM '.$db->qn('#__sabullvial_estadoremito').' WHERE transito = 1 LIMIT 1), 
            '.$idEstadoDefault.'
            ))';
        $query->select($sqlIfHasHojaDeRuta . ' id_estadoremito, er.nombre estadoremito, er.color estadoremito_bg_color, er.color_texto estadoremito_color')
            ->select('er.proceso estadoremito_proceso')
            ->select('er.preparacion estadoremito_preparacion')
            ->select('er.transito estadoremito_transito, er.entregado estadoremito_entregado, er.entregado_mostrador estadoremito_entregado_mostrador')
            ->leftJoin($db->qn('#__sabullvial_estadoremito', 'er') . ' ON (er.id = '.$sqlIfHasHojaDeRuta.')');

        // Filter: like / search
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $query->where('r.N_REMITO = ' . $db->q(substr($search, 3)));
            } else {
                $sqlSearchQuery = '
                    r.N_REMITO LIKE #query# OR 
                    r.DIRECCION LIKE #query# OR 
                    c.RAZON_SOCI LIKE #query# OR 
                    r.NOMBRE_TRA LIKE #query# OR
                    f.NCOMP_FAC LIKE #query# OR
                    co.id LIKE #query#
                ';
                $searchQuery = SabullvialHelper::unorderedSearch($search, $sqlSearchQuery, $db);
                $query->where($searchQuery);
            }
        }

        // Filter by codigo_cliente
        $codigoCliente = $this->getState('filter.codigo_cliente');

        if (is_numeric($codigoCliente)) {
            $type = $this->getState('filter.codigo_cliente.include', true) ? '= ' : '<>';
            $query->where('r.COD_CLIENT ' . $type . (int) $codigoCliente);
        } elseif (is_array($codigoCliente)) {
            $codigoCliente = ArrayHelper::toInteger($codigoCliente);
            $codigoCliente = implode(',', $codigoCliente);
            $query->where('r.COD_CLIENT IN (' . $codigoCliente . ')');
        }

        // Filter by codigo_cliente
        $expreso = $this->getState('filter.expreso');

        if (is_string($expreso) && !empty($expreso)) {
            $type = $this->getState('filter.expreso.include', true) ? '= ' : '<>';
            $query->where('r.NOMBRE_TRA ' . $type . $db->q($expreso));
        } elseif (is_array($expreso)) {
            foreach ($expreso as &$item) {
                $item = $db->q($item);
            }
            $expreso = implode(',', $expreso);
            $query->where('r.NOMBRE_TRA IN (' . $expreso . ')');
        }


        // Filter by estado
        $estado = $this->getState('filter.estado');

        if (!empty($estado)) {
            $query->where('er.id =  ' . $db->q($estado));
        }

        $deliveryDate = $this->getState('filter.delivery_date');

        if (!empty($deliveryDate)) {
            $date = Factory::getDate($deliveryDate)->toSql();
            $query->where('DATE(re.delivery_date) =  ' . $db->q($date));
        }

        $dateFrom = $this->getState('filter.date_from');
        if (!empty($dateFrom)) {
            $date = Factory::getDate($dateFrom)->toSql();
            $query->where('r.FECHA_REM >= ' . $db->q($date));
        }

        $dateTo = $this->getState('filter.date_to');
        if (!empty($dateTo)) {
            $date = Factory::getDate($dateTo)->toSql();
            $query->where('r.FECHA_REM <= ' . $db->q($date));
        }

        // Si el usuario es de tipo Vendedor y tiene un código, filtro por código.
        // A menos que tenga permisos para ver todos los clientes.
        $verTodosLosClientes = $vendedor->get('ver.todosLosClientes', false);
        $codigoVendedor = $vendedor->get('codigo', '');
        $hasFilterByCodigoVendedor = SabullvialHelper::isUserVendedor() && !empty($codigoVendedor);
        if ($hasFilterByCodigoVendedor && !$verTodosLosClientes) {
            $query->where('c.COD_VENDED = ' . $db->q($codigoVendedor));
        } else {
            $codigoVendedor = $this->getState('filter.codigo_vendedor');
            if (!empty($codigoVendedor) && $verTodosLosClientes) {
                $query->where('c.COD_VENDED = ' . $db->q($codigoVendedor));
            }
        }

        // Add the list ordering clause.
        $orderCol	= $this->state->get('list.ordering', 'r.N_REMITO');
        $orderDirn 	= $this->state->get('list.direction', 'desc');

        $query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

        return $query;
    }

    /**
     * Returns a record count for the query.
     *
     * Note: Current implementation of this method assumes that getListQuery() returns a set of unique rows,
     * thus it uses SELECT COUNT(*) to count the rows. In cases that getListQuery() uses DISTINCT
     * then either this method must be overridden by a custom implementation at the derived Model Class
     * or a GROUP BY clause should be used to make the set unique.
     *
     * @param   \JDatabaseQuery|string  $query  The query.
     *
     * @return  integer  Number of rows for query.
     *
     * @since   3.0
     */
    protected function _getListCount($query)
    {
        // Remove the limit, offset and order parts if it's a \JDatabaseQuery object
        if ($query instanceof \JDatabaseQuery) {
            $subQuery = clone $query;
            $subQuery
                ->clear('limit')
                ->clear('offset')
                ->clear('order')
                ->clear('select')
                ->select('r.N_REMITO');

            $db    = JFactory::getDbo();
            $query = $db->getQuery(true);
            $query->select('count(*)')->from('('.$subQuery.') t');

            $this->getDbo()->setQuery($query);

            return (int) $this->getDbo()->loadResult();
        }

        $this->getDbo()->setQuery($query);
        $this->getDbo()->execute();

        return (int) $this->getDbo()->getNumRows();
    }

    /**
     * Method to auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState($ordering = 'r.N_REMITO', $direction = 'desc')
    {
        $app = JFactory::getApplication();

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);

        $estado = $this->getUserStateFromRequest($this->context . '.filter.estado', 'filter_estado', '');
        $this->setState('filter.estado', $estado);

        $deliveryDate = $this->getUserStateFromRequest($this->context . '.filter.delivery_date', 'filter_delivery_date', '');
        $this->setState('filter.delivery_date', $deliveryDate);

        $codigoVendedor = $this->getUserStateFromRequest($this->context . '.filter.codigo_vendedor', 'filter_codigo_vendedor', '');
        $this->setState('filter.codigo_vendedor', $codigoVendedor);

        $dateFrom = $this->getUserStateFromRequest($this->context . '.filter.date_from', 'filter_date_from', '');
        $this->setState('filter.date_from', $dateFrom);

        $dateTo = $this->getUserStateFromRequest($this->context . '.filter.date_to', 'filter_date_to', '');
        $this->setState('filter.date_to', $dateTo);

        $formSubmited = $app->input->post->get('form_submited');

        if ($formSubmited) {
            $codigoCliente = $this->getUserStateFromRequest($this->context . '.filter.codigo_cliente', 'filter_codigo_cliente');
            $this->setState('filter.codigo_cliente', $codigoCliente);

            $expreso = $this->getUserStateFromRequest($this->context . '.filter.expreso', 'filter_expreso');
            $this->setState('filter.expreso', $expreso);
        }

        // Load the parameters.
        $params = JComponentHelper::getParams('com_sabullvial');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to get a store id based on model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  A prefix for the store id.
     *
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . serialize($this->getState('filter.codigo_cliente'));
        $id .= ':' . serialize($this->getState('filter.expreso'));
        $id .= ':' . $this->getState('filter.codigo_vendedor');
        $id .= ':' . $this->getState('filter.delivery_date');
        $id .= ':' . $this->getState('filter.date_from');
        $id .= ':' . $this->getState('filter.date_to');
        $id .= ':' . $this->getState('filter.estado');

        return parent::getStoreId($id);
    }

    /**
     * Procesa un chunk de imágenes de remitos para optimización.
     * Devuelve info de cada imagen: ruta, existe, tamaño, dimensiones.
     */
    public function processImagesChunk()
    {
        $db = Factory::getDbo();
        $params = ComponentHelper::getParams('com_sabullvial');
        $chunkSize = (int) $params->get('remitos_optimizar_imagenes_chunk_size', 20);
        $chunkIndex = (int) $params->get('remitos_optimizar_imagenes_index', 0);
        $offset = $chunkIndex * $chunkSize;

        // Obtener imágenes no nulas y no optimizadas
        $query = $db->getQuery(true)
            ->select('id, numero_remito, image')
            ->from($db->qn('#__sabullvial_remitohistorico'))
            ->where($db->qn('image') . ' IS NOT NULL AND ' . $db->qn('image') . " != ''")
            ->where($db->qn('image_optimized') . ' = 0')
            ->order('id ASC');

        $db->setQuery($query, $offset, $chunkSize);
        $rows = $db->loadAssocList();

        $results = [];
        foreach ($rows as $row) {
            $imagePath = JPATH_SITE . '/' . ltrim($row['image'], '/');
            $exists = file_exists($imagePath);
            $originalSizeMB = null;
            $sizeMB = null;
            $dimensions = null;
            $format = null;
            $estimatedResizedMB = null;
            $newDimensions = null;
            $resized = false;
            $optimizada = 0;
            if ($exists) {
                try {
                    $props = JImage::getImageFileProperties($imagePath);
                    $width = $props->width;
                    $height = $props->height;
                    $dimensions = $width . 'x' . $height;
                    $format = $props->mime;
                    $originalSizeMB = $props->filesize !== null ? round($props->filesize / 1048576, 2) : null;
                    $sizeMB = $originalSizeMB;
                    // Solo redimensionar si alguna dimensión es mayor a MAX_SIZE
                    if ($width > self::MAX_SIZE || $height > self::MAX_SIZE) {
                        $scale = min(self::MAX_SIZE / $width, self::MAX_SIZE / $height);
                        $newW = (int)round($width * $scale);
                        $newH = (int)round($height * $scale);
                        $newDimensions = $newW . 'x' . $newH;
                        $image = new JImage($imagePath);
                        $image->resize($newW, $newH, false, JImage::SCALE_FILL);
                        // Corregir orientación EXIF si es JPEG
                        // IMPORTANTE hacerlo luego de resize
                        if (($format === 'image/jpeg' || $format === 'image/jpg') && function_exists('exif_read_data')) {
                            $exif = @exif_read_data($imagePath);
                            if (!empty($exif['Orientation'])) {
                                switch ($exif['Orientation']) {
                                    case 3:
                                        $image->rotate(180, 0, false);
                                        break;
                                    case 6:
                                        $image->rotate(-90, 0, false);
                                        break;
                                    case 8:
                                        $image->rotate(90, 0, false);
                                        break;
                                }
                            }
                        }
                        // Guardar con calidad adecuada
                        $type = IMAGETYPE_JPEG;
                        $options = ['quality' => 90];
                        if ($format === 'image/png') {
                            $type = IMAGETYPE_PNG;
                            $options = ['quality' => 7];
                        } elseif ($format === 'image/webp') {
                            // WebP no soportado por JImage::toFile en Joomla 3, guardar como JPEG
                            $type = IMAGETYPE_JPEG;
                            $options = ['quality' => 90];
                        }
                        $image->toFile($imagePath, $type, $options);
                        clearstatcache(true, $imagePath);
                        $propsResized = JImage::getImageFileProperties($imagePath);
                        $sizeMB = $propsResized->filesize !== null ? round($propsResized->filesize / 1048576, 2) : null;
                        $estimatedResizedMB = $sizeMB;
                        $resized = true;
                        $optimizada = 1;
                    } else {
                        $newDimensions = $dimensions;
                    }

                    $query = $db->getQuery(true);
                    $query
                        ->update($db->qn('#__sabullvial_remitohistorico'))
                        ->set($db->qn('image_optimized') . ' = 1')
                        ->where($db->qn('id') . ' = ' . (int)$row['id']);
                    $db->setQuery($query);
                    $db->execute();
                } catch (OutOfMemoryError $e) {
                    $error = true;
                    // Error de memoria, continuar
                } catch (RuntimeException $e) {
                    $error = true;
                    // Error al procesar imagen, continuar
                } catch(LogicException $e) {
                    $error = true;
                    // Error de lógica, continuar
                } catch (Exception $e) {
                    $error = true;
                    // Error al procesar imagen, continuar
                }
            }
            $results[] = [
                'id' => $row['id'],
                'numero_remito' => $row['numero_remito'],
                'image' => $row['image'],
                'exists' => $exists,
                'resized' => $resized,
                'optimizada' => $optimizada,
                'size' => $originalSizeMB,
                'dimensions' => $dimensions,
                'format' => $format,
                'estimated_resized' => $estimatedResizedMB,
                'new_dimensions' => $newDimensions,
                'path' => $imagePath
            ];
        }

        // Calcular si hay más chunks
        $queryTotal = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->qn('#__sabullvial_remitohistorico'))
            ->where($db->qn('image') . ' IS NOT NULL AND ' . $db->qn('image') . " != ''")
            ->where($db->qn('image_optimized') . ' = 0');
        $db->setQuery($queryTotal);
        $total = (int) $db->loadResult();
        $hasMore = ($offset + $chunkSize) < $total;

        return [
            'results' => $results,
            'chunkIndex' => $chunkIndex,
            'chunkSize' => $chunkSize,
            'total' => $total,
            'hasMore' => $hasMore
        ];
    }
}
