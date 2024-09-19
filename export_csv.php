<?php
$conn = new mysqli('localhost', 'root', 'theooreo', 'test_cube_trois');

header('Content-Type: text/csv');
header('Content-Disposition: attachment;filename=factures.csv');

$output = fopen('php://output', 'w');
fputcsv($output, array('ID', 'Client', 'Date', 'Total', 'État'));

$result = $conn->query("SELECT * FROM factures");
while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);
?>
