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
 * Theme functions.
 *
 * @package    theme_mint
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Post process the CSS tree.
 *
 * @param string $tree The CSS tree.
 * @param theme_config $theme The theme config object.
 */
function theme_mint_css_tree_post_processor($tree, $theme) {
    error_log('theme_boost_css_tree_post_processor() is deprecated. Required' .
        'prefixes for Bootstrap are now in theme/boost/scss/moodle/prefixes.scss');
    $prefixer = new theme_boost\autoprefixer($tree);
    $prefixer->prefix();
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_mint_get_extra_scss($theme) {
    $content = '';
    $imageurl = $theme->setting_file_url('backgroundimage', 'backgroundimage');

    // Sets the background image, and its settings.
    if (!empty($imageurl)) {
        $content .= '@media (min-width: 768px) {';
        $content .= 'body { ';
        $content .= "background-image: url('$imageurl'); background-size: cover;";
        $content .= ' } }';
    }

    // Sets the login background image.
    $loginbackgroundimageurl = $theme->setting_file_url('loginbackgroundimage', 'loginbackgroundimage');
    if (!empty($loginbackgroundimageurl)) {
        $content .= 'body.pagelayout-login #page { ';
        $content .= "background-image: url('$loginbackgroundimageurl'); background-size: cover;";
        $content .= ' }';
    }

    // Always return the background image with the scss when we have it.
    return !empty($theme->settings->scss) ? $theme->settings->scss . ' ' . $content : $content;
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course
 * @param stdClass $cm
 * @param context $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @param array $options
 * @return bool
 */
function theme_mint_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {
    if (
        $context->contextlevel == CONTEXT_SYSTEM && ($filearea === 'logo' || $filearea === 'backgroundimage' ||
        $filearea === 'loginbackgroundimage')
    ) {
        $theme = theme_config::load('boost');
        // By default, theme files must be cache-able by both browsers and proxies.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
    } else {
        send_file_not_found();
    }
}

/**
 * Returns the main SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_mint_get_main_scss_content(theme_config $theme): string {
    global $CFG;

    $boostscss = theme_boost_get_main_scss_content($theme);

    $fonts = file_get_contents($CFG->dirroot . '/theme/mint/scss/fonts.scss');
    $style = file_get_contents($CFG->dirroot . '/theme/mint/scss/styles.scss');
    $mobile = file_get_contents($CFG->dirroot . '/theme/mint/scss/mobile.scss');
    $modeswitch = file_get_contents($CFG->dirroot . '/theme/mint/scss/dark_mode_switch.scss');
    $darkmode = file_get_contents($CFG->dirroot . '/theme/mint/scss/dark_mode.scss');
    $lightmode = file_get_contents($CFG->dirroot . '/theme/mint/scss/light_mode.scss');
    $courseexplorer = file_get_contents($CFG->dirroot . '/theme/mint/scss/course_explorer.scss');
    $formatmintcampus = file_get_contents($CFG->dirroot . '/theme/mint/scss/format_mintcampus.scss');

    return $fonts . "\n" . $boostscss . "\n" . $style . "\n" . $mobile . "\n" . $modeswitch .
        "\n" . $darkmode . "\n" . $lightmode . "\n" . $courseexplorer . "\n" .
        "\n" . $formatmintcampus;
}

/**
 * Get compiled css.
 *
 * @return string compiled css
 */
function theme_mint_get_precompiled_css() {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/boost/style/moodle.css');
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_mint_get_pre_scss($theme) {
    global $CFG;

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['primary'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function ($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    return $scss;
}
