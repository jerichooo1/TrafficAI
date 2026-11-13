<?php
include 'db.php';
$reports = $conn->query("SELECT * FROM traffic_reports ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Traffic Monitoring</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #111827;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        }
        h1 {
            margin-top: 0;
        }
        a {
            text-decoration: none;
            color: #2563eb;
            font-weight: 600;
            margin-right: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        th, td {
            padding: 14px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }
        th {
            background: #eff6ff;
        }
        video {
            width: 200px;
            border-radius: 8px;
            background: #000;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Traffic Analysis Reports</h1>
        <a href="dashboard.php">Dashboard</a>
        <a href="upload_video.php">Upload Video</a>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Video Name</th>
                    <th>Cars</th>
                    <th>Motorcycles</th>
                    <th>Buses</th>
                    <th>Trucks</th>
                    <th>Total</th>
                    <th>Processed At</th>
                    <th>Output</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($reports && $reports->num_rows > 0): ?>
                    <?php while ($row = $reports->fetch_assoc()): ?>
                        <tr>
                            <td><?= (int)$row['id'] ?></td>
                            <td><?= htmlspecialchars($row['video_name']) ?></td>
                            <td><?= (int)$row['car_count'] ?></td>
                            <td><?= (int)$row['motorcycle_count'] ?></td>
                            <td><?= (int)$row['bus_count'] ?></td>
                            <td><?= (int)$row['truck_count'] ?></td>
                            <td><?= (int)$row['total_count'] ?></td>
                            <td><?= htmlspecialchars($row['processed_at']) ?></td>
                            <td>
                                <video controls>
                                    <source src="<?= htmlspecialchars($row['output_video']) ?>" type="video/mp4">
                                </video>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">No reports found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>