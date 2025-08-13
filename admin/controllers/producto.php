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
 * Producto Controller
 *
 * @package     Sabullvial.Administrator
 * @subpackage  com__sabullvial
 */
class SabullvialControllerProducto extends JControllerForm
{
    protected $view_list = 'productos';

    public function batch($model = null)
    {
        $model = $this->getModel('producto');
        $this->setRedirect((string)JUri::getInstance());
        return parent::batch($model);
    }
}
