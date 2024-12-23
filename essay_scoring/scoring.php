<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');

// Get and validate course id
$courseid = required_param('courseid', PARAM_INT);

// Verify the course exists before proceeding
if (!$DB->record_exists('course', array('id' => $courseid))) {
    print_error('invalidcourseid', 'error');
}

// Get course context
$course = get_course($courseid);
$context = context_course::instance($courseid);

// Check if user is logged in and has access to this course
require_login($course);
require_capability('block/essay_scoring:view', $context);

// Get API endpoint from settings
$api_endpoint = get_config('block_essay_scoring', 'apiendpoint');
if (empty($api_endpoint)) {
    throw new moodle_exception('API endpoint not configured. Please configure it in the block settings.');
}

// Setup page
$PAGE->set_url(new moodle_url('/blocks/essay_scoring/scoring.php', array('courseid' => $courseid)));
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_title(get_string('essayscoring', 'block_essay_scoring'));
$PAGE->set_heading($course->fullname);

// Get all quizzes in the course
$quizzes = $DB->get_records('quiz', array('course' => $courseid));
$scoring_data = array();

foreach ($quizzes as $quiz) {
    // Get all attempts for this quiz
    $attempts = $DB->get_records('quiz_attempts', array('quiz' => $quiz->id, 'state' => 'finished'));
    
    foreach ($attempts as $attempt) {
        $user_data = array(
            'quiz_id' => $quiz->id,
            'user_id' => $attempt->userid,
            'context' => '', // Optional context field
            'questions_answers' => array()
        );
        
        // Get questions and answers for this attempt
        $sql = "SELECT 
                    qa.id,
                    q.questiontext as question,
                    qa.responsesummary as answer
                FROM {question_attempts} qa
                JOIN {question} q ON qa.questionid = q.id
                WHERE qa.questionusageid = ?";
        
        $questions = $DB->get_records_sql($sql, array($attempt->uniqueid));
        
        foreach ($questions as $question) {
            $user_data['questions_answers'][] = array(
                'question' => $question->question,
                'answer' => $question->answer
            );
        }
        
        $scoring_data[] = $user_data;
    }
}

// Debug output - comment out in production
echo $OUTPUT->header();
echo '<h2>' . get_string('essayscoring', 'block_essay_scoring') . '</h2>';

// Debug information
debugging('Course ID: ' . $courseid);
debugging('Number of quizzes found: ' . count($quizzes));
debugging('Scoring data: ' . print_r($scoring_data, true));

// Only make API call if we have data to send
if (!empty($scoring_data)) {
    // Make POST request to external scoring service
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $api_endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($scoring_data),
        CURLOPT_HTTPHEADER => array('Content-Type: application/json')
    ));

    $response = curl_exec($curl);
    $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($curl);
    curl_close($curl);

    // Error handling for API call
    if ($http_code !== 200 || !$response) {
        $error_message = "API call failed. ";
        if ($curl_error) {
            $error_message .= "Error: " . $curl_error;
        } else {
            $error_message .= "HTTP Code: " . $http_code;
        }
        debugging($error_message);
        echo '<div class="alert alert-danger">' . htmlspecialchars($error_message) . '</div>';
    } else {
        $scores = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '<div class="alert alert-danger">Invalid JSON response from API</div>';
        } else {
            // Display scores in a table
            echo '<table class="table">';
            echo '<thead><tr><th>Quiz</th><th>Student</th><th>Question</th><th>Score</th></tr></thead>';
            echo '<tbody>';

            foreach ($scores as $score) {
                $quiz = $DB->get_record('quiz', array('id' => $score['quiz_id']));
                $user = $DB->get_record('user', array('id' => $score['user_id']));
                
                foreach ($score['question_scores'] as $q_score) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($quiz->name) . '</td>';
                    echo '<td>' . htmlspecialchars(fullname($user)) . '</td>';
                    echo '<td>' . htmlspecialchars($q_score['question']) . '</td>';
                    echo '<td>' . htmlspecialchars($q_score['score']) . '</td>';
                    echo '</tr>';
                }
            }

            echo '</tbody></table>';
        }
    }
} else {
    echo '<div class="alert alert-info">No quiz data found for this course.</div>';
}

echo $OUTPUT->footer();
