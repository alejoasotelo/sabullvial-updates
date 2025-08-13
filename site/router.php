<?php

/**
 * @package     Sabullvial.Administrator
 * @subpackage  com_sabullvial
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Component\Router\RouterBase;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

class SabullvialRouter extends RouterBase
{
    public function build(&$query)
    {
        $segments = [];

        if ($this->isQueryRemitoCambiarEstado($query)) {
            return $this->buildRemitoCambiarEstado($query);
        }

        if (isset($query['view'])) {
            $segments[] = $query['view'];
            unset($query['view']);
        }
        if (isset($query['id'])) {
            $segments[] = $query['id'];
            unset($query['id']);
        }

        return $segments;
    }

    protected function isQueryRemitoCambiarEstado($query)
    {
        if (!$query['option'] == 'com_sabullvial') {
            return false;
        }

        $hasView = isset($query['view']) && $query['view'] == 'remito';
        $hasEdit = isset($query['layout']) && $query['layout'] == 'edit';
        $hasId = isset($query['id']) && !empty($query['id']);

        return $hasView && $hasEdit && $hasId;
    }

    protected function buildRemitoCambiarEstado(&$query)
    {
        $segments = ['qrcode', $query['id']];

        unset($query['view']);
        unset($query['layout']);
        unset($query['id']);

        return $segments;
    }

    public function parse(&$segments)
    {
        if ($this->isRemitoCambiarEstado($segments)) {
            return $this->parseRemitoCambiarEstado($segments);
        }

        $vars = [];
        $vars['view'] = 'cliente';
        $vars['layout'] = 'edit';

        $len = strlen($segments[0]);
        if ($len >= 2 && $len <= 4) {
            $vars['codigo_vendedor'] = strtoupper($segments[0]);
            return $vars;
        }

        // switch ($segments[0]) {
        //     case 'clientes':
        //         $vars['view'] = 'clientes';
        //         break;
        //     case 'cliente':
        //         $vars['view'] = 'cliente';
        //         $id = explode(':', $segments[1]);
        //         $vars['id'] = (int) $id[0];
        //         break;
        // }
        return $vars;
    }

    protected function isRemitoCambiarEstado($segments)
    {
        // url: index.php?option=com_sabullvial&view=remito&layout=edit&id=R0000100078618
        // url-sef: alias-menu/edit/R0000100078618
        $editAliases = ['edit', 'editar', 'qr', 'qrcode'];

        return count($segments) == 2 && in_array($segments[0], $editAliases);

    }

    protected function parseRemitoCambiarEstado(&$segments)
    {
        $vars = [];
        $vars['view'] = 'remito';
        $vars['layout'] = 'edit';
        $vars['id'] = $segments[1];

        return $vars;
    }
}
