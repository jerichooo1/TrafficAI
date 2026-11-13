<?php
include 'db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['video']) && $_FILES['video']['error'] === 0) {
        $uploadDir = __DIR__ . "/uploads/input/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $videoName = time() . "_" . basename($_FILES['video']['name']);
        $targetPath = $uploadDir . $videoName;

        $allowedExtensions = ['mp4', 'avi', 'mov', 'mkv'];
        $ext = strtolower(pathinfo($videoName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExtensions)) {
            $message = "Invalid video format. Please upload MP4, AVI, MOV, or MKV.";
        } else {
            if (move_uploaded_file($_FILES['video']['tmp_name'], $targetPath)) {
                $pythonPath = 'C:\\Users\\Jericho\\AppData\\Local\\Programs\\Python\\Python314\\python.exe';
                $scriptPath = __DIR__ . '\\process_video.py';

                $escapedPython = escapeshellarg($pythonPath);
                $escapedScript = escapeshellarg($scriptPath);
                $escapedVideo = escapeshellarg($targetPath);

                $command = "$escapedPython $escapedScript $escapedVideo 2>&1";
                $output = shell_exec($command);

                $message = "Video uploaded and processed successfully.<br><pre>" . htmlspecialchars($output) . "</pre>";
            } else {
                $message = "Failed to upload video.";
            }
        }
    } else {
        $message = "Please select a video file.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Video | Traffic Dashboard</title>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1f2937;
        }
        .container {
            max-width: 700px;
            margin: 60px auto;
            background: #fff;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        h1 {
            margin-top: 0;
            color: #0f172a;
        }
        .form-group {
            margin-bottom: 20px;
        }
        input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #d1d5db;
            border-radius: 10px;
            background: #fff;
        }
        button {
            background: #2563eb;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 15px;
        }
        button:hover {
            background: #1d4ed8;
        }
        .message {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
        }
        .links {
            margin-top: 25px;
        }
        .links a {
            text-decoration: none;
            margin-right: 15px;
            color: #2563eb;
            font-weight: 600;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
            background: #111827;
            color: #e5e7eb;
            padding: 15px;
            border-radius: 10px;
            overflow: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Traffic Video</h1>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <input type="file" name="video" accept="video/*" required>
            </div>
            <button type="submit">Upload and Process</button>
        </form>

        <?php if (!empty($message)): ?>
            <div class="message"><?= $message ?></div>
        <?php endif; ?>

        <div class="links">
            <a href="dashboard.php">Dashboard</a>
            <a href="reports.php">Reports</a>
        </div>
    </div>
</body>
</html>