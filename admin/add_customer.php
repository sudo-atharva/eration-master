<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("location: ../Auth/admin_login.php");
    exit;
}
include '../php/config.php';
include '../php/connection.php';
include '../php/customer_lib.php';
include '../php/node_lib.php';

// ---- Link fingerprint: start an enroll job on a shop's ration node, then poll its status ----
$action = $_GET['action'] ?? '';
if ($action === 'enroll') {
    $rc = mysqli_real_escape_string($conn, trim($_POST['rationcard_no'] ?? ''));
    $pds = (int)($_POST['node_pds'] ?? 0);
    if (!mysqli_num_rows(mysqli_query($conn, "SELECT u_id FROM tbl_user WHERE rationcard_no='$rc'")))
        node_json(['ok' => false, 'msg' => 'No customer with this ration card number.']);
    if (!mysqli_num_rows(mysqli_query($conn, "SELECT pds_no FROM tbl_node WHERE pds_no=$pds")))
        node_json(['ok' => false, 'msg' => 'No ration node registered for this POS / PDS number.']);
    node_cancel_open($conn, $pds);
    $now = date('Y-m-d H:i:s');
    mysqli_query($conn, "INSERT INTO tbl_node_session (pds_no, rationcard_no, status, items, created) VALUES ($pds, '$rc', 'enroll', '', '$now')");
    node_json(['ok' => true, 'session_id' => (int)mysqli_insert_id($conn)]);
}
if ($action === 'state') {
    $s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT status FROM tbl_node_session WHERE id=" . (int)($_GET['sid'] ?? 0)));
    node_json(['status' => $s['status'] ?? 'missing']);
}
$nodes = mysqli_fetch_all(mysqli_query($conn,
    "SELECT n.pds_no, d.fname, d.lname, d.city FROM tbl_node n LEFT JOIN tbl_distributor d ON d.pds_no=n.pds_no ORDER BY n.pds_no"), MYSQLI_ASSOC);
$shops = mysqli_fetch_all(mysqli_query($conn, "SELECT pds_no, fname, lname, city, state, pincode FROM tbl_distributor ORDER BY pds_no"), MYSQLI_ASSOC);
$msg = $err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop = null;
    foreach ($shops as $s) if ((string)$s['pds_no'] === ($_POST['pds_no'] ?? '')) $shop = $s;
    $err = add_customer($conn, $_POST, $shop, $_FILES['photo'] ?? null);
    $msg = $err ?: 'Customer added. Link their fingerprint below.';
}
$shopSelect = '<select name="pds_no" required><option value="">Shop…</option>';
foreach ($shops as $s)
    $shopSelect .= '<option value="' . (int)$s['pds_no'] . '">PDS ' . (int)$s['pds_no'] . ' — ' . htmlspecialchars("{$s['fname']} {$s['lname']}, {$s['city']}") . '</option>';
$shopSelect .= '</select>';
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">

