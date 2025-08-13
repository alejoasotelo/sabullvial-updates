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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Image\Image;

/**
 * Cotizacion Model
 *
 * @since  0.0.1
 */
class SabullvialModelRemito extends JModelForm
{
    // Tamaño máximo de una dimensión para redimensionar imágenes (mismo que en backend)
    public const MAX_SIZE = 2048;
    /**
     * Model context string.
     *
     * @var        string
     */
    protected $_context = 'com_sabullvial.remito';

    /**
     * @var object item
     */
    protected $item;

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return	void
     * @since	2.5
     */
    protected function populateState()
    {
        // Get the message id
        $jinput = JFactory::getApplication()->input;
        $id     = $jinput->get('id');
        $this->setState('remito.id', $id);

        // Load the parameters.
        //$this->setState('params', JFactory::getApplication()->getParams());
        parent::populateState();
    }

    /**
     * Method to get a table object, load it if necessary.
     *
     * @param   string  $type    The table name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  JTable  A JTable object
     *
     * @since   1.6
     */
    public function getTable($type = 'BullvialSitPedidosRemitos', $prefix = 'SabullvialTable', $config = [])
    {
        JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR . '/tables');
        return JTable::getInstance($type, $prefix, $config);
    }

    /**
     * Method to get the record form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed    A JForm object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_sabullvial.remito',
            'remito',
            [
                'control' => 'jform',
                'load_data' => $loadData
            ]
        );

        if (empty($form)) {
            return false;
        }

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        $data = JFactory::getApplication()->getUserState(
            'com_sabullvial.edit.remito.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    /**
     * Get the message
     * @return object The message to be displayed to the user
     */
    public function getItem()
    {
        if (!isset($this->item)) {
            $id    = $this->getState('remito.id');

            // Get a level row instance
            $table = $this->getTable();
            $table->load($id);
            $table->loadEstado();

            $this->item = $table;
        }

        return $this->item;
    }

    /**
     * Sube la imagen del remito y devuelve información sobre la subida y optimización.
     * Aplica optimización de imagen (redimensionado y calidad) durante la subida.
     *
     * @return array|bool Array con 'path' e 'optimized', o false en caso de error
     */
    public function uploadImage($idRemito, $formFile)
    {
        $imageRemitosUri = '/images/com_sabullvial/remitos/' . $idRemito . '/';
        $imageRemitosPath = JPATH_SITE.$imageRemitosUri;

        if (empty($formFile['image']['name'])) {
            $this->setError(Text::_('COM_SABULLVIAL_REMITO_ERROR_NO_IMAGE'));
            return false;
        }

        // Crear directorio si no existe
        if (!is_dir($imageRemitosPath)) {
            if (!Folder::create($imageRemitosPath)) {
                $this->setError(Text::_('COM_SABULLVIAL_REMITO_ERROR_UPLOADING_IMAGE'));
                return false;
            }
        }

        $src = $formFile['image']['tmp_name'];
        $ext = strtolower(File::getExt($formFile['image']['name']));
        $uniqueFileName = date('Ymd') . '-' . md5(uniqid()) . '.' . $ext;
        $fileName = File::makeSafe($uniqueFileName);
        $dest = $imageRemitosPath.$fileName;

        // Primero subir el archivo temporalmente
        if (!File::upload($src, $dest)) {
            $this->setError(Text::_('COM_SABULLVIAL_REMITO_ERROR_UPLOADING_IMAGE'));
            return false;
        }

        // Ahora optimizar la imagen si es necesario
        $optimized = false;
        try {
            $optimized = $this->optimizeImage($dest);
        } catch (Exception $e) {
            // Si hay error en optimización, mantener la imagen original
            // pero registrar el error en logs si es necesario
        }

        return [
            'path' => $imageRemitosUri.$fileName,
            'optimized' => $optimized
        ];
    }

    /**
     * Optimiza una imagen aplicando redimensionado y configuración de calidad.
     * Usa la misma lógica que el optimizador del backend.
     *
     * @param string $imagePath Ruta completa al archivo de imagen
     * @return bool True si se optimizó, false en caso contrario
     */
    private function optimizeImage($imagePath)
    {
        if (!file_exists($imagePath)) {
            return false;
        }

        try {
            $props = Image::getImageFileProperties($imagePath);
            $width = $props->width;
            $height = $props->height;
            $format = $props->mime;

            // Solo redimensionar si alguna dimensión es mayor a MAX_SIZE
            if ($width > self::MAX_SIZE || $height > self::MAX_SIZE) {
                $scale = min(self::MAX_SIZE / $width, self::MAX_SIZE / $height);
                $newW = (int)round($width * $scale);
                $newH = (int)round($height * $scale);

                $image = new Image($imagePath);
                $image->resize($newW, $newH, false, Image::SCALE_FILL);

                // Corregir orientación EXIF si es JPEG (importante hacerlo después de resize)
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
                    // WebP no soportado por Image::toFile en Joomla 3, guardar como JPEG
                    $type = IMAGETYPE_JPEG;
                    $options = ['quality' => 90];
                }

                $image->toFile($imagePath, $type, $options);
                clearstatcache(true, $imagePath);

                return true;
            }
        } catch (Exception $e) {
            // En caso de error, mantener imagen original
            return false;
        }

        return false;
    }
}
