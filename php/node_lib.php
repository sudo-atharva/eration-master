<?php
// Shared logic for the ESP32 ration node (api/node.php) and distributor/node.php.
date_default_timezone_set("Asia/Kolkata");

// tbl_stock.stock_id => hopper index on the node (must match HOPPER_PINS order in the ESP32 sketch).
const NODE_HOPPERS = [1 => 0, 2 => 1, 5 => 2]; // Wheat, Rice, Sugar
const NODE_SESSION_TTL = 600; // seconds an identified/approved session stays valid

function node_json($data) {
    header('Content-Type: application/json');
    exit(json_encode($data));
}

function node_since() {
    return date('Y-m-d H:i:s', time() - NODE_SESSION_TTL);
}

// Per-item monthly allowance for a ration card: quota = per-person quantity x family members.
function node_allowance($conn, $rc) {
    $rc = mysqli_real_escape_string($conn, $rc);
    $card = mysqli_fetch_assoc(mysqli_query($conn, "SELECT f_members FROM tbl_ration WHERE rationcard_no='$rc'"));
    $members = max(1, (int)($card['f_members'] ?? 1));
    $month = date('Y-m-01');
    $out = [];
    foreach (NODE_HOPPERS as $stock_id => $hopper) {
        $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_stock WHERE stock_id=$stock_id"));
        if (!$s) continue;
        $taken = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COALESCE(SUM(kg),0) AS t FROM tbl_dispense WHERE rationcard_no='$rc' AND stock_id=$stock_id AND date>='$month'"))['t'];
        $quota = $s['quantity'] * $members;
        $out[$stock_id] = [
            'stock_id' => $stock_id, 'name' => $s['stock_name'], 'hopper' => $hopper,
            'price' => (float)$s['stock_price'], 'market_price' => (float)$s['market_price'],
            'quota' => (float)$quota, 'taken' => round((float)$taken, 3),
            'remaining' => round(max(0, $quota - $taken), 3),
        ];
    }
    return $out;
}

// Split a dispensed weight into quota/extra and price it.
function node_bill($item, $kg) {
    $in_quota = min($kg, $item['remaining']);
    $extra = $kg - $in_quota;
    return [round($in_quota, 3), round($extra, 3), round($in_quota * $item['price'] + $extra * $item['market_price'], 2)];
}

// Cancel any open session on this shop's node so only one customer is active at a time.
function node_cancel_open($conn, $pds) {
    mysqli_query($conn, "UPDATE tbl_node_session SET status='cancelled' WHERE pds_no=$pds AND status IN ('identified','approved','enroll')");
}
