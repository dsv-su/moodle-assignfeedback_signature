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
 * This file contains the definition for the library class for comment feedback plugin
 *
 *
 * @package   assignfeedback_signature
 * @copyright 2012 Erik Lundberg
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die();

/**
 * library class for comment feedback plugin extending feedback plugin base class
 *
 * @copyright 2012 Erik Lundberg
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_feedback_signature extends assign_feedback_plugin {

   /**
    * Get the name of the signature feedback plugin
    * @return string
    */
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_signature');
    }

    /**
     * Get the signature from the database
     *
     * @param int $gradeid
     * @return stdClass|false The feedback signature for the given grade if it exists. False if it doesn't.
     */
    private function get_signature($gradeid) {
        global $DB;
        return $DB->get_field('assignfeedback_signature', 'signature', array('grade' => $gradeid));
    }

    /**
     * Get form elements for the grading page
     *
     * @param stdClass|null $grade
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool true if elements were added to the form
     */
    public function get_form_elements($grade, MoodleQuickForm $mform, stdClass $data) {
        global $DB, $PAGE;

		$PAGE->requires->js(new moodle_url('feedback/signature/lib/jquery-1.8.0.min.js'));
		$PAGE->requires->js(new moodle_url('feedback/signature/lib/jquery.signaturepad.min.js'));
		$PAGE->requires->js(new moodle_url('feedback/signature/lib/json2.min.js'));
		$PAGE->requires->js(new moodle_url('feedback/signature/lib/signature.js'));
        $signature = '';
        $html = '';

        if ($grade) {
            $grader = $DB->get_record('user', array('id' => $grade->grader));
            $gradername = fullname($grader);
            $mform->addElement('html', get_string('gradedby', 'assignfeedback_signature', $gradername));
            $signature = $this->get_signature($grade->id);
        } else {
            $html = html_writer::tag('a', get_string('clear'), array(
                'href' => '#clear',
                'class' => 'clearButton'
            ));
        }
        
        $html .= html_writer::start_tag('div', array('class' => 'sigWrapper'));
        $html .= html_writer::tag('canvas', $signature, array('width' => '500px', 'class' => 'pad'));
        $html .= html_writer::end_tag('div');
        
        $mform->addElement('html', $html);
        $mform->addElement('hidden', 'output', '', array('class' => 'output'));
        $mform->addRule('output', get_string('error'), 'required', '', 'client', false, false);

        return true;
    }

    /**
     * Saving the signature into the database
     *
     * @param stdClass $grade
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $grade, stdClass $data) {
        global $DB;
        $existingsignature = $DB->get_record('assignfeedback_signature', array('grade' => $grade->id));
        if ($existingsignature) {
            $existingsignature->signature = $data->output;
            return $DB->update_record('assignfeedback_signature', $existingsignature);
        } else {
            $signaturecomment = new stdClass();
		    $signaturecomment->signature = $data->output;
		    $signaturecomment->grade = $grade->id;
            $signaturecomment->assignment = $this->assignment->get_instance()->id;
            return $DB->insert_record('assignfeedback_signature', $signaturecomment) > 0;
        }
    }

    /**
     * display the comment in the feedback table
     *
     * @param stdClass $grade
     * @param bool $showviewlink Set to true to show a link to view the full feedback
     * @return string
     */
    public function view_summary(stdClass $grade, & $showviewlink) {
        global $PAGE, $DB;
        $signature = $DB->get_field('assignfeedback_signature', 'signature', array('grade' => $grade->id));
        $PAGE->requires->js(new moodle_url('feedback/signature/lib/jquery-1.8.0.min.js'));
	    $PAGE->requires->js(new moodle_url('feedback/signature/lib/jquery.signaturepad.min.js'));
	    $PAGE->requires->js(new moodle_url('feedback/signature/lib/json2.min.js'));
        $PAGE->requires->js(new moodle_url('feedback/signature/lib/signature_regenerate.js'));
    	
    	$html  = html_writer::start_tag('div', array('class' => 'sigPad signed'));
        $html .= html_writer::tag('canvas', $signature, array(
            'class' => 'pad',
            'width' => 183,
            'height' => 55
        ));
        $html .= html_writer::end_tag('div');
    	
        return $html;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        $DB->delete_records('assignfeedback_signature', array(
            'assignment' => $this->assignment->get_instance()->id
        ));
        return true;
    }

    /**
     * Returns true if there are no feedback comments for the given grade
     *
     * @param stdClass $grade
     * @return bool
     */
    public function is_empty(stdClass $grade) {
        return true;
    }
}
