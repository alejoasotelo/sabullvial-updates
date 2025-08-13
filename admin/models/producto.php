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

use Joomla\CMS\Helper\MediaHelper;
use Joomla\CMS\Table\Table;

/**
 * Producto Model
 *
 * @since  0.0.1
 */
class SabullvialModelProducto extends JModelAdmin
{
    protected $batch_commands = [
        'upload_images' => 'batchUploadImages',
    ];

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
    public function getTable($type = 'Producto', $prefix = 'SabullvialTable', $config = [])
    {
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
            'com_sabullvial.producto',
            'producto',
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
            'com_sabullvial.edit.producto.data',
            []
        );

        if (empty($data)) {
            $data = $this->getItem();
        }

        return $data;
    }

    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        /*if ($item->id > 0) {
            $productPriceTable = JTable::getInstance('CustomerProductPrice', 'SastoreTable');
            $item->customer_product_price = $productPriceTable->loadByIdCustomer($item->id);

            if (count($item->customer_product_price)) {
                $item->have_custom_prices = true;
            }

            $table = $this->getTable();
            $item->orders = $table->getOrders($item->id);
            $item->orderpayments = $table->getOrderPayments($item->id);
        }*/

        return $item;
    }

    public function save($data)
    {
        $isSaved = parent::save($data);

        /*$id = $this->getState($this->getName() . '.id');

        if ($isSaved && $data['have_custom_prices'] && count($data['customer_product_price'])) {
            $data['customer_product_price'] = $this->removeDuplicateProducts($data['customer_product_price']);

            $table = $this->getTable();
            $table->load($idCustomer);

            $productPrices = $table->getProductPrices();

            // Obtengo los IDs de los productos nuevos o que ya había
            $newProductPricesIds = $this->extractIdsFromArray($data['customer_product_price']);
            $currentProducPricesIds = $this->extractIdsFromArray($productPrices);
            $productPricesToDelete = SastoreHelper::array_diff($currentProducPricesIds, $newProductPricesIds);

            // Elimino los productPrices que hay que eliminar.
            $table->deleteProductPricesById($productPricesToDelete);

            $productPriceTable = JTable::getInstance('CustomerProductPrice', 'SastoreTable');
            foreach ($data['customer_product_price'] as $customerProduct) {
                $item = [
                    'id' => $customerProduct['id'],
                    'id_customer' => $data['id'],
                    'id_product' => (int)$customerProduct['id_product'],
                    'price' => (float)$customerProduct['price'],
                ];
                $productPriceTable->save($item);
            }
        } else if($isSaved && !$data['have_customer_prices']) {
            $table = $this->getTable();
            $table->load($idCustomer);
            $table->deleteAllProductPrices();
        }*/

        return $isSaved;
    }

    public function batchUploadImages($value, $pks, $contexts)
    {
        if (empty($value) || !isset($value['action']) || $value['action'] != 'uploadImages') {
            return false;
        }

        $input = JFactory::getApplication()->input;

        $files = $input->files->get('batch', [], 'array');

        if (!isset($files['upload_images']['images'])) {
            return false;
        }

        $fileImages = $files['upload_images']['images'];

        $dest = JPATH_ROOT . '/images/com_sabullvial/productos/';
        foreach ($fileImages as $file) {
            if ($file['error'] == 4 || $file['size'] == 0) {
                continue;
            }

            $filePath = $dest . $file['name'];
            if (!$this->uploadFile($file, $filePath)) {
                return false;
            }

            $info = @getimagesize($filePath);
            $width  = @$info[0];
            $height = @$info[1];
            $resizeWidth = 320;
            $newDimensions = MediaHelper::imageResize($width, $height, $resizeWidth);

            if (!$this->resizeImage($filePath, $newDimensions[0], $newDimensions[1])) {
                return false;
            }

            $filePaths[] = $file['name'];
        }


        $cid  = (array) $input->post->get('cid', [], 'cmd');

        // Remove zero values resulting from input filter
        $cid = array_filter($cid);

        foreach ($cid as $pk) {
            /** @var SabullvialTableProductoImagen $productoImagen */
            $productoImagen = Table::getInstance('ProductoImagen', 'SabullvialTable');
            $productoImagen->load(['id_producto' => $pk]);
            $productoImagen->id_producto = $pk;

            $images = new JRegistry();
            foreach ($filePaths as $i => $filePath) {
                $images->set('images' . $i . '.path', '/images/com_sabullvial/productos/' . $filePath);
            }

            $productoImagen->images = $images->toString();

            if (!$productoImagen->store()) {
                $this->setError($productoImagen->getError());
            }
        }

        return true;
    }

    protected function uploadFile($file, $dest = null)
    {
        // Make sure that file uploads are enabled in php.
        if (!(bool) ini_get('file_uploads')) {
            JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLFILE'));

            return false;
        }

        // Make sure that zlib is loaded so that the package can be unpacked.
        if (!extension_loaded('zlib')) {
            JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLZLIB'));

            return false;
        }

        // If there is no uploaded file, we have a problem...
        if (!is_array($file)) {
            JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_NO_FILE_SELECTED'));

            return false;
        }

        // Is the PHP tmp directory missing?
        if ($file['error'] && ($file['error'] == UPLOAD_ERR_NO_TMP_DIR)) {
            JError::raiseWarning(
                '',
                JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br />' . JText::_('COM_INSTALLER_MSG_WARNINGS_PHPUPLOADNOTSET')
            );

            return false;
        }

        // Is the max upload size too small in php.ini?
        if ($file['error'] && ($file['error'] == UPLOAD_ERR_INI_SIZE)) {
            JError::raiseWarning(
                '',
                JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR') . '<br />' . JText::_('COM_INSTALLER_MSG_WARNINGS_SMALLUPLOADSIZE')
            );

            return false;
        }

        // Check if there was a different problem uploading the file.
        if ($file['error'] || $file['size'] < 1) {
            JError::raiseWarning('', JText::_('COM_INSTALLER_MSG_INSTALL_WARNINSTALLUPLOADERROR'));

            return false;
        }

        // Build the appropriate paths.
        $tmp_dest = is_null($dest) ? JPATH_ROOT . '/images/com_sabullvial/' . $file['name'] : $dest;
        $tmp_src  = $file['tmp_name'];

        // Move uploaded file.
        jimport('joomla.filesystem.file');
        return JFile::upload($tmp_src, $tmp_dest, false, true);
    }

    public function resizeImage($file, $width, $height)
    {
        $path    = JPath::clean($file);

        try {
            $image      = new JImage($path);
            $properties = $image->getImageFileProperties($path);

            switch ($properties->mime) {
                case 'image/png':
                    $imageType = IMAGETYPE_PNG;
                    break;
                case 'image/gif':
                    $imageType = IMAGETYPE_GIF;
                    break;
                default:
                    $imageType = IMAGETYPE_JPEG;
            }

            $image->resize($width, $height, false, JImage::SCALE_FILL);
            $image->toFile($path, $imageType);

            return true;
        } catch (Exception $e) {
            $this->setError($e->getMessage());
        }

        return false;
    }
}
