<?php
session_start();

// --- Database config ---
$host = 'localhost';
$dbname = 'yourdbname';
$user = 'username';
$pass = 'password';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // If DB connection fails, just skip PDO usage
    $pdo = null;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'get_flag') {
    if ($pdo) {
        // Try to get a random country
        $stmt = $pdo->query("SELECT id FROM flags ORDER BY RAND() LIMIT 1");
        $correct = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($correct) {
            $correct_id = $correct['id'];

            $stmt = $pdo->prepare("SELECT id, image_path, name FROM flags WHERE id = ?");
            $stmt->execute([$correct_id]);
            $correct_country = $stmt->fetch(PDO::FETCH_ASSOC);

            $stmt = $pdo->prepare("SELECT name FROM flags WHERE id != ? ORDER BY RAND() LIMIT 3");
            $stmt->execute([$correct_id]);
            $incorrect_countries = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if ($correct_country && count($incorrect_countries) === 3) {
                $options = $incorrect_countries;
                $options[] = $correct_country['name'];
                shuffle($options);

                $_SESSION['correct_country_name'] = $correct_country['name'];

                header('Content-Type: application/json');
                echo json_encode([
                    'image_path' => $correct_country['image_path'],
                    'options' => $options
                ]);
                exit;
            }
        }
    }

    // FALLBACK PLACEHOLDERS if DB or data missing
    $_SESSION['correct_country_name'] = 'Country 1';
    header('Content-Type: application/json');
    echo json_encode([
        'image_path' => 'placeholder-flag.png',  // Put any placeholder image path here
        'options' => ['Country 1', 'Country 2', 'Country 3', 'Country 4']
    ]);
    exit;
}

if ($action === 'submit_guess') {
    header('Content-Type: application/json');

    if (!isset($_POST['guess'])) {
        echo json_encode(['success' => false, 'message' => 'No guess submitted']);
        exit;
    }

    $guess = $_POST['guess'];
    $correct = $_SESSION['correct_country_name'] ?? null;

    if (!$correct) {
        echo json_encode(['success' => false, 'message' => 'Session expired, please reload']);
        exit;
    }

    if (!isset($_SESSION['score'])) {
        $_SESSION['score'] = 0;
    }

    if ($guess === $correct) {
        $_SESSION['score']++;
        echo json_encode(['correct' => true, 'score' => $_SESSION['score']]);
    } else {
        echo json_encode(['correct' => false, 'score' => $_SESSION['score']]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Flag Guessr Game</title>
</head>
<body>
    <div id="score">Score: 0</div>
    <main>
        <div>
            <img src="" alt="Flag" />
        </div>
        <div>
            <ul>
                <!-- options go here -->
            </ul>
        </div>
    </main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const flagImg = document.querySelector('main div img');
    const optionsList = document.querySelector('main ul');
    const scoreDisplay = document.getElementById('score');

    let score = 0;
    let waitingForNext = false;

    function loadFlag() {
        waitingForNext = false;
        fetch('?action=get_flag')
        .then(res => res.json())
        .then(data => {
            flagImg.src = data.image_path;
            flagImg.alt = 'Guess the flag';

            optionsList.innerHTML = '';

            data.options.forEach(option => {
                const li = document.createElement('li');
                li.textContent = option;
                li.style.cursor = 'pointer';
                li.style.color = ''; // reset color on new load

                li.addEventListener('click', () => {
                    if(waitingForNext) return; // prevent multi-click
                    submitGuess(option, li);
                });

                optionsList.appendChild(li);
            });
        })
        .catch(() => alert('Failed to load flag. Please refresh.'));
    }

    function submitGuess(selected, liElement) {
        fetch('?action=submit_guess', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'guess=' + encodeURIComponent(selected)
        })
        .then(res => res.json())
        .then(data => {
            if (data.correct) {
                score = data.score;
                scoreDisplay.textContent = 'Score: ' + score;
                liElement.style.color = 'green';
                waitingForNext = true;
                setTimeout(loadFlag, 1000);
            } else {
                liElement.style.color = 'red';
                alert('Wrong! Try again.');
                scoreDisplay.textContent = 'Score: ' + data.score;
            }
        })
        .catch(() => alert('Error submitting guess.'));
    }

    loadFlag();
});
</script>
</body>
</html>
