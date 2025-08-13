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
 * Regla Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerRegla extends JControllerForm
{
    protected $view_list = 'reglas';

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

        if ($vendedor->get('ver.reglas', false)) {
            return true;
        }

        return \JFactory::getUser()->authorise('core.edit', $this->option);
    }
}
