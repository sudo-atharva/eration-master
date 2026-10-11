<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("location: ../login/login.php");
    exit;
}
include '../php/config.php';
include '../php/connection.php';
include '../php/customer_lib.php';
$shops = mysqli_fetch_all(mysqli_query($conn, "SELECT pds_no, fname, lname, city, state, pincode FROM tbl_distributor ORDER BY pds_no"), MYSQLI_ASSOC);
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shop = null;
    foreach ($shops as $s) if ((string)$s['pds_no'] === ($_POST['pds_no'] ?? '')) $shop = $s;
    $msg = add_customer($conn, $_POST, $shop) ?: 'Customer added. The shop can now enroll their fingerprint on the Ration Node page.';
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
        <div class="home-content" style="display:block;padding:0 20px">
            <div class="w3-card w3-white w3-round-large w3-padding-large">
                <?php if ($msg) { ?><p class="w3-panel w3-pale-blue"><?= htmlspecialchars($msg) ?></p><?php } ?>
                <form method="post">
                    <?php customer_form_fields($shopSelect); ?>
                    <button class="w3-button w3-round-large w3-dark-blue">Add customer</button>
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
    </script>
<script src="../assets/js/lang.js"></script>
</body>

</html>
