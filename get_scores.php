<?php
$db = new PDO('sqlite:db.sqlite');

$sql = "
SELECT g.name, COUNT(s.id) as points
FROM groups g
LEFT JOIN scores s ON g.id = s.group_id
GROUP BY g.id
ORDER BY points DESC
";

$data = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($data);
