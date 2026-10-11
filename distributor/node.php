<?php
session_start();
include '../php/config.php';
if (!isset($_SESSION['rationcard_no'])) {
    header("location: ../Auth/login.php");
    exit;
}
include '../php/connection.php';
include '../php/node_lib.php';
include '../php/customer_lib.php';
$rcard_no = mysqli_real_escape_string($conn, $_SESSION['rationcard_no']);
$distributor = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_distributor WHERE rationcard_no='$rcard_no'"));
if (!$distributor) {
    header("location: ../Auth/login.php");
    exit;
}
$pds = (int)$distributor['pds_no'];

// ---- AJAX actions ----
$action = $_GET['action'] ?? '';
if ($action === 'state') {
    $since = node_since();
    $s = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT * FROM tbl_node_session WHERE pds_no=$pds AND created>='$since' ORDER BY id DESC LIMIT 1"));
    if (!$s) node_json(['session' => null]);
    $rc = mysqli_real_escape_string($conn, $s['rationcard_no']);
    $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fname, lname, image FROM tbl_user WHERE rationcard_no='$rc'"));
    $card = mysqli_fetch_assoc(mysqli_query($conn, "SELECT f_members, card_type FROM tbl_ration WHERE rationcard_no='$rc'"));
    $out = ['session' => [
        'id' => (int)$s['id'], 'status' => $s['status'], 'rationcard_no' => $s['rationcard_no'],
        'name' => trim(($user['fname'] ?? '') . ' ' . ($user['lname'] ?? '')),
        'image' => $user['image'] ?? 'male.png', 'members' => (int)($card['f_members'] ?? 1), 'card_type' => $card['card_type'] ?? '-',
    ]];
    if ($s['status'] === 'identified') $out['allowance'] = array_values(node_allowance($conn, $s['rationcard_no']));
    if ($s['status'] === 'done') {
        $out['results'] = mysqli_fetch_all(mysqli_query($conn,
            "SELECT stock_name, kg, quota_kg, extra_kg, amount FROM tbl_dispense WHERE session_id={$s['id']}"), MYSQLI_ASSOC);
    }
    node_json($out);
}
if ($action === 'dispense') {
    $in = json_decode(file_get_contents('php://input'), true) ?: [];
    $sid = (int)($in['session_id'] ?? 0);
    $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tbl_node_session WHERE id=$sid AND pds_no=$pds AND status='identified'"));
    if (!$s) node_json(['ok' => false, 'msg' => 'Customer session expired. Ask them to scan again.']);
    $items = [];
    foreach (node_allowance($conn, $s['rationcard_no']) as $stock_id => $item) {
        $kg = round((float)($in['kg'][$stock_id] ?? 0), 3);
        if ($kg < 0 || $kg > 50) node_json(['ok' => false, 'msg' => "Invalid amount for {$item['name']}"]);
        if ($kg > 0) $items[] = ['stock_id' => $stock_id, 'hopper' => $item['hopper'], 'kg' => $kg];
    }
    if (!$items) node_json(['ok' => false, 'msg' => 'Enter an amount for at least one item.']);
    $json = mysqli_real_escape_string($conn, json_encode($items));
    mysqli_query($conn, "UPDATE tbl_node_session SET status='approved', items='$json' WHERE id=$sid");
    node_json(['ok' => true]);
}
if ($action === 'cancel') {
    node_cancel_open($conn, $pds);
    node_json(['ok' => true]);
}
if ($action === 'enroll') {
    $rc = mysqli_real_escape_string($conn, $_POST['rationcard_no'] ?? '');
    if (!mysqli_num_rows(mysqli_query($conn, "SELECT u_id FROM tbl_user WHERE rationcard_no='$rc'")))
        node_json(['ok' => false, 'msg' => 'Customer not found']);
    node_cancel_open($conn, $pds);
    $now = date('Y-m-d H:i:s');
    mysqli_query($conn, "INSERT INTO tbl_node_session (pds_no, rationcard_no, status, items, created) VALUES ($pds, '$rc', 'enroll', '', '$now')");
    node_json(['ok' => true]);
}

if ($action === 'add_customer') {
    $err = add_customer($conn, $_POST, $distributor);
    node_json($err ? ['ok' => false, 'msg' => $err] : ['ok' => true]);
}

