<?php
// Add a customer (ration card + login) from the distributor or admin panel.
// $shop = tbl_distributor row the customer belongs to (customers are matched to a shop by pincode).
// Returns '' on success, else an error message.
const CARD_TYPES = ['PHH', 'AAY', 'NPHH', 'BPL', 'APL'];

function add_customer($conn, $in, $shop) {
    $rc = trim($in['rationcard_no'] ?? '');
    $fname = trim($in['fname'] ?? '');
    $lname = trim($in['lname'] ?? '');
    $members = (int)($in['f_members'] ?? 0);
    $type = $in['card_type'] ?? '';
    $pass = (string)($in['password'] ?? '');
    if (!preg_match('/^\d{15}$/', $rc)) return 'Ration card number must be 15 digits.';
    if ($fname === '') return 'Enter the first name.';
    if ($members < 1 || $members > 20) return 'Family members must be 1 to 20.';
    if (!in_array($type, CARD_TYPES, true)) return 'Pick a card type.';
    if (strlen($pass) < 8) return 'Password must be at least 8 characters.';
    if (!$shop) return 'Pick a shop.';
    if (mysqli_num_rows(mysqli_query($conn, "SELECT u_id FROM tbl_user WHERE rationcard_no='$rc'"))) return 'This ration card is already registered.';

    $e = fn($v) => mysqli_real_escape_string($conn, $v);
    $name = $e(trim("$fname $lname"));
    if (!mysqli_num_rows(mysqli_query($conn, "SELECT rationcard_no FROM tbl_ration WHERE rationcard_no='$rc'"))
        && !mysqli_query($conn, "INSERT INTO tbl_ration (rationcard_no, m_name, m_age, p1_name, p1_age, p2_name, p2_age, p3_name, p3_age, f_members, card_type)
            VALUES ('$rc', '$name', '', '', '', '', '', '', '', $members, '$type')"))
        return 'Could not save ration card: ' . mysqli_error($conn);

    $row = [
        'fname' => $fname, 'mname' => '', 'lname' => $lname, 'address' => trim($in['address'] ?? ''),
        'contact_no' => trim($in['contact_no'] ?? ''), 'gender' => 'male', 'dob' => '',
        'aadhar_no' => substr($rc, -12),  // tbl_user.aadhar_no is unique; same placeholder rule as Auth/registration.php
        'rationcard_no' => $rc, 'city' => $shop['city'], 'state' => $shop['state'], 'pincode' => $shop['pincode'],
        'email_id' => '', 'image' => 'male.png', 'password' => md5($pass),
    ];
    $vals = implode("','", array_map($e, $row));
    if (!mysqli_query($conn, "INSERT INTO tbl_user (" . implode(',', array_keys($row)) . ") VALUES ('$vals')"))
        return 'Could not save customer: ' . mysqli_error($conn);
    return '';
}

// Form fields shared by both panels. Admin passes a shop <select> as $extra.
function customer_form_fields($extra = '') { ?>
    <div class="cust-form">
        <input name="rationcard_no" placeholder="Ration card no. (15 digits)" pattern="\d{15}" required>
        <input name="fname" placeholder="First name" required>
        <input name="lname" placeholder="Last name">
        <input name="contact_no" placeholder="Phone" type="tel" maxlength="10">
        <input name="f_members" type="number" min="1" max="20" value="1" title="Family members" required>
        <select name="card_type"><?php foreach (CARD_TYPES as $t) echo "<option>$t</option>"; ?></select>
        <input name="address" placeholder="Address">
        <input name="password" placeholder="Login password" value="user@123" minlength="8" required>
        <?= $extra ?>
    </div>
    <style>.cust-form { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; } .cust-form input, .cust-form select { padding: 8px; min-width: 0; }</style>
<?php }
