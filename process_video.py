import os
import sys
import cv2
import mysql.connector
from ultralytics import YOLO

# =========================
# CONFIG
# =========================
MODEL_PATH = os.path.join(os.path.dirname(__file__), "best.pt")
BASE_DIR = os.path.dirname(__file__)
OUTPUT_DIR = os.path.join(BASE_DIR, "uploads", "output")

LINE_START = (20, 660)
LINE_END = (950, 400)

CONFIDENCE = 0.25
TRACKER = "bytetrack.yaml"
TARGET_CLASSES = {"car", "motorcycle", "bus", "truck"}

DB_CONFIG = {
    "host": "localhost",
    "user": "root",
    "password": "",
    "database": "electives"  # change if needed
}

# =========================
# HELPERS
# =========================
def get_center(x1, y1, x2, y2):
    return int((x1 + x2) / 2), int((y1 + y2) / 2)

def ccw(A, B, C):
    return (C[1] - A[1]) * (B[0] - A[0]) > (B[1] - A[1]) * (C[0] - A[0])

def intersect(A, B, C, D):
    return ccw(A, C, D) != ccw(B, C, D) and ccw(A, B, C) != ccw(A, B, D)

def draw_text_with_bg(img, text, org, font_scale=0.65, text_color=(255, 255, 255), bg_color=(255, 255, 0), thickness=2):
    font = cv2.FONT_HERSHEY_SIMPLEX
    (w, h), _ = cv2.getTextSize(text, font, font_scale, thickness)
    x, y = org
    cv2.rectangle(img, (x, y - h - 8), (x + w + 8, y + 4), bg_color, -1)
    cv2.putText(img, text, (x + 4, y - 4), font, font_scale, text_color, thickness, cv2.LINE_AA)

# =========================
# MAIN
# =========================
if len(sys.argv) < 2:
    print("No input video path provided.")
    sys.exit(1)

input_video_path = sys.argv[1]

if not os.path.exists(input_video_path):
    print(f"Input video not found: {input_video_path}")
    sys.exit(1)

if not os.path.exists(MODEL_PATH):
    print(f"Model not found: {MODEL_PATH}")
    sys.exit(1)

os.makedirs(OUTPUT_DIR, exist_ok=True)

video_name = os.path.basename(input_video_path)
output_video_name = "processed_" + os.path.splitext(video_name)[0] + ".mp4"
output_video_path = os.path.join(OUTPUT_DIR, output_video_name)

model = YOLO(MODEL_PATH)

cap = cv2.VideoCapture(input_video_path)
if not cap.isOpened():
    print(f"Could not open video: {input_video_path}")
    sys.exit(1)

frame_width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
frame_height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
fps = cap.get(cv2.CAP_PROP_FPS)

fourcc = cv2.VideoWriter_fourcc(*"mp4v")
out = cv2.VideoWriter(output_video_path, fourcc, fps, (frame_width, frame_height))

track_history = {}
counted_ids = set()

class_counts = {
    "car": 0,
    "motorcycle": 0,
    "bus": 0,
    "truck": 0
}

while True:
    ret, frame = cap.read()
    if not ret:
        break

    results = model.track(
        source=frame,
        persist=True,
        tracker=TRACKER,
        conf=CONFIDENCE,
        imgsz=640,
        verbose=False
    )

    annotated = frame.copy()

    cv2.line(annotated, LINE_START, LINE_END, (0, 0, 255), 3)
    draw_text_with_bg(annotated, "COUNT LINE", (LINE_START[0], LINE_START[1] - 10), font_scale=0.6, bg_color=(0, 0, 255), text_color=(255, 255, 255))

    if results and results[0].boxes is not None and results[0].boxes.id is not None:
        boxes = results[0].boxes.xyxy.cpu().numpy().astype(int)
        cls_ids = results[0].boxes.cls.cpu().numpy().astype(int)
        confs = results[0].boxes.conf.cpu().numpy()
        track_ids = results[0].boxes.id.cpu().numpy().astype(int)
        names = model.names

        for box, cls_id, conf, track_id in zip(boxes, cls_ids, confs, track_ids):
            x1, y1, x2, y2 = box
            class_name = names[cls_id]

            if class_name not in TARGET_CLASSES:
                continue

            center = get_center(x1, y1, x2, y2)

            cv2.rectangle(annotated, (x1, y1), (x2, y2), (255, 255, 0), 2)
            cv2.circle(annotated, center, 4, (0, 0, 255), -1)

            label = f"{class_name} {conf:.2f}"
            label_y = y1 - 10 if y1 - 10 > 20 else y1 + 25
            draw_text_with_bg(
                annotated,
                label,
                (x1, label_y),
                font_scale=0.55,
                bg_color=(255, 255, 0),
                text_color=(0, 0, 0),
                thickness=1
            )

            if track_id in track_history and track_id not in counted_ids:
                prev_center = track_history[track_id]
                if intersect(prev_center, center, LINE_START, LINE_END):
                    class_counts[class_name] += 1
                    counted_ids.add(track_id)

            track_history[track_id] = center

    overlay = annotated.copy()
    cv2.rectangle(overlay, (15, 15), (270, 185), (0, 0, 0), -1)
    cv2.addWeighted(overlay, 0.35, annotated, 0.65, 0, annotated)

    cv2.putText(annotated, "Vehicle Counts", (30, 45), cv2.FONT_HERSHEY_SIMPLEX, 1.0, (0, 255, 0), 2, cv2.LINE_AA)
    cv2.putText(annotated, f"Car: {class_counts['car']}", (30, 80), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2, cv2.LINE_AA)
    cv2.putText(annotated, f"Motorcycle: {class_counts['motorcycle']}", (30, 110), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2, cv2.LINE_AA)
    cv2.putText(annotated, f"Bus: {class_counts['bus']}", (30, 140), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2, cv2.LINE_AA)
    cv2.putText(annotated, f"Truck: {class_counts['truck']}", (30, 170), cv2.FONT_HERSHEY_SIMPLEX, 0.8, (0, 255, 0), 2, cv2.LINE_AA)

    out.write(annotated)

cap.release()
out.release()

total_count = class_counts["car"] + class_counts["motorcycle"] + class_counts["bus"] + class_counts["truck"]

relative_output_path = "uploads/output/" + output_video_name
relative_input_path = "uploads/input/" + video_name

try:
    conn = mysql.connector.connect(**DB_CONFIG)
    cursor = conn.cursor()

    sql = """
        INSERT INTO traffic_reports 
        (video_name, input_video, output_video, car_count, motorcycle_count, bus_count, truck_count, total_count)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
    """

    values = (
        video_name,
        relative_input_path,
        relative_output_path,
        class_counts["car"],
        class_counts["motorcycle"],
        class_counts["bus"],
        class_counts["truck"],
        total_count
    )

    cursor.execute(sql, values)
    conn.commit()

    print("Processing complete.")
    print(f"Video: {video_name}")
    print(f"Car: {class_counts['car']}")
    print(f"Motorcycle: {class_counts['motorcycle']}")
    print(f"Bus: {class_counts['bus']}")
    print(f"Truck: {class_counts['truck']}")
    print(f"Total: {total_count}")

except mysql.connector.Error as err:
    print(f"Database error: {err}")

finally:
    if 'cursor' in locals():
        cursor.close()
    if 'conn' in locals() and conn.is_connected():
        conn.close()