<?php
$db = new PDO('sqlite:db.sqlite');
$groups = $db->query("
    SELECT g.*, COUNT(s.id) as points
    FROM groups g
    LEFT JOIN scores s ON g.id = s.group_id
    GROUP BY g.id
    ORDER BY g.name ASC
")->fetchAll();

// Theme aus URL holen (default: 'dark')
$theme = $_GET['theme'] ?? 'dark';
$theme = ($theme === 'light') ? 'light' : 'dark';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Bierwertung</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body.light {
            background: #f0f0f0;
            color: #000;
            font-family: sans-serif;
        }
        body.dark {
            background: #222;
            color: #fff;
            font-family: sans-serif;
        }
        h1 {
            margin: 0;
            display: inline-block;
        }
        .back-button-top {
            margin-top: 0;
        }
        .back-button-bottom {
            display: block;
            margin: 40px auto 20px auto;
            text-align: center;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .group {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
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
            color: white;
        }
        .back {
            background: #666;
        }
    </style>
</head>
<body class="<?= $theme ?>">

<!-- Zurück-Button oben -->
<div class="header">
  <h1>Bierwertung</h1>
  <button class="back-button-top back" onclick="history.back()">← Zurück</button>
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

<!-- Zurück-Button unten -->
<button class="back-button-bottom back" onclick="history.back()">← Zurück</button>

<script>
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

    if (!confirm(confirmMessage)) return;

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
    // Input-Werte zurücksetzen
    document.querySelectorAll('input[name="amount"]').forEach(input => {
        input.value = 1;
    });
});
</script>

</body>
</html>
