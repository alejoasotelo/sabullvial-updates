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

use Joomla\CMS\Language\Text;

/**
 * HelloWorld component helper.
 *
 * @param   string  $submenu  The name of the active view.
 *
 * @return  void
 *
 * @since   1.6
 */
abstract class PriceHelper extends JHelperContent
{
    public const FORMAT_ARS = 'COM_SABULLVIAL_PESO_FORMAT';
    public const FORMAT_USD = 'COM_SABULLVIAL_DOLAR_FORMAT';

    /**
     * Format a number to a currency
     *
     * @param $monto
     * @param string $format por default es PriceHelper::FORMAT_ARS
     * @param int $decimals
     * @param string $dec_point
     * @param string $thousands_sep
     * @return string
     */
    public static function format($monto, $format = self::FORMAT_ARS, $decimals = 2, $dec_point = ',', $thousands_sep = '.')
    {
        if ($format != self::FORMAT_USD && $format != self::FORMAT_ARS) {
            $format = 'COM_SABULLVIAL_PESO_FORMAT';
        }

        return Text::sprintf($format, number_format($monto, $decimals, $dec_point, $thousands_sep));
    }
}
