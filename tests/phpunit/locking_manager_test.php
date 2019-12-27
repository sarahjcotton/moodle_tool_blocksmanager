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
 * Tests for locking manager class.
 *
 * @package     tool_blocksmanager
 * @copyright   2019 Catalyst IT
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class tool_blocksmanager_blocking_manager_testcase extends advanced_testcase {

    /**
     * Initial set up.
     */
    public function setUp() {
        $this->resetAfterTest();
    }

    /**
     * A helper function to create region locking rule.
     *
     * @param array $data Region data.
     * @return \tool_blocksmanager\region
     */
    protected function create_region_rule(array $data = []) {
        $region = new \tool_blocksmanager\region(0, (object)[
            'region' => !isset($data['region']) ? 'region1' : $data['region'],
            'categories' => !isset($data['categories']) ? '' : $data['categories'],
            'config' => !isset($data['config']) ? 0 : $data['config'],
            'delete' => !isset($data['delete']) ? 0 : $data['delete'],
            'hide' => !isset($data['hide']) ? 0 : $data['hide'],
            'add' => !isset($data['add']) ? 0 : $data['add'],
            'move' => !isset($data['move']) ? 0 : $data['move'],
        ]);
        $region->create();

        return $region;
    }

    /**
     * A helper function to create block locking rule.
     *
     * @param array $data Block data.
     * @return \tool_blocksmanager\block
     */
    protected function create_block_rule(array $data = []) {
        $block = new \tool_blocksmanager\block(0, (object)[
            'region' => !isset($data['region']) ? 'region1' : $data['region'],
            'block' => !isset($data['block']) ? 'block1' : $data['block'],
            'categories' => !isset($data['categories']) ? '' : $data['categories'],
            'config' => !isset($data['config']) ? 1 : $data['config'],
            'delete' => !isset($data['delete']) ? 1 : $data['delete'],
            'hide' => !isset($data['hide']) ? 1 : $data['hide'],
            'move' => !isset($data['move']) ? 1 : $data['move'],
        ]);
        $block->create();

        return $block;
    }

    /**
     * Test locking works trough the category children.
     */
    public function test_locked_in_child_category() {
        $category1 = $this->getDataGenerator()->create_category();
        $category11 = $this->getDataGenerator()->create_category(['parent' => $category1->id]);
        $category111 = $this->getDataGenerator()->create_category(['parent' => $category11->id]);
        $this->create_region_rule(['categories' => $category11->id, 'region' => 'region1']);

        $page = new moodle_page();
        $page->set_category_by_id($category1->id);
        $locking = new \tool_blocksmanager\locking_manager($page);
        $this->assertTrue($locking->can_move('block', 'region1'));
        $this->assertTrue($locking->can_hide('block', 'region1'));
        $this->assertTrue($locking->can_delete('block', 'region1'));
        $this->assertTrue($locking->can_move_out('block', 'region1'));
        $this->assertTrue($locking->can_move_in('block', 'region1'));
        $this->assertTrue($locking->can_configure('block', 'region1'));

        $page = new moodle_page();
        $page->set_category_by_id($category11->id);
        $locking = new \tool_blocksmanager\locking_manager($page);
        $this->assertFalse($locking->can_move('block', 'region1'));
        $this->assertFalse($locking->can_hide('block', 'region1'));
        $this->assertFalse($locking->can_delete('block', 'region1'));
        $this->assertFalse($locking->can_move_out('block', 'region1'));
        $this->assertFalse($locking->can_move_in('block', 'region1'));
        $this->assertFalse($locking->can_configure('block', 'region1'));

        $page = new moodle_page();
        $page->set_category_by_id($category111->id);
        $locking = new \tool_blocksmanager\locking_manager($page);
        $this->assertFalse($locking->can_move('block', 'region1'));
        $this->assertFalse($locking->can_hide('block', 'region1'));
        $this->assertFalse($locking->can_delete('block', 'region1'));
        $this->assertFalse($locking->can_move_out('block', 'region1'));
        $this->assertFalse($locking->can_move_in('block', 'region1'));
        $this->assertFalse($locking->can_configure('block', 'region1'));
    }

    /**
     * Test that block locking rules override region locking rules.
     */
    public function test_block_values_override_region() {
        $category = $this->getDataGenerator()->create_category();

        // Create region rule that locks everything.
        $this->create_region_rule(['region' => 'region1', 'categories' => $category->id]);
        $page = new moodle_page();
        $page->set_category_by_id($category->id);
        $locking = new \tool_blocksmanager\locking_manager($page);
        $this->assertFalse($locking->can_move('block', 'region1'));
        $this->assertFalse($locking->can_hide('block', 'region1'));
        $this->assertFalse($locking->can_delete('block', 'region1'));
        $this->assertFalse($locking->can_move_out('block', 'region1'));
        $this->assertFalse($locking->can_move_in('block', 'region1'));
        $this->assertFalse($locking->can_configure('block', 'region1'));

        // Create a block rule that allows everything.
        $this->create_block_rule(['region' => 'region1', 'block' => 'block1', 'categories' => $category->id]);
        $page = new moodle_page();
        $page->set_category_by_id($category->id);
        $locking = new \tool_blocksmanager\locking_manager($page);
        $this->assertTrue($locking->can_move('block1', 'region1'));
        $this->assertTrue($locking->can_hide('block1', 'region1'));
        $this->assertTrue($locking->can_delete('block1', 'region1'));
        $this->assertTrue($locking->can_move_out('block1', 'region2'));
        $this->assertFalse($locking->can_move_in('block1', 'region1')); // Because region is locked, can't add blocks.
        $this->assertTrue($locking->can_configure('block1', 'region1'));
    }

}
