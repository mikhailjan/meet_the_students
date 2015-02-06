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
 * Meet the Students block
 *
 * @package   block_meet_the_students
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class block_meet_the_students extends block_base {
    
    function init() {
        $this->title = get_string('pluginname', 'block_meet_the_students');
    }

    function has_config() {
        return true;
    }

    function applicable_formats() {
        return array('all' => true);
    }

    function specialization() {
        $this->title = isset($this->config->title) ? format_string($this->config->title) : format_string(get_string('pluginname', 'block_meet_the_students'));
    }

    function instance_allow_multiple() {
        return true;
    }

    function get_content() {
        global $CFG;
        global $PAGE;
        global $OUTPUT;
        global $USER;

        require_once($CFG->libdir . '/filelib.php');

        if ($this->content !== NULL) {
            return $this->content;
        }

        // Get block settings or defaults
        $config = get_config('block_meet_the_students'); // defaults
        $onlywithrole = isset($this->config->onlywithrole) ? $this->config->onlywithrole : $config->onlywithrole;
        $onlywithpic = isset($this->config->onlywithpic) ? $this->config->onlywithpic : $config->onlywithpic;
        $numcolumns = (isset($this->config->numcolumns) ? $this->config->numcolumns : $config->numcolumns) + 1;
        $numrows = (isset($this->config->numrows) ? $this->config->numrows : $config->numrows) + 1;
        $maxusers = $numcolumns * $numrows;
        $width = ' style="width:'.round(100/$numcolumns, 2).'%;"';

        // Get the users to display 
        $context = context_course::instance($PAGE->course->id);
         // Only with profile pictures

        if($onlywithrole>0) {
           $users = get_role_users($onlywithrole,$context);
        }
        else{
            $users = get_enrolled_users($context);
        }       
        // Remove own profile
        unset($users[$USER->id]);

        // Only with profile pictures
        if($onlywithpic) {
            $tempusers = array();
            foreach ($users as $value) {
                if($value->picture != '0') {
                    $tempusers[] = $value;
                }
            }
            $users = $tempusers;
        }

        // Order by last access
        usort($users, function ($a, $b) {
            if ($a->lastaccess == $b->lastaccess) {
                return 0;
            }
            else {
                return ($a->lastaccess < $b->lastaccess) ? 1 : -1;
            }
        });
        
        // Render block contents
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->text .= '<div class="meet_the_students">';

        $numusers = count($users);
        for ($i = 0; $i < $maxusers && $i < $numusers; $i++) {
            
            $this->content->text .= '<div class="user_icon" '.$width.'><div class="user_margin">';
            $this->content->text .= $OUTPUT->user_picture($users[$i], array('size' => 100, 'class' => 'user_picture'));
            $this->content->text .= '</div></div>';
        }

        $this->content->text .= '</div>';
        $this->content->footer = '<a href="/user/index.php?contextid='.$context->id.'"><img src="'.$OUTPUT->pix_url('i/users').'" class="icon" alt="">'.get_string('meetall', 'block_meet_the_students').'</a>';
        return $this->content;
    }

}


