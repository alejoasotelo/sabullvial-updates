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

use Joomla\CMS\MVC\Controller\FormController;

/**
 * ProductoImagen Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerProductoImagen extends FormController
{
    protected $view_list = 'productoimagenes';


    /**
     * Method to add a new record.
     *
     * @return  boolean  True if the record can be added, false if not.
     *
     * @since   1.6
     */
    public function add()
    {
        $context = "$this->option.edit.$this->context";

        // Access check.
        if (!$this->allowAdd()) {
            // Set the internal error and also the redirect error.
            $this->setError(\JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'));
            $this->setMessage($this->getError(), 'error');

            $this->setRedirect(
                \JRoute::_(
                    'index.php?option=' . $this->option . '&view=' . $this->view_list
                    . $this->getRedirectToListAppend(),
                    false
                )
            );

            return false;
        }

        $idProducto = $this->input->getInput('id_producto', '');

        // Clear the record edit information from the session.
        \JFactory::getApplication()->setUserState($context . '.data', null);

        // Redirect to the edit screen.
        $this->setRedirect(
            \JRoute::_(
                'index.php?option=' . $this->option . '&view=' . $this->view_item
                . $this->getRedirectToItemAppend($idProducto, 'id_producto'),
                false
            )
        );

        return true;
    }
}