// ---- Page ----
$pincode = (int)$distributor['pincode'];
$customers = mysqli_fetch_all(mysqli_query($conn,
    "SELECT u.rationcard_no, u.fname, u.lname, (SELECT COUNT(*) FROM tbl_fingerprint f WHERE f.pds_no=$pds AND f.rationcard_no=u.rationcard_no) AS fingers
     FROM tbl_user u WHERE u.pincode=$pincode ORDER BY u.fname"), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title> Ration Node | E-Ration </title>
    <link rel="stylesheet" href="style.css?v=<?= $v ?>">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    .panel { background: #fff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,.12); padding: 24px; margin-bottom: 24px; }
    .panel h2 { margin: 0 0 16px; font-size: 22px; color: #0a2558; }
    .who { display: flex; gap: 16px; align-items: center; margin-bottom: 16px; }
    .who img { width: 72px; height: 72px; border-radius: 50%; object-fit: cover; }
    .node-table { width: 100%; border-collapse: collapse; }
    .node-table th, .node-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; }
    .node-table input { width: 90px; padding: 6px; }
    .extra { color: #c0392b; font-weight: 600; }
    .btn { background: #0a2558; color: #fff; border: 0; border-radius: 8px; padding: 10px 20px; cursor: pointer; margin-right: 8px; }
    .btn.grey { background: #888; }
    .waiting { font-size: 18px; color: #555; }
    .total { font-size: 22px; font-weight: 700; margin-top: 16px; }
    select { padding: 8px; min-width: 260px; }
    </style>
</head>

<body>
    <div class="sidebar">
        <div class="logo-details">
            <img src="./images/logo2.png" height="40px" style="margin-left: 10px">
            <img src="./images/logo3.png" height="40px">
        </div>
        <ul class="nav-links">
            <li><a href="dist_dash.php"><i class='bx bx-grid-alt'></i><span class="links_name">Dashboard</span></a></li>
            <li><a href="#" class="active"><i class='bx bx-fingerprint'></i><span class="links_name">Ration Node</span></a></li>
            <li><a href="avail_stock.php"><i class='bx bx-coin-stack'></i><span class="links_name">Check Available Stock</span></a></li>
            <li><a href="customer.php"><i class='bx bx-user'></i><span class="links_name">Customers</span></a></li>
            <li class="log_out"><a href="logout.php"><i class='bx bx-log-out'></i><span class="links_name">Log out</span></a></li>
        </ul>
    </div>
    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class='bx bx-menu sidebarBtn'></i>
                <span class="dashboard">Ration Node</span>
            </div>
            <div class="profile-details">
                <img src="<?= "../uploads_images/" . htmlspecialchars($distributor['image']) ?>" alt="">
                <span class="distributor_name"><?= htmlspecialchars($distributor['fname'] . " " . $distributor['lname']) ?></span>
            </div>
        </nav>

        <div class="home-content" style="display:block;padding:0 20px">
            <div class="panel">
                <h2>Customer at node</h2>
                <div id="live" class="waiting">Loading…</div>
            </div>

            <div class="panel">
                <h2>Enroll fingerprint</h2>
                <select id="enrollCard">
                    <?php foreach ($customers as $c) { ?>
                    <option value="<?= htmlspecialchars($c['rationcard_no']) ?>"<?= ($_GET['new'] ?? '') === $c['rationcard_no'] ? ' selected' : '' ?>>
                        <?= htmlspecialchars($c['fname'] . ' ' . $c['lname'] . ' — ' . $c['rationcard_no'] . ($c['fingers'] ? " ({$c['fingers']} enrolled)" : '')) ?>
                    </option>
                    <?php } ?>
                </select>
                <button class="btn" onclick="enroll()">Enroll on node</button>
            </div>

            <div class="panel">
                <h2>Add customer</h2>
                <form id="addForm" onsubmit="addCustomer(event)">
                    <?php customer_form_fields(); ?>
                    <button class="btn">Add customer</button>
                </form>
            </div>
        </div>
    </section>

    <script>
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".sidebarBtn");
    sidebarBtn.onclick = function() {
        sidebar.classList.toggle("active");
        if (sidebar.classList.contains("active")) sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
        else sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
    }

    const live = document.getElementById('live');
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    let shown = '';   // session id + status currently rendered; avoids wiping inputs while typing
    let allowance = [];

    function who(s) {
        return `<div class="who"><img src="../uploads_images/${esc(s.image)}" onerror="this.style.display='none'">
            <div><b style="font-size:20px">${esc(s.name || 'Unknown')}</b><br>Ration card ${esc(s.rationcard_no)} · ${s.members} member(s) · ${esc(s.card_type)} card</div></div>`;
    }

    function cost(i) {
        const kg = parseFloat(document.getElementById('kg' + allowance[i].stock_id).value) || 0;
        const inQuota = Math.min(kg, allowance[i].remaining), extra = kg - inQuota;
        const a = allowance[i];
        document.getElementById('cost' + a.stock_id).innerHTML = '₹ ' + (inQuota * a.price + extra * a.market_price).toFixed(2) +
            (extra > 0 ? ` <span class="extra">(${extra.toFixed(2)} kg at full price)</span>` : '');
    }

    function render(d) {
        const s = d.session;
        const key = s ? s.id + s.status : 'none';
        if (key === shown) return;
        shown = key;
        if (!s || s.status === 'cancelled') {
            live.innerHTML = '<p class="waiting">Waiting for a customer to scan their finger on the node…</p>';
        } else if (s.status === 'identified') {
            allowance = d.allowance;
            live.innerHTML = who(s) + `<table class="node-table"><tr><th>Item</th><th>Monthly quota</th><th>Taken</th>
                <th>Remaining</th><th>Ration price</th><th>Full price</th><th>Give (kg)</th><th>Cost</th></tr>` +
                allowance.map((a, i) => `<tr><td>${esc(a.name)}</td><td>${a.quota} kg</td><td>${a.taken} kg</td><td><b>${a.remaining} kg</b></td>
                <td>₹${a.price}/kg</td><td>₹${a.market_price}/kg</td>
                <td><input id="kg${a.stock_id}" type="number" min="0" max="50" step="0.1" value="${a.remaining}" oninput="cost(${i})"></td>
                <td id="cost${a.stock_id}"></td></tr>`).join('') +
                `</table><p><button class="btn" onclick="dispense(${s.id})">Dispense</button>
                <button class="btn grey" onclick="cancelSession()">Cancel</button></p>`;
            allowance.forEach((_, i) => cost(i));
        } else if (s.status === 'approved' || s.status === 'dispensing') {
            live.innerHTML = who(s) + '<p class="waiting">Node is dispensing… keep the bag under the chute.</p>';
        } else if (s.status === 'done') {
            const total = d.results.reduce((t, r) => t + parseFloat(r.amount), 0);
            live.innerHTML = who(s) + `<table class="node-table"><tr><th>Item</th><th>Given</th><th>From quota</th><th>Extra (full price)</th><th>Amount</th></tr>` +
                d.results.map(r => `<tr><td>${esc(r.stock_name)}</td><td>${(+r.kg).toFixed(3)} kg</td><td>${(+r.quota_kg).toFixed(3)} kg</td>
                <td class="${r.extra_kg > 0 ? 'extra' : ''}">${(+r.extra_kg).toFixed(3)} kg</td><td>₹ ${(+r.amount).toFixed(2)}</td></tr>`).join('') +
                `</table><p class="total">Collect ₹ ${total.toFixed(2)}</p>`;
        } else if (s.status === 'enroll') {
            live.innerHTML = who(s) + '<p class="waiting">Enrolling: ask the customer to place the same finger on the node twice.</p>';
        } else if (s.status === 'enrolled') {
            live.innerHTML = who(s) + '<p class="waiting">✔ Fingerprint enrolled. The customer can now scan to collect ration.</p>';
        } else if (s.status === 'failed') {
            live.innerHTML = who(s) + '<p class="waiting extra">Enrollment failed. Try again.</p>';
        }
    }

    async function refresh() {
        try { render(await (await fetch('node.php?action=state')).json()); } catch (e) {}
    }

    async function dispense(id) {
        const kg = {};
        allowance.forEach(a => kg[a.stock_id] = parseFloat(document.getElementById('kg' + a.stock_id).value) || 0);
        const r = await (await fetch('node.php?action=dispense', { method: 'POST', body: JSON.stringify({ session_id: id, kg }) })).json();
        if (!r.ok) alert(r.msg);
        refresh();
    }

    async function cancelSession() {
        await fetch('node.php?action=cancel', { method: 'POST' });
        refresh();
    }

    async function enroll() {
        const body = new FormData();
        body.append('rationcard_no', document.getElementById('enrollCard').value);
        const r = await (await fetch('node.php?action=enroll', { method: 'POST', body })).json();
        if (!r.ok) alert(r.msg);
        refresh();
    }

    async function addCustomer(ev) {
        ev.preventDefault();
        const body = new FormData(ev.target);
        const r = await (await fetch('node.php?action=add_customer', { method: 'POST', body })).json();
        if (!r.ok) return alert(r.msg);
        location.href = 'node.php?new=' + encodeURIComponent(body.get('rationcard_no'));  // reload with new customer picked for enrolling
    }

    refresh();
    setInterval(refresh, 1500);
    </script>
<script src="../assets/js/lang.js"></script>
</body>

</html>
