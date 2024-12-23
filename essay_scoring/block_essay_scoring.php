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
 * Block essay_scoring is defined here.
 *
 * @package     block_essay_scoring
 * @copyright   2024 dipo <dipooktama@usu.ac.id>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_essay_scoring extends block_base {

    /**
     * Initializes class member variables.
     */
    public function init() {
        // Needed by Moodle to differentiate between blocks.
        $this->title = get_string('pluginname', 'block_essay_scoring');
    }

    /**
     * Returns the block contents.
     *
     * @return stdClass The block contents.
     */
    public function get_content() {
        global $COURSE;

        if ($this->content !== null) {
            return $this->content;
        }

        /*
        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }
         */

        $this->content = new stdClass();
        $url = new moodle_url('/blocks/essay_scoring/scoring.php', ['courseid'=> $COURSE->id]);

        if (!empty($this->config->text)) {
            $this->content->text = $this->config->text;
        } else {
            this->content->text = html_writer::link(
                $url, 
                get_string('viewscoring', 'block_essay_scoring'), 
                ['class' => 'btn btn-primary']
            );
        }
        $this->content->footer = 'PSI USU';

        return $this->content;
    }

    /**
     * Defines configuration data.
     *
     * The function is called immediately after init().
     */
    public function specialization() {

        // Load user defined title and make sure it's never empty.
        if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_essay_scoring');
        } else {
            $this->title = $this->config->title;
        }
    }

    /**
     * Enables global configuration of the block in settings.php.
     *
     * @return bool True if the global configuration is enabled.
     */
    public function has_config() {
        return true;
    }

    /**
     * Sets the applicable formats for the block.
     *
     * @return string[] Array of pages and permissions.
     */
    public function applicable_formats() {
        return array(
            'all' => false,
            'course-view' => true,
            'course-view-social' => false,
        );
    }
}
