<?php
// ESP32 ration node API. Every request: POST JSON {"key": "<node_key>", "action": "...", ...}
//   identify {finger_id}                 -> fingerprint matched on sensor; opens a session for the distributor
//   poll                                 -> {job: null | "dispense" | "enroll", ...}
//   done     {session_id, grams: [g0..]} -> actual grams per hopper; bills and records the dispense
//   enrolled {session_id, slot, ok}      -> result of fingerprint enrollment
include "../php/connection.php";
include "../php/node_lib.php";

$in = json_decode(file_get_contents('php://input'), true) ?: [];
$key = mysqli_real_escape_string($conn, (string)($in['key'] ?? ''));
$node = mysqli_fetch_assoc(mysqli_query($conn, "SELECT pds_no FROM tbl_node WHERE node_key='$key'"));
if ($key === '' || !$node) {
    http_response_code(401);
    node_json(['ok' => false, 'msg' => 'Bad node key']);
}
$pds = (int)$node['pds_no'];
$sid = (int)($in['session_id'] ?? 0);
$now = date('Y-m-d H:i:s');

switch ($in['action'] ?? '') {
    case 'identify':
        $fid = (int)($in['finger_id'] ?? -1);
        $map = mysqli_fetch_assoc(mysqli_query($conn, "SELECT rationcard_no FROM tbl_fingerprint WHERE pds_no=$pds AND finger_id=$fid"));
        if (!$map) node_json(['ok' => false, 'msg' => 'Finger not registered']);
        $rc = mysqli_real_escape_string($conn, $map['rationcard_no']);
        node_cancel_open($conn, $pds);
        mysqli_query($conn, "INSERT INTO tbl_node_session (pds_no, rationcard_no, status, items, created) VALUES ($pds, '$rc', 'identified', '', '$now')");
        $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fname, lname FROM tbl_user WHERE rationcard_no='$rc'"));
        node_json(['ok' => true, 'name' => trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? ''))]);

    case 'poll':
        $since = node_since();
        $s = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT * FROM tbl_node_session WHERE pds_no=$pds AND status IN ('approved','enroll') AND created>='$since' ORDER BY id DESC LIMIT 1"));
        if (!$s) node_json(['ok' => true, 'job' => null]);
        if ($s['status'] === 'enroll') {
            // ponytail: next slot = highest used + 1; reuse freed slots if the sensor (127-1000 slots) fills up.
            $slot = 1 + (int)mysqli_fetch_assoc(mysqli_query($conn, "SELECT COALESCE(MAX(finger_id),0) AS m FROM tbl_fingerprint WHERE pds_no=$pds"))['m'];
            node_json(['ok' => true, 'job' => 'enroll', 'session_id' => (int)$s['id'], 'slot' => $slot]);
        }
        // Hand the job over once, so a rebooted node never dispenses twice.
        mysqli_query($conn, "UPDATE tbl_node_session SET status='dispensing' WHERE id={$s['id']}");
        $grams = [0, 0, 0];
        foreach (json_decode($s['items'], true) as $it) $grams[$it['hopper']] = (int)round($it['kg'] * 1000);
        node_json(['ok' => true, 'job' => 'dispense', 'session_id' => (int)$s['id'], 'grams' => $grams]);

    case 'done':
        $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_node_session WHERE id=$sid AND pds_no=$pds AND status='dispensing'"));
        if (!$s) node_json(['ok' => false, 'msg' => 'No such dispense job']);
        $rc = mysqli_real_escape_string($conn, $s['rationcard_no']);
        $grams = $in['grams'] ?? [];
        $today = date('Y-m-d');
        $total = 0;
        foreach (node_allowance($conn, $s['rationcard_no']) as $item) {
            $kg = max(0, (float)($grams[$item['hopper']] ?? 0)) / 1000;
            if ($kg <= 0) continue;
            [$quota_kg, $extra_kg, $amount] = node_bill($item, $kg);
            $total += $amount;
            $name = mysqli_real_escape_string($conn, $item['name']);
            mysqli_query($conn, "INSERT INTO tbl_dispense (session_id, rationcard_no, pds_no, stock_id, stock_name, kg, quota_kg, extra_kg, amount, date)
                VALUES ($sid, '$rc', $pds, {$item['stock_id']}, '$name', $kg, $quota_kg, $extra_kg, $amount, '$today')");
            mysqli_query($conn, "UPDATE tbl_available_stock SET quantity=quantity-$kg WHERE pds_no=$pds AND stock_id={$item['stock_id']}");
        }
        mysqli_query($conn, "UPDATE tbl_node_session SET status='done' WHERE id=$sid");
        node_json(['ok' => true, 'amount' => round($total, 2)]);

    case 'enrolled':
        $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_node_session WHERE id=$sid AND pds_no=$pds AND status='enroll'"));
        if (!$s) node_json(['ok' => false, 'msg' => 'No such enroll job']);
        $slot = (int)($in['slot'] ?? 0);
        if (empty($in['ok']) || $slot <= 0) {
            mysqli_query($conn, "UPDATE tbl_node_session SET status='failed' WHERE id=$sid");
            node_json(['ok' => true]);
        }
        $rc = mysqli_real_escape_string($conn, $s['rationcard_no']);
        mysqli_query($conn, "DELETE FROM tbl_fingerprint WHERE pds_no=$pds AND finger_id=$slot");
        mysqli_query($conn, "INSERT INTO tbl_fingerprint (pds_no, finger_id, rationcard_no) VALUES ($pds, $slot, '$rc')");
        mysqli_query($conn, "UPDATE tbl_node_session SET status='enrolled' WHERE id=$sid");
        node_json(['ok' => true]);
}
node_json(['ok' => false, 'msg' => 'Unknown action']);
