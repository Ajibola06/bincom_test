<?php 
require_once 'db.php';  

$message = '';
$parties = ['PDP', 'DPP', 'ACN', 'PPA', 'CDC', 'JP', 'ANPP', 'LABOUR', 'CPP'];

$lgas = $conn->query("SELECT lga_id, lga_name FROM lga ORDER BY lga_name ASC");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pu_name = trim($_POST['pu_name']);
    $pu_number = trim($_POST['pu_number']);
    $lga_id = intval($_POST['lga_id']);
    $entered_by = trim($_POST['entered_by']);
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $date_entered = date('Y-m-d H:i:s');
}

    if (!empty($pu_name) && !empty($pu_number) && $lga_id > 0) {
        $stmt = $conn->prepare("
            INSERT INTO polling_unit (polling_unit_name, polling_unit_number, lga_id, entered_by_user, date_entered, user_ip_address) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssisss", $pu_name, $pu_number, $lga_id, $entered_by, $date_entered, $user_ip);

        if ($stmt->execute()) {
            $new_pu_id = $conn->insert_id;

            $score_stmt = $conn->prepare("
                INSERT INTO announced_pu_results (polling_unit_uniqueid, party_abbreviation, party_score, entered_by_user, date_entered, user_ip_address) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            
            foreach ($parties as $party) {
                $score = isset($_POST['scores'][$party]) ? intval($_POST['scores'][$party]) : 0;
                $score_stmt->bind_param("isisss", $new_pu_id, $party,  $score, $entered_by, $date_entered, $user_ip);
                $score_stmt->execute();
            }

                $message = "<div style 'color: greeen; font-weight: bold; margin: 15px 0;'>Successfully saved! New Polling unit ID: " . $new_pu_id . "</div>";
            } else {
                $message = "<div style 'color: red;'>Error saving Polling Unit: " . $conn->error . "</div>";
            }
            } else {
            $message = "<div style = 'color: red;'>Please fill in all required fields.</div>";
        }
    


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title> Add New Polling Unit Result</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 0 15px; }
        nav a { margin-right: 15px; font-weight: bold; text-decoration: none; color: #0066cc; }
        .form-group { margin-bottom: 12px; }
        label { display: block; font-weight: bold; margin-bottom: 4px; }
        input[type="text"], input[type="number"], select { width: 100%; max-width: 400px; padding: 8px; box-sizing: border-box; }
        .party-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; max-width: 600px; margin-top: 10px; }
        .party-box { background: #f9f9f9; padding: 8px; border: 1px solid #ddd; }
        button { background-color: #0066cc; color: white; padding: 10px 20px; border: none; cursor: pointer; font-size: 15px; margin-top: 15px; }
        button:hover { background-color: #004c99; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php"> Single Polling Unit</a>
        <a href="lga.php"> LGA Total</a>
        <a href="add.php"> Add New Result</a>
    </nav>
    <hr>

    <h2>Enter Polling Unit & Results</h2>
    <?= $message ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="pu_name">Polling Unit Name *</label>
            <input type="text" name="pu_name" id="pu_name" required placeholder="e.g. Community Primary School">
        </div>

        <div class="form-group">
            <label for="pu_number">Polling Unit Number *</label>
            <input type="text" name="pu_number" id="pu_number" required placeholder="e.g. DT/01/01/001">
        </div>

        <div class="form-group">
            <label for="lga_id">Local Government Area (LGA) *</label>
            <select name="lga_id" id="lga_id" required>
                <option value=""> Select LGA </option>
                <?php while ($lga = $lgas->fetch_assoc()): ?>
                    <option value="<?= $lga['lga_id'] ?>"><?= htmlspecialchars($lga['lga_name']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="entered_by">Your Name / User *</label>
            <input type="text" name="entered_by" id="entered_by" required placeholder="e.g. John Doe">
        </div>

        <h3 style="margin-top: 25px;">Enter Scores per Party</h3>
        <div class="party-grid">
            <?php foreach ($parties as $party): ?>
                <div class="party-box">
                    <label for="party_<?= $party ?>"><?= $party ?></label>
                    <input type="number" id="party_<?= $party ?>" name="scores[<?= $party ?>]" min="0" value="0" required>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit">Submit Results</button>
    </form>
</body>
</html>