<?php
$db = new PDO('sqlite:db.sqlite');

$group_id = (int)($_POST['group_id'] ?? 0);
$amount = max(1, (int)($_POST['amount'] ?? 1));
$abziehen = isset($_POST['abziehen']) && $_POST['abziehen'] == '1';

if ($abziehen) {
    // Punkte abziehen: letzte $amount Einträge löschen
    $stmt = $db->prepare("DELETE FROM scores WHERE id IN (
        SELECT id FROM scores WHERE group_id = ? ORDER BY id DESC LIMIT ?
    )");
    $stmt->execute([$group_id, $amount]);

    //echo "$amount Punkt(e) abgezogen!";
} else {
    // Punkte hinzufügen
    $stmt = $db->prepare("INSERT INTO scores (group_id) VALUES (?)");
    for ($i = 0; $i < $amount; $i++) {
        $stmt->execute([$group_id]);
    }

    //echo "$amount Punkt(e) hinzugefügt!";
}
