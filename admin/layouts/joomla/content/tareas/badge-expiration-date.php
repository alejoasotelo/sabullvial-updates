<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

extract($displayData);

if (!isset($expirationDate)) {
    throw new Exception('expirationDate is not defined');
}

if (!isset($daysToConsiderExpired)) {
    throw new Exception('daysToConsiderExpired is not defined');
}

$expirationTime = strtotime(date('Y-m-d', strtotime($expirationDate))); // elimino la hora
$expirationDateFormated = $expirationDate > 0 ? JHtml::_('date', $expirationDate, Text::_('DATE_FORMAT_LC4')) : '-';

$todayTime = strtotime(date('Y-m-d'));
$daysToExpire = ($expirationTime - $todayTime) / (24 * 60 * 60);

$isExpired = $daysToExpire <= 0;
// días para considerar expirado
$isNearToExpire = $daysToExpire <= $daysToConsiderExpired;
?>
<div>
<?php if ($isExpired) : ?>
    <span class="label label-important hasTooltip" title="<?php echo Text::sprintf('COM_SABULLVIAL_TAREA_TAREA_EXPIRADA'); ?>">
        <span class="icon-clock" aria-hidden="true"></span>
        <?php echo $expirationDateFormated; ?>
    </span>
    <?php if ($showLabel): ?>
        <div class="muted">
            <?php echo Text::_('COM_SABULLVIAL_TAREA_TAREA_EXPIRADA'); ?>
        </div>
    <?php endif; ?>
<?php elseif ($isNearToExpire) : ?>
    <?php $text = 'COM_SABULLVIAL_TAREA_TAREA_CERCA_DE_EXPIRAR' . ($daysToExpire > 1 ? '_PLURAL' : ''); ?>
    <span class="label label-warning hasTooltip" title="<?php echo Text::sprintf($text, $daysToExpire); ?>">
        <span class="icon-clock" aria-hidden="true"></span>
        <?php echo $expirationDateFormated; ?>
    </span>
    <?php if ($showLabel): ?>
        <div class="muted">
            <?php echo Text::sprintf($text, $daysToExpire); ?>
        </div>
    <?php endif; ?>
<?php else : ?>
    <span class="label label-info">
        <span class="icon-clock" aria-hidden="true"></span>
        <?php echo $expirationDateFormated; ?>
    </span>
    <?php if ($showLabel): ?>
        <div class="muted">
            <?php echo Text::_('COM_SABULLVIAL_TAREA_TAREA_ACTIVA'); ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
    </div>