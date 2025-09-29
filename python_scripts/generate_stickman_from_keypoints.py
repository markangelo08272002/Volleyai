
import matplotlib
matplotlib.use('Agg')
import matplotlib.pyplot as plt
import matplotlib.animation as animation
import numpy as np
import json

# Define the connections between body parts to draw the stickman
# These are the MediaPipe pose connections
POSE_CONNECTIONS = [
    (11, 12), (11, 23), (12, 24), (23, 24), (11, 13), (13, 15), (12, 14), (14, 16),
    (23, 25), (25, 27), (24, 26), (26, 28)
]

def generate_animation_from_keypoints(keypoints_json_path, output_gif_path):
    with open(keypoints_json_path, 'r') as f:
        all_keypoints_data = json.load(f)

    fig, ax = plt.subplots()
    ax.set_aspect('equal', adjustable='box')
    plt.axis('off')
    ax.set_facecolor('black')
    fig.set_facecolor('black')

    # Determine the bounds of the animation
    all_x = [kp['x'] for frame in all_keypoints_data for kp in frame]
    all_y = [kp['y'] for frame in all_keypoints_data for kp in frame]
    if not all_x or not all_y:
        print("No keypoints found in the data.")
        return

    x_min, x_max = min(all_x), max(all_x)
    y_min, y_max = min(all_y), max(all_y)
    ax.set_xlim(x_min - 0.1, x_max + 0.1)
    ax.set_ylim(y_min - 0.1, y_max + 0.1)
    ax.invert_yaxis() # Invert y-axis to match video coordinates

    lines = []
    for _ in POSE_CONNECTIONS:
        line, = ax.plot([], [], 'w-', lw=2)
        lines.append(line)

    def update(frame_num, all_frames, lines):
        frame_data = all_frames[frame_num]
        landmarks = {kp['id']: kp for kp in frame_data}

        for i, conn in enumerate(POSE_CONNECTIONS):
            start_kp = landmarks.get(conn[0])
            end_kp = landmarks.get(conn[1])

            if start_kp and end_kp and start_kp.get('visibility', 0) > 0.5 and end_kp.get('visibility', 0) > 0.5:
                lines[i].set_data([start_kp['x'], end_kp['x']], [start_kp['y'], end_kp['y']])
            else:
                lines[i].set_data([], []) # Hide the line if keypoints are not visible
        return lines

    ani = animation.FuncAnimation(fig, update, frames=len(all_keypoints_data),
                                  fargs=(all_keypoints_data, lines), blit=True, interval=50)

    writer = animation.PillowWriter(fps=20)
    ani.save(output_gif_path, writer=writer)
    plt.close(fig)
    print(f"Animation saved to {output_gif_path}")

if __name__ == "__main__":
    if len(sys.argv) != 3:
        print("Usage: python generate_stickman_from_keypoints.py <keypoints_json_path> <output_gif_path>")
        sys.exit(1)

    keypoints_path = sys.argv[1]
    output_path = sys.argv[2]
    generate_animation_from_keypoints(keypoints_path, output_path)
