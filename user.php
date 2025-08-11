<?php
$db = new PDO('sqlite:db.sqlite');
$groups = $db->query("
    SELECT g.*, COUNT(s.id) as points
    FROM groups g
    LEFT JOIN scores s ON g.id = s.group_id
    GROUP BY g.id
    ORDER BY g.name ASC
")->fetchAll();

$theme = $_GET['theme'] ?? 'dark';
$theme = ($theme === 'light') ? 'light' : 'dark';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bierwertung</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: sans-serif;
            box-sizing: border-box;
        }

        body.light {
            background: #f0f0f0;
            color: #000;
        }

        body.dark {
            background: #222;
            color: #fff;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            margin: 0;
            display: inline-block;
        }

        .back-button-top,
        .back-button-bottom {
            display: none;
        }

        /* Nur auf kleinen Bildschirmen zeigen */
        @media (max-width: 767px) {
            .back-button-top,
            .back-button-bottom {
                display: block;
                margin: 20px 0;
                background: #666;
                color: #fff;
                padding: 10px 20px;
                border: none;
                border-radius: 8px;
                cursor: pointer;
                text-align: center;
            }
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .group-list {
            display: grid;
            gap: 20px;
            grid-template-columns: repeat(1, 1fr);
        }

        @media (min-width: 768px) {
            .group-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1200px) {
            .group-list {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .group {
            padding: 15px;
            border-radius: 10px;
        }

        body.dark .group {
            background: #333;
        }

        body.light .group {
            background: #fff;
            border: 1px solid #ccc;
        }

        .group h2 {
            margin: 0 0 5px;
            font-size: 1.5em;
        }

        .points {
            font-size: 1em;
            margin-bottom: 10px;
            display: block;
        }

        body.dark .points {
            color: #aaa;
        }

        body.light .points {
            color: #333;
        }

        input[type="number"] {
            width: 60px;
            font-size: 1.2em;
            padding: 5px;
            border-radius: 6px;
        }

        button {
            font-size: 1.2em;
            padding: 10px 20px;
            margin-left: 10px;
            cursor: pointer;
            background: #007acc;
            color: white;
            border-radius: 8px;
            border: none;
        }

        button.remove {
            background: #cc0000;
        }

        .back {
            background: #666;
        }
    </style>
</head>
<body class="<?= $theme ?>">
<div class="container">

    <!-- Zurück-Button oben (nur mobil sichtbar) -->
    <div class="header">
        <h1>Bierwertung</h1>
        <button class="back-button-top" onclick="history.back()">← Zurück</button>
        <!--<button class="theme-toggle" onclick="toggleTheme()">Dark / ️Light</button>-->
    </div>

    <p><strong>Punkte wenn:</strong></p>
    <ul>
        <li><b>1/2 Bier</b> - 1 Punkt</li>
        <li><b>2 Seiterl Bier</b> - 1 Punkt</li>
        <li><b>1/2 Sommerspritzer</b> - 1 Punkt</li>
        <li><b>1/4 Spritzer</b> - 1 Punkt</li>
    </ul>

    <ul>
        <li><b>Doppler Bier</b> - 4 Punkte</li>
        <li><b>Doppler Sommerspritzer</b> - 4 Punkte</li>
        <li><b>Doppler Spritzer</b> - 8 Punkte</li>
    </ul>

    <div class="group-list">
        <?php foreach ($groups as $g): ?>
        <div class="group" id="group-<?= $g['id'] ?>">
            <h2><?= htmlspecialchars($g['name']) ?></h2>
            <span class="points"><?= $g['points'] ?> <?= $g['points'] == 1 ? 'Punkt' : 'Punkte' ?></span>
            <form onsubmit="addPoints(event, <?= $g['id'] ?>, false)">
                <input type="number" name="amount" min="1" value="1" required>
                <button type="button" class="remove" onclick="addPoints(event, <?= $g['id'] ?>, true)"> – </button>
                <button> + </button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Zurück-Button unten (nur mobil sichtbar) -->
    <button class="back-button-bottom" onclick="history.back()">← Zurück</button>

</div> <!-- Ende .container -->

<script>
const confirmModus = "abziehen";  //            <----------------- HIER EINSTELLEN (wann eine Popup-Meldung erscheinen soll)

function addPoints(e, groupId, remove = false) {
    e.preventDefault();

    const form = e.target.form || e.target;
    const amount = parseInt(form.amount.value, 10);

    if (isNaN(amount) || amount < 1) {
        alert("Bitte gib eine gültige Anzahl Punkte ein.");
        return;
    }

    const action = remove ? "abziehen" : "hinzufügen";
    const confirmMessage = `Bist du sicher, dass du ${amount} Punkt${amount === 1 ? '' : 'e'} ${action} möchtest?`;

    const brauchtBestaetigung =
        confirmModus === "immer" ||
        (confirmModus === "hinzufügen" && !remove) ||
        (confirmModus === "abziehen" && remove);

    if (brauchtBestaetigung && !confirm(confirmMessage)) {
        return;
    }

    sessionStorage.setItem('scrollPos', window.scrollY);

    const params = new URLSearchParams();
    params.append('group_id', groupId);
    params.append('amount', amount);
    if (remove) params.append('abziehen', '1');

    fetch('add_point.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: params.toString()
    })
    .then(response => response.text())
    .then(message => {
        if (message.trim() !== '') {
            alert(message);
        }
        location.reload();
    });
}

window.addEventListener('load', () => {
    const scrollPos = sessionStorage.getItem('scrollPos');
    if (scrollPos !== null) {
        window.scrollTo(0, parseInt(scrollPos));
        sessionStorage.removeItem('scrollPos');
    }

    document.querySelectorAll('input[name="amount"]').forEach(input => {
        input.value = 1;
    });
});
</script>

</body>
</html>
