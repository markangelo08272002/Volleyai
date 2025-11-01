import json
import sys
import os
import matplotlib.pyplot as plt
import matplotlib.animation as animation
import numpy as np

# Define the connections between body parts to draw the stickman
connections = [
    ("head", "neck"), ("neck", "torso"),
    ("neck", "left_shoulder"), ("left_shoulder", "left_elbow"), ("left_elbow", "left_wrist"),
    ("neck", "right_shoulder"), ("right_shoulder", "right_elbow"), ("right_elbow", "right_wrist"),
    ("torso", "left_hip"), ("left_hip", "left_knee"), ("left_knee", "left_ankle"),
    ("torso", "right_hip"), ("right_hip", "right_knee"), ("right_knee", "right_ankle")
]

def main(keypoints_path, output_path):
    with open(keypoints_path, 'r') as f:
        keypoints_data = json.load(f)

    # Set up the plot
    fig, ax = plt.subplots()
    ax.set_xlim(0, 1)
    ax.set_ylim(0, 1)
    ax.set_aspect('equal', adjustable='box')
    plt.axis('off')

    # Invert the y-axis
    ax.invert_yaxis()

    # Initialize the plot with the first frame
    lines = []
    for conn in connections:
        part1, part2 = conn
        
        # Find the keypoints for the connected parts
        kp1 = next((kp for kp in keypoints_data[0] if kp['name'] == part1), None)
        kp2 = next((kp for kp in keypoints_data[0] if kp['name'] == part2), None)

        if kp1 and kp2:
            line, = ax.plot([kp1['x'], kp2['x']], [kp1['y'], kp2['y']], 'bo-')
            lines.append(line)

    # Animation function
    def update(frame_num, keypoints_data, lines):
        frame_keypoints = keypoints_data[frame_num]
        for i, conn in enumerate(connections):
            part1, part2 = conn
            
            kp1 = next((kp for kp in frame_keypoints if kp['name'] == part1), None)
            kp2 = next((kp for kp in frame_keypoints if kp['name'] == part2), None)

            if kp1 and kp2:
                lines[i].set_data([kp1['x'], kp2['x']], [kp1['y'], kp2['y']])
        return lines

    # Create the animation
    ani = animation.FuncAnimation(fig, update, frames=len(keypoints_data),
                                  fargs=(keypoints_data, lines), blit=True, interval=50)

    # Save the animation
    writer = animation.PillowWriter(fps=20)
    ani.save(output_path, writer=writer)

    print(f"Animation saved to {output_path}")

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python generate_stickman_from_keypoints.py <keypoints_json_path> <output_gif_path>")
        sys.exit(1)
    
    keypoints_json_path = sys.argv[1]
    output_gif_path = sys.argv[2]
    
    main(keypoints_json_path, output_gif_path)