<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->libdir.'/adminlib.php');

// Get and validate course id
$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

// Verify the course exists
if (!$DB->record_exists('course', array('id' => $courseid))) {
    print_error('invalidcourseid', 'error');
}

$apiendpoint = get_config('block_essay_scoring', 'apiendpoint');
$loadstudenttext = get_string('loadstudent', 'block_essay_scoring');

// Setup page
$course = get_course($courseid);
$context = context_course::instance($courseid);
require_login($course);

$PAGE->set_url(new moodle_url('/blocks/essay_scoring/scoring.php', array('courseid' => $courseid)));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('essayscoring', 'block_essay_scoring'));
$PAGE->set_heading($course->fullname);
$PAGE->requires->js("/blocks/essay_scoring/scoring.js");

$quizzes = $DB->get_records('quiz', array('course' => $courseid));

// Start page output
echo $OUTPUT->header();

if (empty($quizzes)) {
    echo '<div class="alert alert-info">No quizzes found in this course.</div>';
} else {
    echo '<form id="quiz-selection-form">';
    echo '<div class="mb-3">';
    echo '<label><input type="checkbox" onclick="toggleCheckAll(this)"> Select All</label>';
    echo '</div>';
    
    echo '<div class="mb-3">';
    foreach ($quizzes as $quiz) {
        echo '<div class="form-check">';
        echo '<input class="form-check-input" type="checkbox" name="quizzes[]" value="' . $quiz->id . '" id="quiz' . $quiz->id . '">';
        echo '<label class="form-check-label" for="quiz' . $quiz->id . '">' . htmlspecialchars($quiz->name) . '</label>';
        echo '</div>';
    }
    echo '</div>';
    
    echo '<button type="button" class="btn btn-primary" onclick="loadStudent()">'.$loadstudenttext.'</button>';
    echo '</form>';
}

echo '<div id="student-list-container"></div>';
echo $OUTPUT->footer();
