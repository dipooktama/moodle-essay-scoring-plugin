<?php

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/quiz/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$action = required_param('action', PARAM_ALPHA);
$quiz_ids = required_param('quiz', PARAM_RAW);

if ($action == 'loadstudent') {
    $quiz_array = explode(',', $quiz_ids);
    $students = array();

    $sql =
        "WITH ranked_steps AS (
            SELECT
                qas.questionattemptid,
                qas.fraction,
                ROW_NUMBER() OVER (
                    PARTITION BY qas.questionattemptid
                    ORDER BY 
                        CASE 
                            WHEN qas.state = 'gradedright' THEN 1
                            WHEN qas.state = 'gradedwrong' THEN 2
                            ELSE 3 
                        END
                ) AS rank
            FROM mdl_question_attempt_steps AS qas
            WHERE qas.state IN ('gradedright', 'gradedwrong')
        )
        SELECT 
            qa.id AS id,
            qa.questionsummary AS question_text,
            qa.responsesummary AS student_answer,
            qa.rightanswer AS context,
            qat.id as quiz_attempt_id,
            qa.questionusageid as question_usage_id,
            qat.quiz AS quiz_id,
            u.id AS user_id,
            q.name AS quiz_name,
            u.firstname AS firstname,
            u.lastname AS lastname,
            (rs.fraction * qs.maxmark) AS grade
        FROM mdl_question_attempts AS qa 
        JOIN mdl_quiz_slots AS qs ON qs.id = qa.slot 
        JOIN mdl_quiz_attempts AS qat ON qa.questionusageid = qat.id
        JOIN mdl_quiz AS q ON qat.quiz = q.id
        JOIN mdl_user AS u ON qat.userid = u.id 
        JOIN ranked_steps AS rs ON rs.questionattemptid = qa.id AND rs.rank = 1
        WHERE qat.quiz IN (" . implode(',', array_map('intval', $quiz_array)) . ")
        ORDER BY qat.id, u.username, qa.slot;";
    /*
    $sql =
        "SELECT 
            qa.id AS id,
            qa.questionsummary AS question_text,
            qa.responsesummary AS student_answer,
            qa.rightanswer AS context,
            qat.id as quiz_attempt_id,
            qa.questionusageid as question_usage_id,
            qat.quiz AS quiz_id,
            u.id AS user_id,
            q.name AS quiz_name,
            u.firstname AS firstname,
            u.lastname AS lastname,
            qas.state AS graded_state,
            (qas.fraction * qs.maxmark) AS grade
        FROM 
            mdl_question_attempts AS qa 
        JOIN 
            mdl_quiz_slots AS qs ON qs.slot = qa.slot 
        JOIN 
            mdl_quiz_attempts AS qat ON qa.questionusageid = qat.id
        JOIN 
            mdl_quiz AS q ON qat.quiz = q.id
        JOIN 
            mdl_user AS u ON qat.userid = u.id 
        JOIN 
            mdl_question_attempt_steps AS qas ON qas.questionattemptid = qa.id
        WHERE 
            qat.quiz IN (" . implode(',', array_map('intval', $quiz_array)) . ")
        AND 
            qas.state IN ('gradedright', 'gradedwrong')
        ORDER BY 
            qat.id, u.username, qa.slot;";
*/

    $queryResults = $DB->get_records_sql($sql);

    if (empty($queryResults)) {
        echo '<div class="alert alert-info">No students found for the selected quizzes.</div>';
        die();
    }

    function processQuizResults(array $results): array
    {
        $processed = [];

        foreach ($results as $result) {
            $key = $result->user_id . '_' . $result->quiz_id;

            // Initialize group if doesn't exist
            if (!isset($processed[$key])) {
                $processed[$key] = [
                    'user_id' => $result->user_id,
                    'firstname' => $result->firstname,
                    'lastname' => $result->lastname,
                    'quiz_id' => $result->quiz_id,
                    'quiz_name' => $result->quiz_name,
                    'grade' => $result->grade,
                    'questions' => [],
                    'datapass' => [
                        'quiz_id' => $result->quiz_id,
                        'user_id' => $result->user_id,
                        'context' => '',
                        'questions_answers' => []
                    ]
                ];
            }

            // Add question data
            $question = [
                'question' => $result->question_text,
                'answer' => $result->student_answer,
                'context' => $result->context
            ];

            // Update main questions array
            $processed[$key]['questions'][] = $question;

            // Update datapass simultaneously
            $processed[$key]['datapass']['questions_answers'][] = [
                'question' => $result->question_text,
                'answer' => $result->student_answer
            ];

            // Append context (with proper spacing)
            $processed[$key]['datapass']['context'] = trim(
                $processed[$key]['datapass']['context'] . " \n " . $result->context
            );
        }

        return $processed;
    }

    // Group results by student and quiz
    $grouped_results = processQuizResults($queryResults);

    // Output the student list
    echo '<div class="mt-4">
            <h4>' . get_string('studentlist', 'block_essay_scoring') . '</h4>
            <div class="mb-3">
                <label><input type="checkbox" onclick="toggleStudentCheckAll(this)"> ' . get_string('selectall', 'block_essay_scoring') . '</label>
            </div>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th></th>
                        <th>' . get_string('student', 'block_essay_scoring') . '</th>
                        <th>' . get_string('quiz', 'block_essay_scoring') . '</th>
                        <th>' . get_string('scorecurrent', 'block_essay_scoring') . '</th>
                        <th>' . get_string('scoregenerated', 'block_essay_scoring') . '</th>
                    </tr>
                </thead>
                <tbody>';

    foreach ($grouped_results as $key => $result) {
        // Create hidden input with JSON-encoded question and answer data
        $qa_data = htmlspecialchars(json_encode($result['datapass']), ENT_QUOTES, 'UTF-8');
        // $tes_data = htmlspecialchars(json_encode($result), ENT_QUOTES, 'UTF-8');
        $id = $result['user_id'] . '_' . $result['quiz_id'];

        echo '<tr>
                <td><input type="checkbox" name="students[]" value="' . $id . '"></td>
                <td>' . $result['firstname'] . ' ' . $result['lastname'] . '</td>
                <td>' . $result['quiz_name'] . '</td>
                <td>' . ($result['grade'] !== null ? format_float($result['grade'], 2) : '-') . '</td>
                <td>
                    <input type="text" class="generated-score" id="' . $id . '" value="0">
                </td>
                <input type="hidden" 
                    class="qa-data" 
                    id="data_' . $id . '" 
                    value="' . $qa_data . '">
              </tr>';
    }

    echo '</tbody>
        </table>
        <div class="mt-3">
            <button type="button" class="btn btn-primary" onclick="fetchScores()">' . get_string('generatescore', 'block_essay_scoring') . '</button>
        </div>
        <div id="loading" style="display: none;">
            <div class="d-flex justify-content-center">
                <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>';


    $res_data = htmlspecialchars(json_encode($queryResults), ENT_QUOTES, 'UTF-8');
    echo '<p>' . $res_data . '</p>';

    // Add JavaScript for toggling student checkboxes
    echo '<script>
        function toggleStudentCheckAll(source) {
            const checkboxes = document.getElementsByName("students[]");
            for (let i = 0; i < checkboxes.length; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>';
} else {
    echo '<p>Error</p>';
}
