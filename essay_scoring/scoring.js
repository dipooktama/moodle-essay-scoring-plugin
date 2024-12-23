// scoring.js
function toggleCheckAll(source) {
    const checkboxes = document.getElementsByName('quizzes[]');
    for (let i = 0; i < checkboxes.length; i++) {
        checkboxes[i].checked = source.checked;
    }
}

function fetchScores() {
    const checkboxes = document.getElementsByName('quizzes[]');
    const selectedQuizzes = [];
    
    for (let i = 0; i < checkboxes.length; i++) {
        if (checkboxes[i].checked) {
            selectedQuizzes.push(checkboxes[i].value);
        }
    }

    if (selectedQuizzes.length === 0) {
        alert('Please select at least one quiz');
        return;
    }

    // Show loading, hide results
    document.getElementById('loading').style.display = 'block';
    document.getElementById('results').style.display = 'none';

    // Get the current URL (including courseid parameter)
    const currentUrl = window.location.href;

    // Create form data
    const formData = new FormData();
    formData.append('action', 'fetch_scores');
    formData.append('quizzes', JSON.stringify(selectedQuizzes));

    // Make the request
    fetch(currentUrl, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('results').innerHTML = html;
        document.getElementById('results').style.display = 'block';
    })
    .catch(error => {
        document.getElementById('loading').style.display = 'none';
        alert('Error fetching scores. Please try again.');
        console.error('Error:', error);
    });
}
