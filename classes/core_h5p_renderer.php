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
 * Extending h5p renderer and adding css to it
 * @package    theme_mint
 * @copyright
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 o
 */
class theme_mint_core_h5p_renderer extends \core_h5p\output\renderer {

    /**
     * Alter which stylesheets are loaded for H5P.
     * This is useful for adding custom styles or replacing existing ones.
     *
     * @param array|object $scripts List of stylesheets that will be loaded
     * @param array $libraries Array of libraries indexed by the library's machineName
     * @param string $embedtype Possible values: div, iframe, external, editor
     */
    public function h5p_alter_styles(&$styles, $libraries, $embedtype) {
        global $CFG;
        $styles[] = (object)array(
            'path' => $CFG->wwwroot . '/theme/mint/h5p/h5p_overrides.css',
            'version' => '',
        );
    }
}
