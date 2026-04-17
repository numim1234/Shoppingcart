<?php
// Usage: php dump_product_names.php <slip_id>
if (php_sapi_name() !== 'cli') {
    echo "Run via CLI\n";
    exit(1);
}
$id = isset($argv[1]) ? (int)$argv[1] : 0;
if ($id <= 0) {
    echo "Usage: php dump_product_names.php <slip_id>\n";
    exit(1);
}
require_once __DIR__ . '/../condb.php';
try {
    $stmt = $conn->prepare("SELECT sd.p_id, p.p_name FROM tbl_sale_detail sd LEFT JOIN tbl_product p ON sd.p_id = p.p_id WHERE sd.slip_id = ?");
    $stmt->execute([$id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!$rows) {
        echo "No rows for slip {$id}\n";
        exit(0);
    }
    foreach ($rows as $r) {
        $name = $r['p_name'] ?? '';
        echo "p_id: {$r['p_id']}\n";
        echo "p_name (raw): ";
        // print hex bytes and UTF-8 repr
        $bytes = unpack('C*', $name);
        foreach ($bytes as $b) printf('%02x ', $b);
        echo "\n";
        echo "p_name (as-is): " . $name . "\n\n";
    }
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage() . "\n";
}
