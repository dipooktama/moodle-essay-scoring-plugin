<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

// Get and validate course id
$courseid = required_param('courseid', PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);

// Verify the course exists
if (!$DB->record_exists('course', array('id' => $courseid))) {
    print_error('invalidcourseid', 'error');
}

// Setup page
$course = get_course($courseid);
$context = context_course::instance($courseid);
require_login($course);

$PAGE->set_url(new moodle_url('/blocks/essay_scoring/scoring.php', array('courseid' => $courseid)));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('essayscoring', 'block_essay_scoring'));
$PAGE->set_heading($course->fullname);

// Add necessary JavaScript
$PAGE->requires->js('/blocks/essay_scoring/scoring.js');

echo $OUTPUT->header();
echo '<h2>' . get_string('essayscoring', 'block_essay_scoring') . '</h2>';

// Display quiz selection form
$quizzes = $DB->get_records('quiz', array('course' => $courseid));

if (empty($quizzes)) {
    echo '<div class="alert alert-info">No quizzes found in this course.</div>';
} 
else {
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
    
    echo '<button type="button" class="btn btn-primary" onclick="fetchScores()">Check Scores</button>';
    echo '</form>';
    
    echo '<div id="loading" style="display: none;" class="mt-3">';
    echo '<div class="alert alert-info">Loading scores...</div>';
    echo '</div>';
    
    echo '<div id="results" class="mt-3"></div>';
}

echo $OUTPUT->footer();

if ($action === 'fetch_scores') {
    return json_decode("hehe");
}
