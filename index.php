<?php
session_start();

// --- Database config ---
$host = 'localhost';
$dbname = 'flag_guessr_game';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    $pdo = null;
}

$action = $_POST['action'] ?? $_GET['action'] ?? null;

if ($action === 'get_flag') {
    header('Content-Type: application/json');

    if (!$pdo) {
        echo json_encode(['error' => 'Database connection failed.']);
        exit;
    }

    $stmt = $pdo->query("SELECT id FROM flags ORDER BY RAND() LIMIT 1");
    $correct = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$correct) {
        echo json_encode(['error' => 'No flags found in database.']);
        exit;
    }

    $correct_id = $correct['id'];

    $stmt = $pdo->prepare("SELECT id, image_path, name FROM flags WHERE id = ?");
    $stmt->execute([$correct_id]);
    $correct_country = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$correct_country) {
        echo json_encode(['error' => 'Flag details not found.']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT name FROM flags WHERE id != ? ORDER BY RAND() LIMIT 3");
    $stmt->execute([$correct_id]);
    $incorrect_countries = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($incorrect_countries) !== 3) {
        echo json_encode(['error' => 'Not enough countries in database for options.']);
        exit;
    }

    $options = $incorrect_countries;
    $options[] = $correct_country['name'];
    shuffle($options);

    $_SESSION['correct_country_name'] = $correct_country['name'];
    $_SESSION['attempts'] = 0; // Reset attempts

    echo json_encode([
        'image_path' => $correct_country['image_path'],
        'options' => $options
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

    if (!isset($_SESSION['attempts'])) {
        $_SESSION['attempts'] = 0;
    }

    $_SESSION['attempts']++;

    if ($guess === $correct) {
        $attempts = $_SESSION['attempts'];
        $points = 0;

        if ($attempts === 1) $points = 3;
        elseif ($attempts === 2) $points = 2;
        elseif ($attempts === 3) $points = 1;

        $_SESSION['score'] += $points;

        // Reset for next round
        unset($_SESSION['attempts']);
        unset($_SESSION['correct_country_name']);

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
    <title>Flag Guessr Game</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<main>
    <div class="score"><p id="score">Score: 0</p></div>
    <div class="image">
        <img src="" alt="Flag not available" id="flag-image" onerror="this.src='';" />
    </div>
    <div class="list">
        <ul></ul>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const flagImg = document.getElementById('flag-image');
    const optionsList = document.querySelector('main ul');
    const scoreDisplay = document.getElementById('score');

    let score = 0;
    let waitingForNext = false;

    function loadFlag() {
        waitingForNext = false;
        flagImg.src = '';
        optionsList.innerHTML = '';

        fetch('?action=get_flag')
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    console.error(data.error);
                    return;
                }

                flagImg.src = data.image_path;
                flagImg.alt = 'Guess the flag';

                data.options.forEach(option => {
                    const li = document.createElement('li');
                    li.textContent = option;
                    li.classList.add('option');

                    li.addEventListener('click', () => {
                        if (waitingForNext) return;
                        submitGuess(option, li);
                    });

                    optionsList.appendChild(li);
                });
            })
            .catch(() => {
                console.error('Failed to load flag.');
            });
    }

    function submitGuess(selected, liElement) {
        fetch('?action=submit_guess', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'guess=' + encodeURIComponent(selected)
        })
        .then(res => res.json())
        .then(data => {
            if (data.correct) {
                score = data.score;
                scoreDisplay.textContent = 'Score: ' + score;
                liElement.style.backgroundColor = '#a0e6a0'; // green
                waitingForNext = true;
                setTimeout(loadFlag, 1000);
            } else {
                liElement.style.backgroundColor = '#f7a7a7'; // red
                scoreDisplay.textContent = 'Score: ' + data.score;
            }
        })
        .catch(() => {
            console.error('Error submitting guess.');
        });
    }

    loadFlag();
});
</script>
</body>
</html>
