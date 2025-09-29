import json, os, sys
import numpy as np
import joblib
import pandas as pd
from collections import deque

# This list must be identical to the one in generate_dataset.py and train_action_model.py
FEATURE_COLUMNS = [
    "total_jump_norm", "right_elbow_angle_range", "left_elbow_angle_range",
    "max_right_wrist_velocity", "max_left_wrist_velocity", "max_hip_vertical_velocity",
    "right_wrist_rise", "left_wrist_rise", "hands_above_shoulders_ratio",
    "wrists_close_ratio", "max_torso_tilt_deg", "shoulder_rotation_range_x",
    "approach_speed_x"
]

def _moving_average(arr, k=5):
    if len(arr) == 0: return arr
    k = max(1, int(k))
    pad = k // 2
    padded = np.pad(arr, ((pad, pad), (0,0)) if arr.ndim==2 else (pad, pad), mode='edge')
    if arr.ndim==1:
        out = np.convolve(padded, np.ones(k)/k, mode='valid')
    else:
        out = np.vstack([np.convolve(padded[:,i], np.ones(k)/k, mode='valid') for i in range(arr.shape[1])]).T
    return out

def _angle(a, b, c):
    v1 = np.array(a) - np.array(b)
    v2 = np.array(c) - np.array(b)
    n1, n2 = np.linalg.norm(v1), np.linalg.norm(v2)
    if n1==0 or n2==0: return 0.0
    cosang = np.clip(np.dot(v1,v2)/(n1*n2), -1.0, 1.0)
    return np.degrees(np.arccos(cosang))

