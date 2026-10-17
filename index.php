<?php
require_once 'db.php';

$selected_pu = isset($_GET['pu_id']) ? intval($_GET['pu_id']) : 0;
$results = [];


$pu_query = $conn->query("SELECT uniqueid, polling_unit_name, polling_unit_number FROM polling_unit WHERE polling_unit_name != '' ORDER BY polling_unit_name ASC");

if ($selected_pu > 0) {
    $stmt = $conn->prepare("SELECT party_abbreviation, party_score FROM announced_pu_results WHERE polling_unit_uniqueid = ?");
    $stmt->bind_param("i", $selected_pu);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Polling Unit Results</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 30px auto; padding: 0 15px; }
        nav a { margin-right: 15px; font-weight: bold; text-decoration: none; color: #0066cc; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f4f4f4; }
        select, button { padding: 8px; font-size: 14px; }
    </style>
</head>
<body>
    <nav>
        <a href="index.php"> Single Polling Unit</a>
        <a href="lga.php"> LGA Total</a>
        <a href="add.php"> Add New Result</a>
    </nav>
    <hr>

    <h2>Check Polling Unit Result</h2>
    <form method="GET" action="">
        <label for="pu_id"><strong>Select Polling Unit:</strong></label><br><br>
        <select name="pu_id" id="pu_id" required>
            <option value="">Choose a Polling Unit </option>
            <?php while ($pu = $pu_query->fetch_assoc()): ?>
                <option value="<?= $pu['uniqueid'] ?>" <?= ($selected_pu == $pu['uniqueid']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($pu['polling_unit_name']) ?> (<?= htmlspecialchars($pu['polling_unit_number']) ?>)
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">View Results</button>
    </form>

    <?php if ($selected_pu > 0): ?>
        <h3>Results</h3>
        <?php if (!empty($results)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Party</th>
                        <th>Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total = 0;
                    foreach ($results as $row): 
                        $total += $row['party_score'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['party_abbreviation']) ?></td>
                            <td><?= htmlspecialchars($row['party_score']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #fafafa;">
                        <td>TOTAL</td>
                        <td><?= $total ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>No results recorded for this polling unit.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>