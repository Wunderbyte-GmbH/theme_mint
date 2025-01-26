<?php

namespace theme_mint\output;

use moodle_url;
use theme_mint\output\core_course\activity_navigation;

defined('MOODLE_INTERNAL') || die;

class core_renderer extends \theme_boost\output\core_renderer
{
    const DEFAULT_MODE = 'light';
    /**
     * @throws \moodle_exception
     * @throws \coding_exception
     */
    public function standard_head_html()
    {
        global $PAGE;

        if (isset($_SESSION['currentmode']) && $_SESSION['currentmode']) {
            $currentmode = $_SESSION['currentmode'];
        } else {
            $currentmode = self::DEFAULT_MODE;
            $_SESSION['currentmode'] = $currentmode;
        }
        $targetedmode = $currentmode === 'light' ? 'dark' : 'light';

        $output = "
            <script type=\"text/javascript\">
                let currentMode = '$currentmode';
                let targetedMode = '$targetedmode';
            </script>";
        $PAGE->requires->js(new \moodle_url('/theme/mint/javascript/dark_mode_switch.js'));

        ## Disabling standard MyCourses page
        $path = $PAGE->__get('url')->get_path();
        if ($path === '/my/courses.php') {
            $url = new \moodle_url('/my/');
            redirect($url);
        }

        if ($path === '/' && !CLI_SCRIPT && !AJAX_SCRIPT) {
            $url = new \moodle_url('/my/'); // WordPress landing page
            redirect($url);
        }

        ## redirect for database activity (mintcampusactivityheader is not rendered)
        $bodyattributes = $this->body_attributes();
        $pattern = '/\bcmid-(\d+)\b/';
        if (strpos($bodyattributes, 'path-mod-data') !== false && preg_match($pattern, $bodyattributes, $matches)) {
            $cmid = $matches[1];
            $url = new \moodle_url("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

            if ($url->get_path() === '/mod/data/view.php'
                && ($url->param('id') != $cmid
                || $url->param('perpage') <= 1)
                && $url->param('sesskey') === null
                && count($url->params()) > 0
            ) {
                $url->remove_all_params();
                $url->param('id', $cmid);
                $url->param('perpage', 1000);
                redirect($url);
            }
        }

        $output .= parent::standard_head_html();
        return $output;
    }

    /**
     * Returns standard navigation between activities in a course.
     *
     * @return string the navigation HTML.
     */
    public function activity_navigation() {
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        $course = $this->page->cm->get_course();
        $courseformat = course_get_format($course);

        // Get a list of all the activities in the course.
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }
            // Module URL.
            $linkurl = new moodle_url($module->url, array('forceview' => 1));
            // Add module URL (as key) and name (as value) to the activity list array.
            $activitylist[$linkurl->out(false)] = $modname;
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        // Check if we have a previous mod to show.
        if ($position > 0) {
            $prevmod = $mods[$modids[$position - 1]];
        }

        // Check if we have a next mod to show.
        if ($position < ($nummods - 1)) {
            $nextmod = $mods[$modids[$position + 1]];
        }

        $activitynav = new activity_navigation($prevmod, $nextmod, $activitylist);
        $renderer = $this->page->get_renderer('core', 'course');
        return $renderer->render($activitynav);
    }
}