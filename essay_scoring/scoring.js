// scoring.js
function toggleCheckAll(source) {
    const checkboxes = document.getElementsByName('quizzes[]');
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}

async function loadStudent() {
    const quizCheckBoxes = getCheckBoxes('quizzes[]');
    console.log(quizCheckBoxes);
    if(quizCheckBoxes.length === 0) {
        alert('Please select at least one quiz');
        return;
    }
    const quizIds = quizCheckBoxes.map((val) => {
        return val.value
    });
    console.log(quizIds);
    console.log(quizIds.join(','));

    console.log("loading student...");
    const host = window.location.origin;
    let url = `${host}/blocks/essay_scoring/get_students.php?action=loadstudent&quiz=${quizIds.join(',')}`;
    // let things = "";
    // let res = await fetch(url);
    // let resText = await res.text();
    // console.log(resText);
    // console.log("student loaded");
    fetch(url).then(response => response.text())
        .then(html => {
            document.getElementById('student-list-container').innerHTML = html;
        })
        .catch(error => {
            console.error('Error: ', error);
            alert(error);
        });
}

function getCheckBoxes(elementName) {
    const checkboxes = document.getElementsByName(elementName);
    const selectedQuizzes = [];
    
    for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            selectedQuizzes.push(checkboxes[i]);
        }
    }

    return selectedQuizzes;
}

function fetchScores() {
    const studentCheckBoxes = getCheckBoxes('students[]');
    if(studentCheckBoxes.length === 0) {
        alert('Please select at least one student');
        return;
    }

    const studentIds = studentCheckBoxes.map((val) => {
        return val.value
    });

    console.log(studentCheckBoxes);
}