def analyze_keypoints_for_training(keypoints_json_path):
    """
    Analyzes keypoints from a JSON file and returns a dictionary of features.
    This function is intended for use in generating a training dataset.
    """
    with open(keypoints_json_path, 'r') as f:
        all_keypoints_data = json.load(f)

    L_SH, R_SH = 11, 12; L_EL, R_EL = 13, 14; L_WR, R_WR = 15, 16
    L_HIP, R_HIP = 23, 24; NOSE = 0

    frames = [{kp['id']: (kp['x'], kp['y'], kp['z'], kp.get('visibility',1.0)) for kp in frame} for frame in all_keypoints_data]

    xs, scale_list, center_list = [], [], []
    for mp in frames:
        if L_SH in mp and R_SH in mp:
            cx, cy, cz = (np.array(mp[L_SH][:3]) + np.array(mp[R_SH][:3])) / 2.0
            shoulder_w = max(1e-4, abs(mp[L_SH][0] - mp[R_SH][0]))
        elif L_HIP in mp and R_HIP in mp:
            cx, cy, cz = (np.array(mp[L_HIP]) + np.array(mp[R_HIP])) / 2.0
            shoulder_w = 0.15
        else:
            scale_list.append(None); center_list.append(None); xs.append(None)
            continue

        scale_list.append(shoulder_w); center_list.append((cx,cy,cz))
        def norm(pt): return ((pt[0]-cx)/shoulder_w, (pt[1]-cy)/shoulder_w, (pt[2]-cz)/shoulder_w)
        def g(idx): return norm(mp[idx]) if idx in mp else None
        xs.append({'L_SH': g(L_SH), 'R_SH': g(R_SH), 'L_EL': g(L_EL), 'R_EL': g(R_EL),
                   'L_WR': g(L_WR), 'R_WR': g(R_WR), 'L_HIP': g(L_HIP), 'R_HIP': g(R_HIP), 'NOSE': g(NOSE)})

    def series(key):
        arr = [xs[t][key] if xs[t] and xs[t][key] is not None else (np.nan, np.nan, np.nan) for t in range(len(xs))]
        return np.array(arr)

    def _ffill_bfill(A):
        A=A.copy();
        for i in range(A.shape[1]):
            s=pd.Series(A[:,i]); s=s.ffill().bfill(); A[:,i]=s.values
        return A

    keypoint_series = {k: _ffill_bfill(series(k)) for k in ['R_WR','L_WR','R_SH','L_SH','L_EL','R_EL','L_HIP','R_HIP','NOSE']}
    for k, v in keypoint_series.items(): keypoint_series[k] = _moving_average(v, k=5)

    RWR, LWR, RSH, LSH, REL, LEL, RHP, LHP, NO = [keypoint_series[k] for k in ['R_WR','L_WR','R_SH','L_SH','L_EL','R_EL','R_HIP','L_HIP','NOSE']]

    def y_of(A): return A[:,1]
    def x_of(A): return A[:,0]

    HIPS = (LHP + RHP) / 2.0
    SHOULDERS = (LSH + RSH) / 2.0
    hip_y = y_of(HIPS)
    total_jump = float(np.nanmax(hip_y) - np.nanmin(hip_y)) if hip_y.size > 0 else 0.0
    def rise_y(A): return (np.nanmin(y_of(A)) - np.nanmax(y_of(A))) if A.size > 0 else 0.0
    right_wrist_rise = rise_y(RWR)
    left_wrist_rise = rise_y(LWR)
    def max_vel(A): return float(np.nanmax(np.linalg.norm(A[1:] - A[:-1], axis=1))) if A.shape[0] > 1 else 0.0
    max_rwr_v = max_vel(RWR)
    max_lwr_v = max_vel(LWR)
    max_hip_v = max_vel(HIPS)
    torso_vec = HIPS - SHOULDERS
    torso_tilt = [_angle(v+(0,1,0), (0,0,0), (0,1,0)) for v in torso_vec]
    max_torso_tilt = float(np.nanmax(torso_tilt)) if len(torso_tilt) > 0 else 0.0
    shoulder_sep_x = x_of(LSH) - x_of(RSH)
    shoulder_rot_range = float(np.nanmax(shoulder_sep_x) - np.nanmin(shoulder_sep_x)) if shoulder_sep_x.size > 0 else 0.0
    def above_head_ratio(LWR, RWR, NO_or_SH):
        ref_y, lw_y, rw_y = y_of(NO_or_SH), y_of(LWR), y_of(RWR)
        return np.mean([lw_y[i] < ref_y[i] and rw_y[i] < ref_y[i] for i in range(len(ref_y))]) if len(ref_y)>0 else 0.0
    hands_above_shoulders_ratio = above_head_ratio(LWR, RWR, SHOULDERS)
    def wrists_together_ratio(LWR, RWR, threshold=0.6):
        return float(np.mean(np.linalg.norm(LWR - RWR, axis=1) < threshold)) if LWR.size>0 and RWR.size>0 else 0.0
    wrists_close_ratio = wrists_together_ratio(LWR, RWR)
    def elbow_series(SH, EL, WR):
        ang = [_angle(SH[i], EL[i], WR[i]) for i in range(min(len(SH),len(EL),len(WR)))]
        return (np.nanmax(ang), np.nanmin(ang)) if ang else (0, 180)
    max_RE, min_RE = elbow_series(RSH, REL, RWR)
    max_LE, min_LE = elbow_series(LSH, LEL, LWR)
    range_RE, range_LE = max_RE - min_RE, max_LE - min_LE
    approach_vx = float(np.nanmean(np.abs(x_of(HIPS)[1:] - x_of(HIPS)[:-1]))) if HIPS.shape[0] > 1 else 0.0

    features = {
        "total_jump_norm": abs(total_jump),
        "right_elbow_angle_range": abs(range_RE),
        "left_elbow_angle_range": abs(range_LE),
        "max_right_wrist_velocity": abs(max_rwr_v),
        "max_left_wrist_velocity": abs(max_lwr_v),
        "max_hip_vertical_velocity": abs(max_hip_v),
        "right_wrist_rise": right_wrist_rise,
        "left_wrist_rise": left_wrist_rise,
        "hands_above_shoulders_ratio": abs(hands_above_shoulders_ratio),
        "wrists_close_ratio": abs(wrists_close_ratio),
        "max_torso_tilt_deg": abs(max_torso_tilt),
        "shoulder_rotation_range_x": abs(shoulder_rot_range),
        "approach_speed_x": abs(approach_vx)
    }
    return features

