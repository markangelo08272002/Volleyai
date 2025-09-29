
import matplotlib.pyplot as plt
import matplotlib.animation as animation
import numpy as np
import os

# Define the key body parts for the stickman
body_parts = {
    "head": (0, 8), "neck": (0, 7),
    "left_shoulder": (-1, 6), "left_elbow": (-2, 4), "left_wrist": (-3, 2),
    "right_shoulder": (1, 6), "right_elbow": (2, 4), "right_wrist": (3, 2),
    "torso": (0, 3),
    "left_hip": (-1, 2), "left_knee": (-1.5, 0), "left_ankle": (-2, -2),
    "right_hip": (1, 2), "right_knee": (1.5, 0), "right_ankle": (2, -2)
}

# Define the connections between body parts to draw the stickman
connections = [
    ("head", "neck"), ("neck", "torso"),
    ("neck", "left_shoulder"), ("left_shoulder", "left_elbow"), ("left_elbow", "left_wrist"),
    ("neck", "right_shoulder"), ("right_shoulder", "right_elbow"), ("right_elbow", "right_wrist"),
    ("torso", "left_hip"), ("left_hip", "left_knee"), ("left_knee", "left_ankle"),
    ("torso", "right_hip"), ("right_hip", "right_knee"), ("right_knee", "right_ankle")
]

# Define the keyframes of a proper volleyball spike
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

# Interpolate between keyframes to create a smoother animation
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

all_frames = []
for i in range(len(keyframes) - 1):
    all_frames.extend(interpolate(keyframes[i], keyframes[i+1], 10))

# Set up the plot
fig, ax = plt.subplots()
ax.set_xlim(-5, 5)
ax.set_ylim(-3, 13)
ax.set_aspect('equal', adjustable='box')
plt.axis('off')

# Initialize the plot with the first frame
lines = []
for conn in connections:
    part1, part2 = conn
    x1, y1 = all_frames[0][part1]
    x2, y2 = all_frames[0][part2]
    line, = ax.plot([x1, x2], [y1, y2], 'bo-')
    lines.append(line)

# Animation function
def update(frame_num, all_frames, lines):
    frame_data = all_frames[frame_num]
    for i, conn in enumerate(connections):
        part1, part2 = conn
        x1, y1 = frame_data[part1]
        x2, y2 = frame_data[part2]
        lines[i].set_data([x1, x2], [y1, y2])
    return lines

# Create the animation
ani = animation.FuncAnimation(fig, update, frames=len(all_frames),
                              fargs=(all_frames, lines), blit=True, interval=50)

# Save the animation
output_path = os.path.join("public", "images", "proper_spike_stickman.gif")
writer = animation.PillowWriter(fps=20)
ani.save(output_path, writer=writer)

print(f"Animation saved to {output_path}")
