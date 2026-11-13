<?php
include 'db.php';

$latest = null;
$result = $conn->query("SELECT * FROM traffic_reports ORDER BY id DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $latest = $result->fetch_assoc();
}

$totals = [
    'car' => 0,
    'motorcycle' => 0,
    'bus' => 0,
    'truck' => 0,
    'total' => 0
];

$totalQuery = $conn->query("
    SELECT 
        SUM(car_count) AS cars,
        SUM(motorcycle_count) AS motorcycles,
        SUM(bus_count) AS buses,
        SUM(truck_count) AS trucks,
        SUM(total_count) AS total_vehicles
    FROM traffic_reports
");

if ($totalQuery && $totalQuery->num_rows > 0) {
    $row = $totalQuery->fetch_assoc();
    $totals['car'] = $row['cars'] ?? 0;
    $totals['motorcycle'] = $row['motorcycles'] ?? 0;
    $totals['bus'] = $row['buses'] ?? 0;
    $totals['truck'] = $row['trucks'] ?? 0;
    $totals['total'] = $row['total_vehicles'] ?? 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Traffic Monitoring</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #111827;
        }
        .layout {
            display: flex;
            min-height: 100vh;
        }
        .sidebar {
            width: 240px;
            background: #0f172a;
            color: white;
            padding: 30px 20px;
        }
        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 30px;
        }
        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .sidebar a:hover {
            color: white;
        }
        .main {
            flex: 1;
            padding: 30px;
        }
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background: white;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        }
        .card h3 {
            margin: 0 0 10px;
            font-size: 15px;
            color: #6b7280;
        }
        .card .value {
            font-size: 30px;
            font-weight: 700;
            color: #111827;
        }
        .panel {
            background: white;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.06);
        }
        video {
            width: 100%;
            max-width: 800px;
            border-radius: 12px;
            margin-top: 15px;
            background: black;
        }
        .meta {
            margin-top: 15px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
<div class="layout">
    <div class="sidebar">
        <h2>Traffic AI</h2>
        <a href="dashboard.php">Dashboard</a>
        <a href="upload_video.php">Upload Video</a>
        <a href="reports.php">Reports</a>
    </div>

    <div class="main">
        <h1>Traffic Monitoring Dashboard</h1>

        <div class="cards">
            <div class="card">
                <h3>Total Vehicles</h3>
                <div class="value"><?= (int)$totals['total'] ?></div>
            </div>
            <div class="card">
                <h3>Cars</h3>
                <div class="value"><?= (int)$totals['car'] ?></div>
            </div>
            <div class="card">
                <h3>Motorcycles</h3>
                <div class="value"><?= (int)$totals['motorcycle'] ?></div>
            </div>
            <div class="card">
                <h3>Buses</h3>
                <div class="value"><?= (int)$totals['bus'] ?></div>
            </div>
            <div class="card">
                <h3>Trucks</h3>
                <div class="value"><?= (int)$totals['truck'] ?></div>
            </div>
        </div>

        <div class="panel">
            <h2>Latest Processed Video</h2>

            <?php if ($latest): ?>
                <div class="meta">
                    <strong>Video Name:</strong> <?= htmlspecialchars($latest['video_name']) ?><br>
                    <strong>Processed At:</strong> <?= htmlspecialchars($latest['processed_at']) ?><br>
                    <strong>Total Count:</strong> <?= (int)$latest['total_count'] ?><br>
                    <strong>Cars:</strong> <?= (int)$latest['car_count'] ?> |
                    <strong>Motorcycles:</strong> <?= (int)$latest['motorcycle_count'] ?> |
                    <strong>Buses:</strong> <?= (int)$latest['bus_count'] ?> |
                    <strong>Trucks:</strong> <?= (int)$latest['truck_count'] ?>
                </div>

                <video controls>
                    <source src="<?= htmlspecialchars($latest['output_video']) ?>" type="video/mp4">
                    Your browser does not support video playback.
                </video>
            <?php else: ?>
                <p>No processed video yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>