def analyze_keypoints(keypoints_json_path, output_dir, expected_action=None):
    # --- 1. Load Model ---
    script_dir = os.path.dirname(__file__)
    model_path = os.path.join(script_dir, 'action_classifier_model.pkl')
    encoder_path = os.path.join(script_dir, 'action_label_encoder.pkl')

    try:
        model = joblib.load(model_path)
        encoder = joblib.load(encoder_path)
    except FileNotFoundError:
        print("Error: Model or encoder not found.")
        print(f"Looked for '{model_path}' and '{encoder_path}'")
        print("Please run train_action_model.py first.")
        return None, None

    os.makedirs(output_dir, exist_ok=True)

    # --- 2. Calculate Metrics from Keypoints ---
    metrics = analyze_keypoints_for_training(keypoints_json_path)
    if metrics is None:
        return None, None

    # --- 3. Predict Action with ML Model ---
    features_df = pd.DataFrame([metrics])
    # Ensure the order of columns in the DataFrame matches the training order
    features_df = features_df[FEATURE_COLUMNS]

    prediction_encoded = model.predict(features_df)
    recognized_action = encoder.inverse_transform(prediction_encoded)[0]
    
    # Get confidence scores
    try:
        probabilities = model.predict_proba(features_df)
        confidence = float(np.max(probabilities))
        scores = dict(zip(encoder.classes_, probabilities[0]))
        scores_str = ", ".join([f"{k}: {v:.2f}" for k, v in scores.items()])
    except AttributeError:
        confidence = 1.0
        scores = {recognized_action: 1.0}
        scores_str = f"{recognized_action}: 1.0"

    if expected_action and expected_action != recognized_action:
        pass

    # --- 4. Generate LLM Prompt ---
    prompt_intro = f"As an AI Volleyball Coach, analyze a player's {recognized_action}. "
    if expected_action and recognized_action != expected_action:
        prompt_intro += f"The player intended to do a {expected_action}, but the model detected a {recognized_action} with {confidence:.2f} confidence. Analyze the metrics to explain this discrepancy. "
    prompt_intro += "Use the normalized metrics below to provide feedback on strengths, areas to improve, and specific drills.\n"

    prompt = (
        f"{prompt_intro}\n"
        f"**Action Analysis:**\n"
        f"- Recognized Action: **{recognized_action.upper()}** (Confidence: {confidence:.2f})\n"
        f"- Model Scores: {scores_str}\n\n"
        f"**Key Performance Metrics (Normalized by Shoulder Width):**\n"
        f"- Jump Height: {metrics['total_jump_norm']:.2f}\n"
        f"- Dominant Arm Elbow Range (Right): {metrics['right_elbow_angle_range']:.1f}°\n"
        f"- Off-Arm Elbow Range (Left): {metrics['left_elbow_angle_range']:.1f}°\n"
        f"- Max Dominant Wrist Speed (Right): {metrics['max_right_wrist_velocity']:.2f}\n"
        f"- Max Off-Arm Wrist Speed (Left): {metrics['max_left_wrist_velocity']:.2f}\n"
        f"- Max Upward Wrist Rise (Right): {-metrics['right_wrist_rise']:.2f}\n"
        f"- Torso Tilt: {metrics['max_torso_tilt_deg']:.1f}°\n"
        f"- Shoulder Rotation: {metrics['shoulder_rotation_range_x']:.2f}\n"
        f"- Approach Speed: {metrics['approach_speed_x']:.2f}\n\n"
        "**Structure feedback as: 1) Strengths 2) Areas to improve 3) Drills.**"
    )

    # --- 5. Final Output ---
    final_metrics = {
        "recognized_action": recognized_action,
        "model_confidence": confidence,
        "model_scores": scores,
    }
    final_metrics.update(metrics) # Combine the calculated metrics with the prediction results

    output_data = {"metrics": final_metrics, "llm_prompt": prompt}
    base_name = os.path.splitext(os.path.basename(keypoints_json_path))[0]
    output_json_path = os.path.join(output_dir, f"{base_name}_analysis.json")
    with open(output_json_path, 'w') as f:
        json.dump(output_data, f, indent=4)

    print(f"Analysis saved to {output_json_path}")
    return output_json_path, prompt

if __name__ == "__main__":
    if len(sys.argv) < 3 or len(sys.argv) > 4:
        print("Usage: python action_analysis.py <keypoints_json_path> <output_directory> [expected_action]")
        sys.exit(1)

    keypoints_json_path = sys.argv[1]
    output_directory = sys.argv[2]
    expected_action = sys.argv[3] if len(sys.argv) == 4 else None

    analysis_file, llm_prompt = analyze_keypoints(keypoints_json_path, output_directory, expected_action)
    if llm_prompt:
        print(f"Final LLM Prompt: {llm_prompt}")
