import pandas as pd
import joblib
import os
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestClassifier
from sklearn.metrics import accuracy_score, classification_report
from sklearn.preprocessing import LabelEncoder

# This list must be identical to the one in generate_dataset.py and action_analysis.py
FEATURE_COLUMNS = [
    "total_jump_norm", "right_elbow_angle_range", "left_elbow_angle_range",
    "max_right_wrist_velocity", "max_left_wrist_velocity", "max_hip_vertical_velocity",
    "right_wrist_rise", "left_wrist_rise", "hands_above_shoulders_ratio",
    "wrists_close_ratio", "max_torso_tilt_deg", "shoulder_rotation_range_x",
    "approach_speed_x"
]

def train_model():
    """Loads data, trains a classifier, and saves the model and encoder."""
    script_dir = os.path.dirname(__file__)
    data_path = os.path.join(script_dir, 'action_data.csv')
    model_path = os.path.join(script_dir, 'action_classifier_model.pkl')
    encoder_path = os.path.join(script_dir, 'action_label_encoder.pkl')

    # Load the dataset
    try:
        df = pd.read_csv(data_path)
    except FileNotFoundError:
        print(f"Error: Dataset not found at {data_path}")
        print("Please run generate_dataset.py first.")
        return

    print(f"Loaded dataset with {len(df)} records.")

    # Prepare features (X) and labels (y)
    X = df[FEATURE_COLUMNS]
    y = df['action']

    # Encode string labels into numbers
    encoder = LabelEncoder()
    y_encoded = encoder.fit_transform(y)

    # Split data into training and testing sets
    X_train, X_test, y_train, y_test = train_test_split(
        X, y_encoded, test_size=0.2, random_state=42, stratify=y_encoded
    )

    print(f"Training on {len(X_train)} samples, testing on {len(X_test)} samples.")

    # Initialize and train the Random Forest Classifier
    model = RandomForestClassifier(n_estimators=100, random_state=42, class_weight='balanced')
    model.fit(X_train, y_train)

    # Evaluate the model
    y_pred = model.predict(X_test)
    accuracy = accuracy_score(y_test, y_pred)
    print(f"\nModel Accuracy: {accuracy * 100:.2f}%")

    # Show detailed report
    print("\nClassification Report:")
    # Use encoder to get back original labels for the report
    target_names = encoder.classes_
    print(classification_report(y_test, y_pred, target_names=target_names))

    # Save the trained model and the label encoder
    joblib.dump(model, model_path)
    joblib.dump(encoder, encoder_path)

    print(f"Model saved to: {model_path}")
    print(f"Label encoder saved to: {encoder_path}")

if __name__ == "__main__":
    train_model()
