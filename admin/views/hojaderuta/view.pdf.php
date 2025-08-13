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
use Joomla\CMS\Version;
use Joomla\CMS\Document\PdfDocument;

/**
 * Cotizacion View
 *
 * @since  0.0.1
 */
class SabullvialViewHojaDeRuta extends JViewLegacy
{
    /**
     * View form
     *
     * @var         form
     */
    protected $form = null;

    protected $canDo = null;

    /**
     * Display the Cotizacion view
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
        $this->state = $this->get('State');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode('<br />', $errors));

            return false;
        }

        // Set the document
        $this->setDocument();

        // Display the template
        parent::display($tpl);
    }

    /**
     * Method to set up the document properties
     *
     * @return void
     */
    protected function setDocument()
    {
        $document = JFactory::getDocument();
        $document->setTitle(JText::_('COM_SABULLVIAL_HOJA_DE_RUTA_PDF_TITLE'));
        $document->setName(JText::sprintf('COM_SABULLVIAL_HOJA_DE_RUTA_PDF_FILENAME', $this->item->id));
        $document->setOptions([
            'Attachment' => false,
            'orientation' => 'landscape'
        ]);
    }
}