<head>
    <meta charset="UTF-8">
    <title> Admin | E-Ration </title>
    <link rel="stylesheet" href="style.css?v=<?= $v ?>">
    <link rel="stylesheet" href="https://www.w3schools.com/w3css/4/w3.css">
    <link href='https://unpkg.com/boxicons@2.0.7/css/boxicons.min.css' rel='stylesheet'>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <div class="sidebar">
        <div class="logo-details">
            <img src="./images/logo2.png" height="40px" weight="40px" style="margin-left: 10px">
            <img src="./images/logo3.png" height="40px" weight="140px">
        </div>
        <ul class="nav-links">
            <li>
                <a href="admin_dash.php">
                    <i class='bx bx-grid-alt'></i>
                    <span class="links_name">Dashboard</span>
                </a>
            </li>
            <li>
                <a href="add_dist.php">
                    <i class='bx bx-user'></i>
                    <span class="links_name">Add Distributor</span>
                </a>
            </li>
            <li>
                <a href="#" class="active">
                    <i class='bx bx-user-plus'></i>
                    <span class="links_name">Add Customer</span>
                </a>
            </li>
            <li>
                <a href="add_stock.php">
                    <i class="bx bxs-cart-add"></i>
                    <span class="links_name">Add Stock</span>
                </a>
            </li>
            <li>
                <a href="giveration.php">
                    <i class="bx bx-coin-stack"></i>
                    <span class="links_name">Give Stock</span>
                </a>
            </li>
            <li>
                <a href="applied_stock.php">
                    <i class='bx bx-list-ul'></i>
                    <span class="links_name">View Applied Stock</span>
                </a>
            </li>
            <li>
                <a href="stock_details.php">
                    <i class='bx bx-coin-stack'></i>
                    <span class="links_name">Stock Details</span>
                </a>
            </li>
            <li>
                <a href="stock_details.php">
                    <i class='bx bx-user'></i>
                    <span class="links_name">Distributors Details</span>
                </a>
            </li>
            <li>
                <a href="check_complaints.php">
                    <i class='bx bx-message'></i>
                    <span class="links_name">Check Complains</span>
                </a>
            </li>
            <li class="log_out">
                <a href="logout.php">
                    <i class='bx bx-log-out'></i>
                    <span class="links_name">Log out</span>
                </a>
            </li>
        </ul>
    </div>
    <section class="home-section">
        <nav>
            <div class="sidebar-button">
                <i class='bx bx-menu sidebarBtn'></i>
                <span class="dashboard">Add Customer</span>
            </div>
            <div class="profile-details">
                <img src="images/profile.jpg" alt="">
                <span class="admin_name">Admin</span>
            </div>
        </nav>
        <div class="home-content" style="padding:104px 20px 20px">
            <div class="w3-card w3-white w3-round-large w3-padding-large">
                <?php if ($msg) { ?><p class="w3-panel <?= $err ? 'w3-pale-red' : 'w3-pale-green' ?>"><?= htmlspecialchars($msg) ?></p><?php } ?>
                <form method="post" enctype="multipart/form-data">
                    <?php customer_form_fields($shopSelect); ?>
                    <button class="w3-button w3-round-large w3-dark-blue">Add customer</button>
                </form>
            </div>

            <div class="w3-card w3-white w3-round-large w3-padding-large" style="margin:24px 0" id="link">
                <h4 class="cust-h">Link fingerprint</h4>
                <p class="w3-small" style="margin:0 0 12px">Starts enrollment on the shop's ration node. The customer then places the same finger on that node's sensor twice.</p>
                <form class="cust-form" onsubmit="linkFinger(event)">
                    <label>Ration card no.<input name="rationcard_no" pattern="\d{15}" maxlength="15" required
                        value="<?= ($_POST && !$err) ? htmlspecialchars($_POST['rationcard_no']) : '' ?>"></label>
                    <label>Node (POS / PDS no.)<select name="node_pds" required>
                        <option value="">Select node…</option>
                        <?php foreach ($nodes as $n) { ?>
                        <option value="<?= (int)$n['pds_no'] ?>"<?= (!$err && ($_POST['pds_no'] ?? '') === (string)$n['pds_no']) ? ' selected' : '' ?>>
                            POS <?= (int)$n['pds_no'] ?><?= $n['fname'] ? htmlspecialchars(" — {$n['fname']} {$n['lname']}, {$n['city']}") : '' ?></option>
                        <?php } ?>
                    </select></label>
                    <label>&nbsp;<button class="w3-button w3-round-large w3-dark-blue">Start enrollment</button></label>
                </form>
                <?php if (!$nodes) { ?><p class="w3-text-red">No ration nodes registered yet (tbl_node).</p><?php } ?>
                <p id="linkStatus" style="font-weight:600"></p>
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

    const linkStatus = document.getElementById('linkStatus');
    const STATUS = {
        enroll: ['w3-text-blue', 'Waiting at node… ask the customer to place the same finger on the sensor twice.'],
        enrolled: ['w3-text-green', '✔ Fingerprint linked. The customer can now scan at this shop to collect ration.'],
        failed: ['w3-text-red', 'Enrollment failed on the node. Try again.'],
        cancelled: ['w3-text-red', 'Enrollment was cancelled at the shop.'],
    };
    let pollTimer;
    async function linkFinger(ev) {
        ev.preventDefault();
        clearInterval(pollTimer);
        const r = await (await fetch('add_customer.php?action=enroll', { method: 'POST', body: new FormData(ev.target) })).json();
        if (!r.ok) { linkStatus.className = 'w3-text-red'; linkStatus.textContent = r.msg; return; }
        const started = Date.now();
        const poll = async () => {
            const { status } = await (await fetch('add_customer.php?action=state&sid=' + r.session_id)).json();
            const [cls, text] = STATUS[status] || ['w3-text-red', 'Node did not respond.'];
            linkStatus.className = cls;
            linkStatus.textContent = text;
            // stop once finished, or after the node session TTL (10 min)
            if (status !== 'enroll' || Date.now() - started > 600000) clearInterval(pollTimer);
        };
        poll();
        pollTimer = setInterval(poll, 1500);
    }
    <?php if ($_POST && !$err) { ?>document.getElementById('link').scrollIntoView();<?php } ?>
    </script>
<script src="../assets/js/lang.js"></script>
</body>

</html>
