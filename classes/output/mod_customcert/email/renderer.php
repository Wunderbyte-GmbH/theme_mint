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
 * Email certificate as html renderer.
 *
 * @package    mod_customcert
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_mint\output\mod_customcert\email;

/**
 * Email certificate as html renderer.
 *
 * @package    mod_customcert
 * @copyright  2017 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderer extends \mod_customcert\output\email\renderer {

    /**
     * The template name for this renderer.
     *
     * @return string
     */
    public function get_template_name() {
        return 'email_certificate_html';
    }


    /**
     * Formats the email used to send the certificate by the email_certificate_task.
     *
     * @param  $certificate The certificate to email
     * @return string
     */
    public function render_email_certificate($certificate) {
        global $DB, $OUTPUT;
        $data = $certificate->export_for_template($this);

        $url = $data->emailcertificatelink;
        preg_match('/id=(\d+)/', $url, $matches);
        $cmid = $matches[1];
        $data->footer = $OUTPUT->image_url('Footermail', 'theme');
        $data->logo = $OUTPUT->image_url('Headermail', 'theme');
        $sql = "SELECT cm.instance, c.id AS courseid, c.fullname
        FROM {course_modules} cm
        JOIN {course} c ON cm.course = c.id
        WHERE cm.id = :cmid";
        $record = $DB->get_record_sql($sql, ['cmid' => $cmid]);

        $sql = "SELECT c.name FROM {customcert} c WHERE id = :instance";
        $cert = $DB->get_record_sql($sql, ['instance' => $record->instance]);
        $certname = $cert->name;
        if ($certname == 'Leistungsnachweis') {
//             $data->text = "Glückwunsch! 🎉 Du hast es geschafft!
// Du hast mindestens 60 % der Gesamtpunktzahl in $record->fullname erreicht.  Als Anerkennung kannst Du Dir jetzt Deinen Leistungsnachweis in Moodle herunterladen.
// Teile Deinen Erfolg mit Deinem Netzwerk, feiere Deine Leistung auf LinkedIn und inspiriere andere! 🚀";
            $data->buttonlabel = 'Leistungsnachweis herunterladen ';

        } else if ($certname == 'Teilnahmebestätigung') {
//             $data->text = "Glückwunsch! 🎉 Du hast es geschafft!
// Du hast mindestens 60 % der Gesamtpunktzahl in $record->fullname erreicht.  Als Anerkennung kannst Du Dir jetzt Deinen Leistungsnachweis in Moodle herunterladen.
// Teile Deinen Erfolg mit Deinem Netzwerk, feiere Deine Leistung auf LinkedIn und inspiriere andere! 🚀";
            $data->buttonlabel = 'Teilenahmebescheinigung herunterladen';

        }
        $data->footer = $OUTPUT->image_url('Footermail', 'theme');

        return $this->render_from_template('mod_customcert/' . $this->get_template_name(), $data);
    }

}
