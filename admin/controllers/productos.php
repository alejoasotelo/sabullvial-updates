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

/**
 * Productos Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerProductos extends JControllerAdmin
{
    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The model name. Optional.
     * @param   string  $prefix  The class prefix. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  object  The model.
     *
     * @since   1.6
     */
    public function getModel($name = 'Producto', $prefix = 'SabullvialModel', $config = ['ignore_request' => true])
    {
        $model = parent::getModel($name, $prefix, $config);

        return $model;
    }

    public function exportImages()
    {
        $app = JFactory::getApplication();

        $app->setHeader('Content-Type', 'application/csv');
        $app->setHeader('Content-Disposition', 'attachment; filename="productos.csv"');
        $app->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT');
        $app->setHeader('Cache-Control', 'no-cache, must-revalidate');
        $app->setHeader('Pragma', 'no-cache');

        $input = $app->input;
        $ids = $input->get('cid', [], 'array');
        $model = $this->getModel('Productos');
        $csv = $model->exportImagesToCSV($ids);

        $app->setBody($csv);
        echo $app->toString(true);
        die();
    }

    public function importImages()
    {
        $app = JFactory::getApplication();
        $input = $app->input;
        $file = $input->files->get('import_images_csv');
        $model = $this->getModel('Productos');
        if (!$model->importImagesFromCSV($file['tmp_name'])) {
            $errors = $model->getErrors();
            foreach ($errors as $error) {
                $app->enqueueMessage($error, 'error');
            }
            return $app->redirect('index.php?option=com_sabullvial&view=productos');
        }

        $app->enqueueMessage('Imágenes importadas correctamente', 'message');
        $app->redirect('index.php?option=com_sabullvial&view=productos');
    }
}
