<?php
$db = new PDO('sqlite:db.sqlite');

$selectedGroupId = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

// Gruppen für Dropdown holen
$groups = $db->query("SELECT id, name FROM groups ORDER BY name ASC")->fetchAll();

// Logs je nach Filter laden
if ($selectedGroupId > 0) {
    $stmt = $db->prepare("
        SELECT l.*, g.name AS group_name
        FROM score_log l
        JOIN groups g ON l.group_id = g.id
        WHERE l.group_id = ?
        ORDER BY l.timestamp DESC
    ");
    $stmt->execute([$selectedGroupId]);
    $logs = $stmt->fetchAll();
} else {
    $logs = $db->query("
        SELECT l.*, g.name AS group_name
        FROM score_log l
        JOIN groups g ON l.group_id = g.id
        ORDER BY l.timestamp DESC
    ")->fetchAll();
}

// Aktueller Punktestand je Gruppe (immer alle anzeigen)
$points = $db->query("
    SELECT g.id, g.name, COUNT(s.id) AS current_points
    FROM groups g
    LEFT JOIN scores s ON s.group_id = g.id
    GROUP BY g.id
    ORDER BY current_points DESC, g.name ASC
")->fetchAll();

/*
echo "<pre>Selected Group ID: $selectedGroupId\n";
echo "Logs:\n";
var_dump($logs);
echo "</pre>";*/

?>

<!DOCTYPE html>
<html>
<head>
    <title>Punktestand-Protokoll</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 40px; }
        th, td { padding: 8px 12px; border: 1px solid #ccc; }
        th { background: #eee; }
        .positive { color: green; }
        .negative { color: red; }
        h2 { margin-top: 40px; }
        select {
            font-size: 1em;
            padding: 5px 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<h1>Bierwertung - Protokoll</h1>
<h2>Aktueller Punktestand je Gruppe</h2>

<table>
    <thead>
        <tr>
            <th>Gruppe</th>
            <th>Aktueller Punktestand</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($points as $group): ?>
        <tr>
            <td><?= htmlspecialchars($group['name']) ?></td>
            <td><?= (int)$group['current_points'] ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h1>Punktestand-Protokoll</h1>

<form method="get" id="filterForm">
    <label for="groupSelect">Verlauf filtern nach Gruppe:</label>
    <select name="group_id" id="groupSelect" onchange="document.getElementById('filterForm').submit()">
        <option value="0" <?= $selectedGroupId === 0 ? 'selected' : '' ?>>Alle anzeigen</option>
        <?php foreach ($groups as $group): ?>
            <option value="<?= $group['id'] ?>" <?= $selectedGroupId === (int)$group['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($group['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<table>
    <thead>
        <tr>
            <th>Datum / Uhrzeit</th>
            <th>Gruppe</th>
            <th>Änderung</th>
            <th>Kommentar</th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($logs) === 0): ?>
        <tr>
            <td colspan="4" style="text-align:center;">Keine Einträge für diese Gruppe.</td>
        </tr>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['timestamp']) ?></td>
                <td><?= htmlspecialchars($log['group_name']) ?></td>
                <td class="<?= $log['change'] > 0 ? 'positive' : 'negative' ?>">
                    <?= $log['change'] > 0 ? '+' : '' ?><?= $log['change'] ?>
                </td>
                <td><?= htmlspecialchars($log['comment']) ?></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>



</body>
</html>
