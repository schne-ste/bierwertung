<?php
session_start();
if (!($_SESSION['admin'] ?? false)) {
    header("Location: login.php");
    exit;
}

$db = new PDO('sqlite:db.sqlite');

// Neue Gruppe anlegen
if ($_POST['new_group'] ?? false) {
    $stmt = $db->prepare("INSERT INTO groups (name) VALUES (?)");
    $stmt->execute([$_POST['new_group']]);
}

// Punkte anpassen
if (isset($_POST['adjust_group'], $_POST['adjust_type'], $_POST['adjust_amount'])) {
    $group_id = (int)$_POST['adjust_group'];
    $amount = max(1, (int)$_POST['adjust_amount']);

    if ($_POST['adjust_type'] === 'add') {
        $stmt = $db->prepare("INSERT INTO scores (group_id) VALUES (?)");
        for ($i = 0; $i < $amount; $i++) {
            $stmt->execute([$group_id]);
        }
    } elseif ($_POST['adjust_type'] === 'remove') {
        $stmt = $db->prepare("DELETE FROM scores 
            WHERE id IN (
                SELECT id FROM scores WHERE group_id = ?
                ORDER BY timestamp DESC LIMIT ?
            )");
        $stmt->execute([$group_id, $amount]);
    }
}

// Gruppe löschen
if (isset($_POST['delete_group'])) {
    $group_id = (int)$_POST['delete_group'];
    $db->prepare("DELETE FROM scores WHERE group_id = ?")->execute([$group_id]);
    $db->prepare("DELETE FROM groups WHERE id = ?")->execute([$group_id]);
}

// Gruppen mit Punktestand abrufen
$groups = $db->query("
    SELECT g.*, COUNT(s.id) as points
    FROM groups g
    LEFT JOIN scores s ON g.id = s.group_id
    GROUP BY g.id
    ORDER BY g.name ASC
")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Bereich</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
		    font-family: sans-serif;
		    padding: 20px;
		    background: #f0f2f5;
		    color: #333;
		}
		
		h1, h2 {
		    color: #222;
		}
		
		form {
		    margin-bottom: 20px;
		}
		
		input[type="text"], input[type="number"] {
		    padding: 8px;
		    font-size: 1em;
		    border: 1px solid #ccc;
		    border-radius: 5px;
		}
		
		button {
		    padding: 8px 16px;
		    font-size: 1em;
		    border: none;
		    border-radius: 5px;
		    cursor: pointer;
		}
		
		button[name="adjust_type"][value="add"] {
		    background-color: #4caf50;
		    color: white;
		}
		
		button[name="adjust_type"][value="remove"] {
		    background-color: #ff9800;
		    color: white;
		}
		
		.delete-button {
		    background-color: #f44336;
		    color: white;
		}
		
		.group-box {
		    background: white;
		    padding: 15px;
		    border-radius: 10px;
		    margin-bottom: 20px;
		    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
		}
		
		.group-box form {
		    display: flex;
		    flex-wrap: wrap;
		    gap: 10px;
		    margin-top: 10px;
		    align-items: center;
		}
		
		@media (max-width: 600px) {
		    .group-box form {
		        flex-direction: column;
		        align-items: stretch;
		    }
		}

    </style>
    <script>
        function confirmDelete(groupName) {
            return confirm("Gruppe '" + groupName + "' wirklich löschen?");
        }
    </script>
</head>
<body>

<h1>Adminbereich</h1>
<p><a href="logout.php"> Logout</a></p>

<h2>Neue Gruppe anlegen</h2>
<form method="post">
    <input name="new_group" placeholder="Gruppenname" required>
    <button>Hinzufügen</button>
</form>

<h2>Gruppen & Punkte</h2>
<?php foreach ($groups as $g): ?>
<div class="group-box">
    <strong><?= htmlspecialchars($g['name']) ?>:</strong> <?= $g['points'] ?> Punkte
    <form method="post" onsubmit="return confirmDelete('<?= htmlspecialchars($g['name']) ?>')">
        <input type="hidden" name="delete_group" value="<?= $g['id'] ?>">
        <button class="delete-button">Gruppe löschen</button>
    </form>
    <form method="post">
        <input type="hidden" name="adjust_group" value="<?= $g['id'] ?>">
        <input type="number" name="adjust_amount" min="1" value="1" required>
        <button name="adjust_type" value="remove">– Abziehen</button>
        <button name="adjust_type" value="add">+ Hinzufügen</button>  
    </form>
</div>
<?php endforeach; ?>

</body>
</html>
