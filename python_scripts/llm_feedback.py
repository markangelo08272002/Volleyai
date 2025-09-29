import requests
import json
import sys

def get_llm_feedback(prompt, lm_studio_api_url="http://localhost:1234/v1/chat/completions"):
    headers = {
        "Content-Type": "application/json"
    }
    system_message = (
        "You are an expert varsity volleyball coach AI. "
        "Your first sentence must be the grade in the format 'Grade: [Your numerical grade here, e.g., 85]'. "
        "Following the grade, provide detailed, actionable, and positive feedback. "
        "Break your feedback into these sections: "
        "1. Strengths: Highlight what the player did well. "
        "2. Areas for Improvement: Pinpoint form or performance issues based on the metrics. "
        "3. Actionable Recommendations: Give specific drills or corrections to improve their volleyball spike. "
        "Maintain a supportive, motivating, and professional coaching tone." 
        "Finally, include a 'Grade' in the format 'Grade: [Your numerical grade here, e.g., 85]' at the end of your feedback."
    )
    data = {
        "model": "llama-3.2-1b-instruct", # This might need to be adjusted based on your LM Studio setup
        "messages": [
            {"role": "system", "content": system_message},
            {"role": "user", "content": prompt}
        ],
        "temperature": 0.7
    }

    try:
        response = requests.post(lm_studio_api_url, headers=headers, json=data)
        response.encoding = 'utf-8'
        response.raise_for_status() # Raise an HTTPError for bad responses (4xx or 5xx)
        result = response.json()
        feedback = result['choices'][0]['message']['content']
        print(feedback)
        return feedback
    except requests.exceptions.RequestException as e:
        print(f"Error communicating with LM Studio API: {e}")
        return f"Error: Could not get feedback from LLM. {e}"

if __name__ == "__main__":
    sys.stdout.reconfigure(encoding='utf-8')
    if len(sys.argv) != 2:
        print("Usage: python llm_feedback.py <prompt>")
        sys.exit(1)

    prompt = sys.argv[1]
    feedback = get_llm_feedback(prompt)
    # The feedback is printed to stdout, which Laravel can capture.
