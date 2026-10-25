<?php
// Add a customer (ration card + login) from the distributor or admin panel.
// $shop = tbl_distributor row the customer belongs to (customers are matched to a shop by pincode).
// Returns '' on success, else an error message.
require_once __DIR__ . '/card_types.php';

// tbl_ration has fixed slots: head of family (m_) + 3 more members (p1_..p3_).
const MEMBER_SLOTS = ['m' => 'Head of family', 'p1' => 'Member 2', 'p2' => 'Member 3', 'p3' => 'Member 4'];

function add_customer($conn, $in, $shop, $file = null) {
    $t = fn($k) => trim((string)($in[$k] ?? ''));
    $rc = $t('rationcard_no');
    $aadhar = $t('aadhar_no');
    $members = (int)($in['f_members'] ?? 0);
    $type = $in['card_type'] ?? '';
    $pass = (string)($in['password'] ?? '');
    if (!preg_match('/^\d{15}$/', $rc)) return 'Ration card number must be 15 digits.';
    if ($t('fname') === '') return 'Enter the first name.';
    if (!preg_match('/^\d{12}$/', $aadhar)) return 'Aadhaar number must be 12 digits.';
    if ($t('contact_no') !== '' && !preg_match('/^\d{10}$/', $t('contact_no'))) return 'Phone number must be 10 digits.';
    if ($t('email_id') !== '' && !filter_var($t('email_id'), FILTER_VALIDATE_EMAIL)) return 'Enter a valid email.';
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $t('dob'))) return 'Enter the date of birth.';
    if (!in_array($t('gender'), ['male', 'female', 'other'], true)) return 'Pick a gender.';
    if ($members < 1 || $members > 20) return 'Family members must be 1 to 20.';
    if (!isset(CARD_TYPES[$type])) return 'Pick a card type.';
    if (strlen($pass) < 8) return 'Password must be at least 8 characters.';
    if (!$shop) return 'Pick a shop.';
    foreach (MEMBER_SLOTS as $p => $label) {
        $a = $t("{$p}_aadhar");
        if ($a !== '' && !preg_match('/^\d{12}$/', $a)) return "$label: Aadhaar must be 12 digits.";
    }

    $e = fn($v) => mysqli_real_escape_string($conn, $v);
    if (mysqli_num_rows(mysqli_query($conn, "SELECT u_id FROM tbl_user WHERE rationcard_no='$rc'"))) return 'This ration card is already registered.';
    if (mysqli_num_rows(mysqli_query($conn, "SELECT u_id FROM tbl_user WHERE aadhar_no='$aadhar'"))) return 'This Aadhaar number is already registered.';

    $image = 'male.png';
    if ($file && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($file['error'] !== UPLOAD_ERR_OK || !in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) || !getimagesize($file['tmp_name']))
            return 'Photo must be a JPG, PNG or WEBP image.';
        $image = "cust_$rc.$ext";
        if (!move_uploaded_file($file['tmp_name'], __DIR__ . "/../uploads_images/$image")) return 'Could not save the photo.';
    }

    $name = trim($t('fname') . ' ' . $t('lname'));
    $ration = ['rationcard_no' => $rc, 'f_members' => (string)$members, 'card_type' => $type];
    foreach (MEMBER_SLOTS as $p => $_) {
        $ration["{$p}_name"] = $t("{$p}_name");
        $ration["{$p}_age"] = $t("{$p}_age");
        if ($t("{$p}_aadhar") !== '') $ration["{$p}_aadhar"] = $t("{$p}_aadhar");  // else column default
    }
    if ($ration['m_name'] === '') $ration['m_name'] = $name;
    if (!isset($ration['m_aadhar'])) $ration['m_aadhar'] = $aadhar;
    if ($ration['m_age'] === '') $ration['m_age'] = (string)(new DateTime($t('dob')))->diff(new DateTime())->y;
    if (!mysqli_num_rows(mysqli_query($conn, "SELECT rationcard_no FROM tbl_ration WHERE rationcard_no='$rc'"))
        && !mysqli_query($conn, "INSERT INTO tbl_ration (" . implode(',', array_keys($ration)) . ") VALUES ('" . implode("','", array_map($e, $ration)) . "')"))
        return 'Could not save ration card: ' . mysqli_error($conn);

    $row = [
        'fname' => $t('fname'), 'mname' => $t('mname'), 'lname' => $t('lname'), 'address' => $t('address'),
        'contact_no' => $t('contact_no'), 'gender' => $t('gender'), 'dob' => $t('dob'), 'aadhar_no' => $aadhar,
        'rationcard_no' => $rc, 'city' => $shop['city'], 'state' => $shop['state'], 'pincode' => $shop['pincode'],
        'email_id' => $t('email_id'), 'image' => $image, 'password' => md5($pass),
    ];
    $vals = implode("','", array_map($e, $row));
    if (!mysqli_query($conn, "INSERT INTO tbl_user (" . implode(',', array_keys($row)) . ") VALUES ('$vals')"))
        return 'Could not save customer: ' . mysqli_error($conn);
    return '';
}

