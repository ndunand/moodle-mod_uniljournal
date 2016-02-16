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
 * Capability definitions for the uniljournal module
 *
 * The capabilities are loaded into the database table when the module is
 * installed or updated. Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 *
 * The system has four possible values for a capability:
 * CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
 *
 * It is important that capability names are unique. The naming convention
 * for capabilities that are specific to modules and blocks is as follows:
 *   [mod/block]/<plugin_name>:<capabilityname>
 *
 * component_name should be the same as the directory name of the mod or block.
 *
 * Core moodle capabilities are defined thus:
 *    moodle/<capabilityclass>:<capabilityname>
 *
 * Examples: mod/forum:viewpost
 *           block/recent_activity:view
 *           moodle/site:deleteuser
 *
 * The variable name for the capability definitions array is $capabilities
 *
 * @package    mod_uniljournal
 * @copyright  2014-2015  Université de Lausanne
 * @author     Liip AG eLearning Team <elearning@liip.ch>
 * @author     Didier Raboud <didier.raboud@liip.ch>
 * @author     Claude Bossy <claude.bossy@liip.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = ['mod/uniljournal:addinstance'     => ['riskbitmask'          => RISK_XSS, 'captype' => 'write',
                                                       'contextlevel'         => CONTEXT_COURSE,
                                                       'archetypes'           => ['editingteacher' => CAP_ALLOW,
                                                                                  'manager'        => CAP_ALLOW],
                                                       'clonepermissionsfrom' => 'moodle/course:manageactivities'],

                 'mod/uniljournal:view'            => ['captype' => 'read', 'contextlevel' => CONTEXT_MODULE,
                                                       'legacy'  => ['guest' => CAP_ALLOW, 'student' => CAP_ALLOW,
                                                                     'teacher' => CAP_ALLOW,
                                                                     'editingteacher' => CAP_ALLOW,
                                                                     'manager' => CAP_ALLOW]],

                 'mod/uniljournal:createarticle'   => ['riskbitmask'  => RISK_SPAM, 'captype' => 'write',
                                                       'contextlevel' => CONTEXT_MODULE,
                                                       'archetypes'   => ['student' => CAP_ALLOW]],

                 'mod/uniljournal:deletearticle'   => ['captype'    => 'write', 'contextlevel' => CONTEXT_MODULE,
                                                       'archetypes' => ['student' => CAP_ALLOW]],

                 'mod/uniljournal:editallarticles' => ['captype'    => 'write', 'contextlevel' => CONTEXT_MODULE,
                                                       'archetypes' => ['teacher'        => CAP_ALLOW,
                                                                        'editingteacher' => CAP_ALLOW,
                                                                        'manager'        => CAP_ALLOW],],

                 'mod/uniljournal:viewallarticles' => ['captype'    => 'read', 'contextlevel' => CONTEXT_MODULE,
                                                       'archetypes' => ['teacher'        => CAP_ALLOW,
                                                                        'editingteacher' => CAP_ALLOW,
                                                                        'manager'        => CAP_ALLOW],],

                 'mod/uniljournal:managetemplates' => ['riskbitmask'          => RISK_XSS, 'captype' => 'write',
                                                       'contextlevel'         => CONTEXT_COURSE,
                                                       'archetypes'           => ['editingteacher' => CAP_ALLOW,
                                                                                  'manager'        => CAP_ALLOW],
                                                       'clonepermissionsfrom' => 'moodle/course:manageactivities'],

                 'mod/uniljournal:managethemes'    => ['riskbitmask'          => RISK_XSS, 'captype' => 'write',
                                                       'contextlevel'         => CONTEXT_SYSTEM,
                                                       'archetypes'           => ['editingteacher' => CAP_ALLOW,
                                                                                  'manager'        => CAP_ALLOW],
                                                       'clonepermissionsfrom' => 'moodle/course:manageactivities'],

                 'mod/uniljournal:addcomment'      => ['riskbitmask'  => RISK_SPAM, 'captype' => 'write',
                                                       'contextlevel' => CONTEXT_MODULE,
                                                       'archetypes'   => ['student'        => CAP_ALLOW,
                                                                          'editingteacher' => CAP_ALLOW,
                                                                          'manager'        => CAP_ALLOW]],

                 'mod/uniljournal:deletecomment'   => ['captype'    => 'write', 'contextlevel' => CONTEXT_MODULE,
                                                       'archetypes' => ['editingteacher' => CAP_ALLOW,
                                                                        'manager'        => CAP_ALLOW]],];
