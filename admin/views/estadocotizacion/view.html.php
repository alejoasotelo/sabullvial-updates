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
 * EstadoCotizacion View
 *
 * @since  0.0.1
 */
class SabullvialViewEstadoCotizacion extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    protected $canDo = null;

    /**
     * Display the EstadoCotizacion view
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        // Get the Data
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->canDo = JHelperContent::getActions('com_sabullvial', 'estadocotizacion', $this->item->id);

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }


        // Set the toolbar
        $this->addToolBar();

        // Display the template
        parent::display($tpl);

        // Set the document
        $this->setDocument();
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function addToolBar()
    {
        $input = JFactory::getApplication()->input;

        // Hide Joomla Administrator Main menu
        $input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $userId     = $user->id;
        $isNew = ($this->item->id == 0);

        // Built the actions for new and existing records.
        $canDo = $this->canDo;

        JToolbarHelper::title(JText::_('COM_SABULLVIAL_ESTADO_COTIZACION_MANAGER_' . ($isNew ? 'NEW' : 'EDIT')), 'pencil-2 article-add');

        if ($isNew) {
            if ($canDo->get('core.create')) {
                JToolbarHelper::apply('estadocotizacion.apply');
                JToolbarHelper::save('estadocotizacion.save');
                JToolbarHelper::save2new('estadocotizacion.save2new');
            }

            JToolbarHelper::cancel('estadocotizacion.cancel');
        } else {
            // Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
            $itemEditable = $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $this->item->created_by == $userId);

            if ($itemEditable) {
                JToolbarHelper::apply('estadocotizacion.apply');
                JToolbarHelper::save('estadocotizacion.save');

                // We can save this record, but check the create permission to see if we can return to make a new one.
                if ($canDo->get('core.create')) {
                    JToolbarHelper::save2new('estadocotizacion.save2new');
                }
            }

            JToolbarHelper::cancel('estadocotizacion.cancel', 'JTOOLBAR_CLOSE');
        }
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $isNew = ($this->item->id < 1);
        $document = JFactory::getDocument();
        $document->setTitle($isNew ? JText::_('COM_SABULLVIAL_ESTADO_COTIZACION_CREATING') :
            JText::_('COM_SABULLVIAL_ESTADO_COTIZACION_EDITING'));
    }
}
