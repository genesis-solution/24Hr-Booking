<?php

/**
 * @package      Zap Calendar Library Framework
 *
 * @copyright   Copyright (C) 2006 - 2016 by Dan Cogliano
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 *
 * For more information, visit https://github.com/zcontent/icalendar
 */

// No direct access
defined('_ICALENDAR') or die( 'Restricted access' );

// set MAXYEAR to 2036 for 32 bit systems, can be higher for 64 bit systems
define('_ICALENDAR_MAXYEAR', 2036);

// set MAXREVENTS to maximum #of repeating events
define('_ICALENDAR_MAXREVENTS', 5000);

require_once(__DIR__ . '/date.php');
require_once(__DIR__ . '/recurringdate.php');
require_once(__DIR__ . '/ical.php');
require_once(__DIR__ . '/timezone.php');
