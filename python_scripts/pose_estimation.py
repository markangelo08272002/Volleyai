import os
import json

# For debugging: Print environment variables
with open('env_vars_debug.log', 'w') as f:
    f.write(json.dumps(dict(os.environ), indent=4))

import cv2
import mediapipe as mp
import numpy as np
import sys

# Initialize MediaPipe Pose
mp_pose = mp.solutions.pose
mp_drawing = mp.solutions.drawing_utils

def process_video_for_keypoints(video_path, output_dir):
    if not os.path.exists(output_dir):
        os.makedirs(output_dir)

    cap = cv2.VideoCapture(video_path)
    if not cap.isOpened():
        print(f"Error: Could not open video {video_path}")
        return None

    frame_width = int(cap.get(cv2.CAP_PROP_FRAME_WIDTH))
    frame_height = int(cap.get(cv2.CAP_PROP_FRAME_HEIGHT))
    fps = cap.get(cv2.CAP_PROP_FPS)

    all_keypoints_data = []

    with mp_pose.Pose(min_detection_confidence=0.5, min_tracking_confidence=0.5) as pose:
        frame_count = 0
        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break

            # Recolor image to RGB
            image = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
            image.flags.writeable = False

            # Make detection
            results = pose.process(image)

            # Recolor image back to BGR
            image.flags.writeable = True
            image = cv2.cvtColor(image, cv2.COLOR_RGB2BGR)

            frame_keypoints = []
            if results.pose_landmarks:
                for i, landmark in enumerate(results.pose_landmarks.landmark):
                    frame_keypoints.append({
                        'id': i,
                        'x': landmark.x,
                        'y': landmark.y,
                        'z': landmark.z,
                        'visibility': landmark.visibility
                    })
            all_keypoints_data.append(frame_keypoints)
            frame_count += 1

    cap.release()

    # Save keypoints to a JSON file
    video_filename = os.path.basename(video_path)
    output_json_path = os.path.join(output_dir, f"{os.path.splitext(video_filename)[0]}_keypoints.json")
    with open(output_json_path, 'w') as f:
        json.dump(all_keypoints_data, f, indent=4)

    print(f"Keypoints saved to {output_json_path}")
    return output_json_path

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python pose_estimation.py <video_path> <output_directory>")
        sys.exit(1)

    video_path = sys.argv[1]
    output_directory = sys.argv[2]

    process_video_for_keypoints(video_path, output_directory)
