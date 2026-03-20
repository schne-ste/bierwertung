<?php
$db = new PDO('sqlite:db.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$group_id = (int)($_POST['group_id'] ?? 0);
$amount = max(1, (int)($_POST['amount'] ?? 1));
$abziehen = isset($_POST['abziehen']) && $_POST['abziehen'] === '1';

if ($group_id <= 0) {
    echo "Ungültige Gruppen-ID.";
    exit;
}

// Punktestand aktuell ermitteln (Anzahl der Einträge)
$stmt = $db->prepare("SELECT COUNT(*) FROM scores WHERE group_id = ?");
$stmt->execute([$group_id]);
$currentPoints = (int)$stmt->fetchColumn();

if ($abziehen) {
    if ($currentPoints < $amount) {
        echo "Es sind nur $currentPoints Punkt(e) vorhanden, aber du möchtest $amount abziehen.";
        exit;
    }

    // Letzte $amount Einträge löschen
    $stmt = $db->prepare("
        DELETE FROM scores 
        WHERE id IN (
            SELECT id FROM scores WHERE group_id = ? ORDER BY id DESC LIMIT ?
        )
    ");
    $stmt->execute([$group_id, $amount]);
    $change = -$amount;

    //echo "$amount Punkt(e) abgezogen.";
} else {
    // Punkte hinzufügen - jeweils ein Eintrag pro Punkt
    $stmt = $db->prepare("INSERT INTO scores (group_id) VALUES (?)");
    for ($i = 0; $i < $amount; $i++) {
        $stmt->execute([$group_id]);
    }
	 $change = $amount;
    //echo "$amount Punkt(e) hinzugefügt.";
}



// Logeintrag erstellen
try {
    $stmt = $db->prepare("INSERT INTO score_log (group_id, change, comment) VALUES (?, ?, ?)");
    $comment = $abziehen ? "Punkte abgezogen" : "Punkte hinzugefügt";
    $stmt->execute([$group_id, $change, $comment]);
} catch (PDOException $e) {
    echo "Fehler beim Speichern des Logs: " . $e->getMessage();
    exit;
}