// Form fields shared by both panels (form needs enctype="multipart/form-data"). Admin passes a shop <select> as $extra.
function customer_form_fields($extra = '') { ?>
    <h4 class="cust-h">Personal details</h4>
    <div class="cust-form">
        <label>Ration card no.<input name="rationcard_no" placeholder="15 digits" pattern="\d{15}" maxlength="15" required></label>
        <label>First name<input name="fname" required></label>
        <label>Middle name<input name="mname"></label>
        <label>Last name<input name="lname"></label>
        <label>Aadhaar no.<input name="aadhar_no" placeholder="12 digits" pattern="\d{12}" maxlength="12" required></label>
        <label>Date of birth<input name="dob" type="date" required></label>
        <label>Gender<select name="gender"><option value="male">Male</option><option value="female">Female</option><option value="other">Other</option></select></label>
        <label>Phone<input name="contact_no" type="tel" pattern="\d{10}" maxlength="10" placeholder="10 digits"></label>
        <label>Email<input name="email_id" type="email"></label>
        <label class="wide">Address<input name="address"></label>
        <label>Photo<input name="photo" type="file" accept="image/png,image/jpeg,image/webp"></label>
        <?php if ($extra) { ?><label>Shop<?= $extra ?></label><?php } ?>
    </div>
    <h4 class="cust-h">Ration card</h4>
    <div class="cust-form">
        <label>Card type<select name="card_type"><?php foreach (CARD_TYPES as $k => $label) echo "<option value=\"$k\">" . htmlspecialchars($label) . '</option>'; ?></select></label>
        <label>Family members<input name="f_members" type="number" min="1" max="20" value="1" required></label>
    </div>
    <div class="cust-scroll">
        <table class="cust-members">
            <tr><th></th><th>Name</th><th>Age</th><th>Aadhaar</th></tr>
            <?php foreach (MEMBER_SLOTS as $p => $label) { ?>
            <tr><td><?= $label ?></td><td><input name="<?= $p ?>_name"<?= $p === 'm' ? ' placeholder="Same as customer if empty"' : '' ?>></td>
                <td><input name="<?= $p ?>_age" type="number" min="0" max="120"></td>
                <td><input name="<?= $p ?>_aadhar" pattern="\d{12}" maxlength="12"></td></tr>
            <?php } ?>
        </table>
    </div>
    <h4 class="cust-h">Login</h4>
    <div class="cust-form">
        <label>Password<input name="password" value="user@123" minlength="8" required></label>
    </div>
    <style>
    .cust-h { margin: 16px 0 8px; color: #0a2558; font-size: 16px; }
    .cust-form { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 12px; margin-bottom: 12px; }
    .cust-form label { display: flex; flex-direction: column; gap: 4px; font-size: 14px; min-width: 0; }
    .cust-form .wide { grid-column: span 2; }
    .cust-form input, .cust-form select, .cust-members input { padding: 8px; border: 1px solid #ccc; border-radius: 6px; width: 100%; min-width: 0; }
    .cust-scroll { overflow-x: auto; margin-bottom: 12px; }
    .cust-members { border-collapse: collapse; width: 100%; min-width: 520px; }
    .cust-members th, .cust-members td { padding: 4px 6px; text-align: left; font-size: 14px; }
    @media (max-width: 500px) { .cust-form .wide { grid-column: auto; } }
    </style>
<?php }
