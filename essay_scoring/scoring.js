// scoring.js
function toggleCheckAll(source) {
    const checkboxes = document.getElementsByName('quizzes[]');
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}

const dialog = document.getElementById('attribution');
const openDialogBtn = document.getElementById('openAttribution');
const closeDialogBtn = document.getElementById('closeAttribution');

openDialogBtn.addEventListener('click', () => {
    dialog.showModal();
});

closeDialogBtn.addEventListener('click', () => {
    dialog.close();
});

dialog.addEventListener('click', (event) => {
    const rect = dialog.getBoundingClientRect();
    if (
        event.clientX < rect.left ||
        event.clientX > rect.right ||
        event.clientY < rect.top ||
        event.clientY > rect.bottom
    ) {
        dialog.close();
    }
});

async function loadStudent() {
    const quizCheckBoxes = getCheckBoxes('quizzes[]');
    console.log(quizCheckBoxes);
    if (quizCheckBoxes.length === 0) {
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

function toggleScoreDetails(userId, quizId) {
    const detailsId = `details_${userId}_${quizId}`;
    const detailsElement = document.getElementById(detailsId);
    const iconElement = document.getElementById(`icon_${userId}_${quizId}`);
    
    if (detailsElement.style.display === 'none') {
        detailsElement.style.display = 'table-row';
        iconElement.classList.replace('fa-chevron-down', 'fa-chevron-up');
    } else {
        detailsElement.style.display = 'none';
        iconElement.classList.replace('fa-chevron-up', 'fa-chevron-down');
    }
}

async function fetchScores() {
    const studentCheckBoxes = getCheckBoxes('students[]');
    if (studentCheckBoxes.length === 0) {
        alert('Please select at least one student');
        return;
    }
    const studentIds = studentCheckBoxes.map((val) => {
        return val.value
    });
    let itemData = [];
    studentIds.forEach((id) => {
        const hiddenInput = document.getElementById("data_" + id);
        if (hiddenInput) {
            try {
                const data = hiddenInput.value;
                itemData.push(JSON.parse(data));
            } catch (e) {
                console.error('Error parsing JSON for ID:', id, e);
            }
        }
    });
    console.log("getting endpoint...");
    const host = window.location.origin;
    let url = `${host}/blocks/essay_scoring/get_endpoint.php`;
    let endpoint = await fetch(url).then(result => { return result.text(); });

    fetch(endpoint, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(itemData)
    })
        .then(response => response.json())  // Parse the JSON response
        .then(data => {
            console.log('Response data:', data);
            data.forEach(dt => {  // Note: forEach, not foreach
                const elementId = `${dt.user_id}_${dt.quiz_id}`;
                const element = document.getElementById(elementId);
                if (element) {
                    // Check if summary exists and has average_score
                    if (dt.summary && typeof dt.summary.average_score === 'number') {
                        element.value = dt.summary.average_score.toFixed(2);
                    }

                    // Update individual question scores
                    if (dt.results) {
                        dt.results.forEach((result, index) => {
                            const scoreElement = document.getElementById(`score_${elementId}_${index}`);
                            if (scoreElement && typeof result.score === 'number') {
                                scoreElement.textContent = result.score.toFixed(2);
                            }
                        });
                    }
                } else {
                    console.log(`Element with ID ${elementId} not found`);
                }
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while generating scores');
        });
}
