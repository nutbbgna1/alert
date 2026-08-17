<?php
require 'includes/db.php';
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
foreach($tables as $t) {
    echo $t . "\n";
    $cols = $pdo->query("DESCRIBE $t")->fetchAll();
    foreach($cols as $c) {
        echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
    }
}
