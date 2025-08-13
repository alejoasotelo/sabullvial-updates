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

use Joomla\CMS\Table\Table;

/**
 * RemitoEstado Table class
 *
 * @since  0.0.1
 */
class SabullvialTableRemitoEstado extends Table
{
    /**
     * Constructor
     *
     * @param   JDatabaseDriver  &$db  A database connector object
     */
    public function __construct(&$db)
    {
        parent::__construct('#__sabullvial_remitoestado', 'id', $db);
    }
    /**
     * Stores a contact.
     *
     * @param   boolean  $updateNulls  True to update fields even if they are null.
     *
     * @return  boolean  True on success, false on failure.
     *
     * @since   1.6
     */
    public function store($updateNulls = true)
    {
        $date   = JFactory::getDate()->toSql();
        $userId = JFactory::getUser()->id;

        // Set created date if not set.
        if (!(int) $this->created) {
            $this->created = $date;
        }

        if ($this->id) {
            // Existing item
            $this->modified_by = $userId;
            $this->modified    = $date;
        } else {
            // Field created_by field can be set by the user, so we don't touch it if it's set.
            if (empty($this->created_by)) {
                $this->created_by = $userId;
            }

            if (empty($this->created_by_alias)) {
                $this->created_by_alias = JFactory::getUser()->username;
            }

            if (!(int) $this->modified) {
                $this->modified = $date;
            }

            if (empty($this->modified_by)) {
                $this->modified_by = $userId;
            }
        }

        $imageOptimized = 0;

        // Si se subio una imagen nueva, se actualiza la imagen
        if (isset($this->upload_image) && !empty($this->upload_image)) {
            // El upload_image puede ser string (compatibilidad) o array con path y optimized
            if (is_array($this->upload_image)) {
                $this->image = $this->upload_image['path'];
                $imageOptimized = $this->upload_image['optimized'] ? 1 : 0;
            } else {
                $this->image = $this->upload_image;
            }
            unset($this->upload_image);
        } else {
            $this->image = null;
        }

        $isStored = parent::store($updateNulls);

        if (!$isStored) {
            return false;
        }

        /** @var SabullvialTableRemitoHistorico $historico */
        $historico = Table::getInstance('RemitoHistorico', 'SabullvialTable');
        $historico->insertEstado($this->numero_remito, $this->id_estadoremito, $this->image, $imageOptimized);

        return $isStored;
    }
}
