# Traffic AI: Vision-Based AI System for Vehicle Detection, Classification, and Counting in Philippine Roadways

A web-based traffic monitoring and vehicle counting system built for our Data Analytics final project. It combines a **PHP/MySQL** web application with a **YOLOv8 + ByteTrack** computer vision pipeline to detect, track, and count vehicles from uploaded traffic videos, storing results for dashboard visualization and reporting.

---

## Key Features

- **Video Upload & Automated Processing (`upload_video.php`)**: Supports `.mp4`, `.avi`, `.mov`, and `.mkv` uploads, saves files to `uploads/input/`, and triggers the Python analysis pipeline.
- **YOLOv8 Vehicle Detection & Tracking (`process_video.py`)**: Uses a custom-trained YOLO model (`best.pt`) paired with ByteTrack (`bytetrack.yaml`) to track `car`, `motorcycle`, `bus`, and `truck` classes across frames.
- **Line-Crossing Counting Logic**: Counts each unique tracked vehicle once when its bounding-box centroid crosses the configured counting line (`LINE_START = (20, 660)` to `LINE_END = (950, 400)`), rendering bounding boxes, confidence scores, and a live count overlay onto the output video.
- **Analytics Dashboard (`dashboard.php`)**: Displays aggregate vehicle counts across all processed videos alongside the latest processed video playback and breakdown.
- **Historical Reports (`reports.php`)**: Lists all processed video runs with per-class counts, total counts, timestamps, and inline video playback.

## 🎥 Video Walkthrough
Watch the full prototype walkthrough here: https://drive.google.com/file/d/11Maxuc_RZT7Iwl_p9LhHKPbVrw-VfEW1/view?usp=sharing

## 📄 Prototype Screenshots
View the full PDF documentation of the prototype screens: [Prototype Screenshots (PDF)](./interface/interfaces.pdf)

---

## Tech Stack

| Layer | Technology |
| :--- | :--- |
| **Frontend / Web UI** | HTML5, CSS3, PHP |
| **Database** | MySQL (`mysqli` in PHP, `mysql-connector-python` in Python) |
| **Computer Vision** | Python, OpenCV (`cv2`), Ultralytics YOLO (`best.pt`), ByteTrack (`bytetrack.yaml`) |
| **Local Server** | XAMPP / Apache + MySQL (or any PHP/MySQL stack) |

---

## Project Structure

```text
electives/
├── best.pt               # Trained YOLO model weights for vehicle detection
├── db.php                # MySQL database connection configuration
├── dashboard.php         # Main analytics dashboard (KPI cards + latest video)
├── upload_video.php      # Video upload form and Python pipeline trigger
├── reports.php           # Tabular view of all processed traffic reports
├── process_video.py      # Vehicle detection, tracking, counting, and DB logging
└── uploads/
    ├── input/            # Uploaded raw traffic videos
    └── output/           # Annotated output videos (prefixed with processed_)
```

---

## Database Setup

No `.sql` dump is included in the repository. Create the `electives` database and `traffic_reports` table in MySQL (e.g., via phpMyAdmin or MySQL CLI) before running the app:

```sql
CREATE DATABASE IF NOT EXISTS electives;
USE electives;

CREATE TABLE IF NOT EXISTS traffic_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    video_name VARCHAR(255) NOT NULL,
    input_video VARCHAR(255) NOT NULL,
    output_video VARCHAR(255) NOT NULL,
    car_count INT NOT NULL DEFAULT 0,
    motorcycle_count INT NOT NULL DEFAULT 0,
    bus_count INT NOT NULL DEFAULT 0,
    truck_count INT NOT NULL DEFAULT 0,
    total_count INT NOT NULL DEFAULT 0,
    processed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Database Configuration

Both `db.php` and `process_video.py` connect using the following default credentials:

| Parameter | Default Value |
| :--- | :--- |
| **Host** | `localhost` |
| **User** | `root` |
| **Password** | `""` (empty) |
| **Database** | `electives` |

Update `db.php` and `DB_CONFIG` in `process_video.py` if your MySQL setup uses different credentials.

---

## Installation & Setup

### 1. Prerequisites

- **PHP & MySQL** (e.g., XAMPP, WAMP, Laragon, or Apache + MySQL)
- **Python 3.10+**
- Required Python packages:

```bash
pip install ultralytics opencv-python mysql-connector-python
```

### 2. Place Project Files

Copy the `electives/` folder into your web server's document root (for example, `C:\xampp\htdocs\electives` on Windows).

### 3. Configure the Python Executable Path

In `upload_video.php` (line 23), update `$pythonPath` to point to your local Python executable where the packages above are installed:

```php
$pythonPath = 'C:\\Path\\To\\Your\\python.exe';
```

### 4. (Optional) Adjust Detection & Counting Line Settings

In `process_video.py`, you can tune the model and counting line coordinates to match your camera angle:

- `LINE_START = (20, 660)` and `LINE_END = (950, 400)` — Start and end `(x, y)` coordinates of the red counting line.
- `CONFIDENCE = 0.25` — Minimum detection confidence threshold.
- `TARGET_CLASSES = {"car", "motorcycle", "bus", "truck"}` — Vehicle classes included in counts.

---

## Usage

1. Start **Apache** and **MySQL** from your server control panel.
2. Open `http://localhost/electives/dashboard.php` in your browser.
3. Navigate to **Upload Video** (`upload_video.php`), select a traffic video (`.mp4`, `.avi`, `.mov`, or `.mkv`), and click **Upload and Process**.
4. Once processing completes, view the summary output on the upload page, inspect the latest annotated video on **Dashboard** (`dashboard.php`), or review all historical runs on **Reports** (`reports.php`).

---

## Project Team & Course Info

- **Course**: Data Analytics
- **Author**: Jericho Antonio O. Castillo
