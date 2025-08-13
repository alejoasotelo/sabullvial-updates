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

$badgeTypes = [
    'success',
    'warning',
    'info',
    'danger'
];

$badgeType = isset($badgeType) && in_array($badgeType, $badgeTypes) ? $badgeType : 'info';

extract($displayData);
?>
<div class="card-tarea <?php echo !empty($class) ? $class : ''; ?>">
    <h1 class="text-info">
        <?php echo $title; ?>
        <?php if (!empty($titleInfo)) : ?>
            <i class="icon-info font-normal muted h3 hasTooltip" title="<?php echo $titleInfo; ?>"></i>
        <?php endif; ?>
    </h1>
    <?php if (!empty($badge)): ?>
        <p>
            <span class="label label-<?php echo $badgeType; ?>"><?php echo $badge; ?></span>
        </p>
    <?php endif; ?>
    <?php if (!empty($description)): ?>
        <p class="muted"><?php echo $description; ?></p>
    <?php endif; ?>
</div>