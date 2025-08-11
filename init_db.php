<?php
$db = new PDO('sqlite:db.sqlite');

// Bestehende Tabellen löschen (nur wenn neu aufgesetzt wird)
$db->exec("DROP TABLE IF EXISTS groups");
$db->exec("DROP TABLE IF EXISTS scores");

// Gruppen-Tabelle
$db->exec("CREATE TABLE IF NOT EXISTS groups (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL
)");

// Punktestand-Tabelle
$db->exec("CREATE TABLE IF NOT EXISTS scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    group_id INTEGER NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(group_id) REFERENCES groups(id)
)");

echo "Datenbankstruktur erfolgreich initialisiert.";
?>
