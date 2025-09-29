
import os
import sys
import pandas as pd
import subprocess
from action_analysis import analyze_keypoints_for_training

# Add the python_scripts directory to the python path to allow calling other scripts
sys.path.append(os.path.dirname(os.path.abspath(__file__)))

def process_videos_in_directory(root_video_dir, output_csv_path):
    """
    Processes all videos in a directory, runs pose estimation,
    extracts features, and saves them to a CSV file.
    """
    all_features = []
    # Expects a directory structure like:
    # - root_video_dir
    #   - spike
    #     - video1.mp4
    #     - video2.mp4
    #   - dive
    #     - video3.mp4
    for action_name in os.listdir(root_video_dir):
        action_dir = os.path.join(root_video_dir, action_name)
        if not os.path.isdir(action_dir):
            continue

        print(f"Processing action: {action_name}")
        for video_file in os.listdir(action_dir):
            if not video_file.endswith((".mp4", ".mov", ".avi")):
                continue

            video_path = os.path.join(action_dir, video_file)
            base_name = os.path.splitext(video_file)[0]
            keypoints_dir = os.path.join(action_dir, "keypoints")
            os.makedirs(keypoints_dir, exist_ok=True)
            keypoints_json_path = os.path.join(keypoints_dir, f"{base_name}_keypoints.json")

            # 1. Run pose estimation to generate keypoints JSON
            print(f"  Running pose estimation for {video_file}...")
            subprocess.run([
                sys.executable,
                os.path.join(os.path.dirname(__file__), "pose_estimation.py"),
                video_path,
                keypoints_dir
            ], check=True)

            # 2. Analyze keypoints to get features
            print(f"  Analyzing keypoints for {video_file}...")
            features = analyze_keypoints_for_training(keypoints_json_path)
            if features:
                features['action'] = action_name
                all_features.append(features)

    if not all_features:
        print("No features were extracted. Please check your video directory structure.")
        return

    # Create a DataFrame and save to CSV
    df = pd.DataFrame(all_features)
    # Reorder columns to have 'action' as the last column
    cols = [c for c in df.columns if c != 'action'] + ['action']
    df = df[cols]
    df.to_csv(output_csv_path, index=False)
    print(f"Successfully generated dataset with {len(df)} samples.")
    print(f"Dataset saved to: {output_csv_path}")


if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Usage: python generate_real_dataset.py <path_to_video_directory>")
        print("The video directory should contain subdirectories for each action type.")
        sys.exit(1)

    video_directory = sys.argv[1]
    output_csv = os.path.join(os.path.dirname(__file__), "action_data.csv")
    process_videos_in_directory(video_directory, output_csv)
