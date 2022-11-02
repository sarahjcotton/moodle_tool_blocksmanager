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

namespace tool_blocksmanager;

use tool_blocksmanager\table\region_list;

/**
 * Region manager class for manipulating with regions on the edit page.
 *
 * @package     tool_blocksmanager
 * @copyright   2019 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class region_controller extends base_controller {

    /**
     * {@inheritDoc}
     */
    protected function get_instance($id = 0, \stdClass $data = null) {
        return new region($id, $data);
    }

    /**
     * {@inheritDoc}
     */
    protected function get_form($instance) {
        global $PAGE;

        return new form\region_form($PAGE->url->out(false), ['persistent' => $instance]);
    }

    /**
     * {@inheritDoc}
     */
    protected function display_all_records() {
        $records = region::get_records();

        $table = new region_list();
        $table->display($records);
    }

    /**
     * {@inheritDoc}
     */
    protected function get_create_button_text() {
        return get_string('addregionlocking', 'tool_blocksmanager');
    }

    /**
     * {@inheritDoc}
     */
    protected function set_external_page() {
        admin_externalpage_setup('tool_blocksmanager/region');
    }

    /**
     * {@inheritDoc}
     */
    public static function get_base_url() {
        return '/admin/tool/blocksmanager/region.php';
    }

    /**
     * {@inheritDoc}
     */
    protected function get_view_heading() {
        return get_string('manageregionlocking', 'tool_blocksmanager');
    }

    /**
     * {@inheritDoc}
     */
    protected function get_new_heading() {
        return get_string('newregionlocking', 'tool_blocksmanager');
    }

    /**
     * {@inheritDoc}
     */
    protected function get_edit_heading() {
        return get_string('editregionlocking', 'tool_blocksmanager');
    }
}
