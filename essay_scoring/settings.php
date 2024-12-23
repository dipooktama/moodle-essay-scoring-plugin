<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     block_essay_scoring
 * @category    admin
 * @copyright   2024 dipo <dipooktama@usu.ac.id>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$settings->add(new admin_setting_configtext(
    'block_essay_scoring/apiendpoint',
    get_string('apiendpoint', 'block_essay_scoring'),
    get_string('apiendpoint_desc', 'block_essay_scoring'),
    '',
    PARAM_URL
));

/*


if ($hassiteconfig) {
    # $settings = new admin_settingpage('block_essay_scoring_settings', new lang_string('pluginname', 'block_essay_scoring'));

    // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedIf
    if ($ADMIN->fulltree) {
        $settings->add(new admin_setting_configtext(
            'block_essay_scoring/apiendpoint',
            get_string('apiendpoint', 'block_essay_scoring'),
            get_string('apiendpoint_desc', 'block_essay_scoring'),
            '',
            PARAM_URL
        ));
    }
}
 */
