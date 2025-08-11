<?php
$db = new PDO('sqlite:db.sqlite');

$groups = $db->query("
    SELECT g.*, COUNT(s.id) as points
    FROM groups g
    LEFT JOIN scores s ON g.id = s.group_id
    GROUP BY g.id
    ORDER BY points DESC, g.name ASC
")->fetchAll();

$maxPoints = 0;
if (!empty($groups)) {
    $maxPoints = max(array_column($groups, 'points'));
}

$timestamp = date('d.m.Y H:i:s');
?>

<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <title>Bierwertung – Druckansicht</title>
  <style>
    * {
      box-sizing: border-box;
    }

    html, body {
      font-family: sans-serif;
      background: white;
      color: black;
      margin: 0;
      padding: 0;
      height: 100%;
      width: 100%;
    }

    body {
      padding: 20mm;
      position: relative;
      font-size: 11pt;
    }

    h1 {
      text-align: center;
      font-size: 24pt;
      margin-bottom: 20px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      table-layout: fixed;
      font-size: 11pt;
      border: 2px solid #000; /* Gesamt-Rahmen */
    }

    th, td {
      padding: 8px 12px;
      border: 1px solid #000;
      text-align: left;
      word-wrap: break-word;
    }

    th {
      background-color: #e0e0e0;
      color: #000;
    }

    .winner {
      background-color: #ffeb3b !important; /* gut sichtbares Gelb */
      font-weight: bold;
    }

    .footer {
      position: absolute;
      bottom: 10mm;
      right: 20mm;
      font-size: 9pt;
      color: #555;
    }

    @media print {
      html, body {
        height: 100%;
        width: 100%;
      }

      .footer {
        position: fixed;
        bottom: 10mm;
        right: 20mm;
      }

      /* Explizit Farben und Rahmen auch im Druck */
      table, th, td {
        border: 1px solid black !important;
        color-adjust: exact; /* Safari/Chrome */
        -webkit-print-color-adjust: exact; /* Chrome */
        print-color-adjust: exact; /* Firefox */
      }

      .winner {
        background-color: #ffeb3b !important;
        -webkit-print-color-adjust: exact;
      }

      th {
        background-color: #e0e0e0 !important;
        -webkit-print-color-adjust: exact;
      }
    }
  </style>
</head>
<body>

<h1>Bierwertung</h1>

<table>
  <thead>
    <tr>
      <th style="width: 70%;">Gruppe</th>
      <th style="width: 30%;">Punkte</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($groups as $g): ?>
    <tr class="<?= $g['points'] == $maxPoints && $maxPoints > 0 ? 'winner' : '' ?>">
      <td><?= htmlspecialchars($g['name']) ?></td>
      <td><?= $g['points'] ?> <?= $g['points'] == 1 ? 'Punkt' : 'Punkte' ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<div class="footer">
  Gedruckt am <?= $timestamp ?>
</div>

<script>
  window.onload = () => {
    const isMobile = /Mobi|Android|iPhone|iPad|iPod/i.test(navigator.userAgent);

    if (!isMobile) {
      window.print();
    }
  };
</script>

</body>
</html>
