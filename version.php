<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * Defines the version of uniljournal
 *
 * This code fragment is called by moodle_needs_upgrading() and
 * /admin/index.php
 *
 * @package    mod_uniljournal
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2015022000;
$plugin->requires  = 2014050800; // Moodle 2.7
$plugin->maturity  = MATURITY_BETA;
$plugin->release   = '1.0 BETA for Moodle 2.7-2.8 (Build: 2015022000)';
$plugin->component = 'mod_uniljournal'; // To check on upgrade, that module sits in correct place.
$plugin->cron      = 0; // Period for cron to check this module (secs).

