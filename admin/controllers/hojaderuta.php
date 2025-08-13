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
use Joomla\CMS\Response\JsonResponse;

/**
 * HojaDeRuta Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerHojaDeRuta extends JControllerForm
{
    protected $view_list = 'hojasderuta';

    /**
     * Method to check if you can edit an existing record.
     *
     * Extended classes can override this if necessary.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key; default is id.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function allowEdit($data = [], $key = 'id')
    {
        $vendedor = SabullvialHelper::getVendedor();

        if ($vendedor->get('administrar.remitos', false)) {
            return true;
        }

        return \JFactory::getUser()->authorise('core.edit', $this->option);
    }

    /**
     * Method to unpublish a record.
     *
     * @param   string  $key     The name of the primary key of the URL variable.
     * @param   string  $urlVar  The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
     *
     * @return  boolean  True if successful, false otherwise.
     *
     * @since   1.6
     */
    public function unpublish($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        $this->checkToken();

        $model = $this->getModel();
        $table = $model->getTable();

        // Determine the name of the primary key for the data.
        if (empty($key)) {
            $key = $table->getKeyName();
        }

        // To avoid data collisions the urlVar may be different from the primary key.
        if (empty($urlVar)) {
            $urlVar = $key;
        }

        $recordId = $this->input->getInt($urlVar);

        // Get the model.
        $model = $this->getModel();

        try {
            $itemId = $recordId;
            $ntext = \JText::_($this->text_prefix . '_HOJA_DE_RUTA_UNPUBLISH_SUCCESS');
            if (!$model->publish($itemId, SabullvialModelHojaDeRuta::ESTADO_ANULADO)) {
                $ntext = '';
                $errors = $model->getErrors();
                foreach ($errors as $error) {
                    \JFactory::getApplication()->enqueueMessage($error, 'error');
                }
            }

            if (!empty($ntext)) {
                $this->setMessage(\JText::_($ntext));
            }

        } catch (\Exception $e) {
            $this->setMessage($e->getMessage(), 'error');
        }

        $this->setRedirect(
            \JRoute::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item
                    . $this->getRedirectToItemAppend($recordId, $urlVar),
                false
            )
        );
    }
}
