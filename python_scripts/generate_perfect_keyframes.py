
import json
import os

# This data is copied from create_stickman_animation.py to represent the ideal motion
keyframes = [
    # Frame 1: Approach
    {
        "head": (0, 8), "neck": (0, 7), "torso": (0, 4),
        "left_shoulder": (-1, 6.5), "left_elbow": (-2, 5), "left_wrist": (-3, 3.5),
        "right_shoulder": (1, 6.5), "right_elbow": (2, 5), "right_wrist": (3, 3.5),
        "left_hip": (-1, 3), "left_knee": (-0.5, 1), "left_ankle": (-0.5, -1),
        "right_hip": (1, 3), "right_knee": (1.5, 1), "right_ankle": (1.5, -1)
    },
    # Frame 2: Take-off (arms back)
    {
        "head": (0, 8.5), "neck": (0, 7.5), "torso": (0, 4.5),
        "left_shoulder": (-1, 7), "left_elbow": (-2, 6), "left_wrist": (-3, 5),
        "right_shoulder": (1, 7), "right_elbow": (2, 6), "right_wrist": (3, 5),
        "left_hip": (-1, 3.5), "left_knee": (-1.5, 1.5), "left_ankle": (-1.5, -0.5),
        "right_hip": (1, 3.5), "right_knee": (1.5, 1.5), "right_ankle": (1.5, -0.5)
    },
    # Frame 3: In-air (cocking the arm)
    {
        "head": (0.5, 10), "neck": (0.5, 9), "torso": (0, 6),
        "left_shoulder": (-1, 8), "left_elbow": (-2, 7), "left_wrist": (-3, 6),
        "right_shoulder": (2, 8.5), "right_elbow": (3.5, 8), "right_wrist": (3, 6.5), # Arm cocked back
        "left_hip": (-0.5, 5), "left_knee": (-1, 3), "left_ankle": (-1, 1),
        "right_hip": (1.5, 5), "right_knee": (2, 3), "right_ankle": (2, 1)
    },
    # Frame 4: Contact
    {
        "head": (1, 10.5), "neck": (1, 9.5), "torso": (0.5, 6.5),
        "left_shoulder": (-1.5, 8), "left_elbow": (-2.5, 6.5), "left_wrist": (-3.5, 5),
        "right_shoulder": (2, 9), "right_elbow": (2.5, 10.5), "right_wrist": (3, 12), # Arm extended high
        "left_hip": (-0.5, 5.5), "left_knee": (-1, 3.5), "left_ankle": (-1, 1.5),
        "right_hip": (1.5, 5.5), "right_knee": (2, 3.5), "right_ankle": (2, 1.5)
    },
    # Frame 5: Follow-through
    {
        "head": (1.5, 9), "neck": (1.5, 8), "torso": (1, 5),
        "left_shoulder": (-0.5, 7), "left_elbow": (-1.5, 5.5), "left_wrist": (-2.5, 4),
        "right_shoulder": (2.5, 7.5), "right_elbow": (3, 6), "right_wrist": (2.5, 4.5), # Arm coming down
        "left_hip": (0, 4), "left_knee": (0, 2), "left_ankle": (0, 0),
        "right_hip": (2, 4), "right_knee": (2.5, 2), "right_ankle": (2.5, 0)
    },
    # Frame 6: Landing
    {
        "head": (0, 8), "neck": (0, 7), "torso": (0, 4),
        "left_shoulder": (-1, 6.5), "left_elbow": (-2, 5), "left_wrist": (-3, 3.5),
        "right_shoulder": (1, 6.5), "right_elbow": (2, 5), "right_wrist": (3, 3.5),
        "left_hip": (-1, 3), "left_knee": (-1.5, 1), "left_ankle": (-1.5, -1),
        "right_hip": (1, 3), "right_knee": (1.5, 1), "right_ankle": (1.5, -1)
    }
]

# Mapping from stickman parts to MediaPipe pose landmark indices
mediapipe_map = {
    "left_wrist": 15, "right_wrist": 16,
    "left_elbow": 13, "right_elbow": 14,
    "left_shoulder": 11, "right_shoulder": 12,
    "left_hip": 23, "right_hip": 24
    # Add other points if needed by the analysis script
}

def interpolate(start_frame, end_frame, steps):
    interpolated_frames = []
    for i in range(steps):
        t = i / float(steps)
        new_frame = {}
        for part in start_frame:
            x1, y1 = start_frame[part]
            x2, y2 = end_frame[part]
            new_frame[part] = (x1 + (x2 - x1) * t, y1 + (y2 - y1) * t)
        interpolated_frames.append(new_frame)
    return interpolated_frames

all_frames_stickman = []
for i in range(len(keyframes) - 1):
    all_frames_stickman.extend(interpolate(keyframes[i], keyframes[i+1], 10))

# Convert stickman frames to the format expected by action_analysis.py
all_keypoints_data = []
for frame_data in all_frames_stickman:
    frame_keypoints = []
    for part_name, coords in frame_data.items():
        if part_name in mediapipe_map:
            # Normalize coordinates to be roughly within [0, 1] as MediaPipe does
            # This is a simplified normalization for demonstration
            x = (coords[0] + 5) / 10 # Shift from [-5, 5] to [0, 10] then scale
            y = (13 - coords[1]) / 16 # Invert and scale from [-3, 13]
            z = 0 # Stickman is 2D, so Z is 0

            frame_keypoints.append({
                "id": mediapipe_map[part_name],
                "name": part_name,
                "x": x,
                "y": y,
                "z": z,
                "visibility": 0.99 # Assume high visibility
            })
    all_keypoints_data.append(frame_keypoints)

# Save the generated keypoints to a JSON file
output_dir = "storage/app/public/videos/processed"
if not os.path.exists(output_dir):
    os.makedirs(output_dir)

output_path = os.path.join(output_dir, "perfect_spike_keypoints.json")
with open(output_path, 'w') as f:
    json.dump(all_keypoints_data, f, indent=4)

print(f"Successfully generated perfect keypoints at: {output_path}")
