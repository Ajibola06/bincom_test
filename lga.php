<?php
require_once 'db.php';

$selected_lga = isset($_GET['lga_id']) ? intval($_GET['lga_id']) : 0;
$lga_totals = [];


$lgas = $conn->query("SELECT lga_id, lga_name FROM lga ORDER BY lga_name ASC");

if ($selected_lga > 0) {
    $sql = "
        SELECT apr.party_abbreviation, SUM(apr.party_score) as total_score
        FROM announced_pu_results apr
        INNER JOIN polling_unit pu ON apr.polling_unit_uniqueid = pu.uniqueid
        WHERE pu.lga_id = ?
        GROUP BY apr.party_abbreviation
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_lga);
    $stmt->execute();
    $lga_totals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Total Results by LGA</title>
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
        <a href="index.php">Single Polling Unit</a>
        <a href="lga.php">LGA Total</a>
        <a href="add.php">Add New Result</a>
    </nav>
    <hr>

    <h2>Total Summed Results by LGA</h2>
    <form method="GET" action="">
        <label for="lga_id"><strong>Select - Local Government Area (LGA):</strong></label><br><br>
        <select name="lga_id" id="lga_id" required>
            <option value=""> Choose LGA </option>
            <?php while ($lga = $lgas->fetch_assoc()): ?>
                <option value="<?= $lga['lga_id'] ?>" <?= ($selected_lga == $lga['lga_id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($lga['lga_name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Calculate Total</button>
    </form>

    <?php if ($selected_lga > 0): ?>
        <h3>Aggregated Party Scores</h3>
        <?php if (!empty($lga_totals)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Party</th>
                        <th>Total Votes Across LGA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $grand_total = 0;
                    foreach ($lga_totals as $row): 
                        $grand_total += $row['total_score'];
                    ?>
                        <tr>
                            <td><?= htmlspecialchars($row['party_abbreviation']) ?></td>
                            <td><?= htmlspecialchars($row['total_score']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="font-weight: bold; background: #fafafa;">
                        <td>GRAND TOTAL</td>
                        <td><?= $grand_total ?></td>
                    </tr>
                </tbody>
            </table>
        <?php else: ?>
            <p>No results recorded for the selected LGA.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>