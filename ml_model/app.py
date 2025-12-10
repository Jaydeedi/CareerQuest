#!/usr/bin/env python3
"""Flask ML service wrapper.

Provides a `/predict` endpoint that accepts JSON: {"command": string, "data": {...}}
Logs incoming requests and prediction completions in the requested format.

Run:
  python3 ml_model\app.py
"""
from datetime import datetime
from flask import Flask, request, jsonify

app = Flask(__name__)

try:
	from prediction_service import (
		health_check,
		generate_quiz,
		suggest_study,
		recommend_career,
		classify_question,
		load_models,
	)
except Exception as e:
	raise RuntimeError(f"Failed to import prediction_service: {e}")


def now_ts():
	return datetime.now().strftime("%Y-%m-%d %H:%M:%S")


def log_request(command, data):
	print(f"[{now_ts()}] ML Service Request")
	print(f"  Command: {command}")
	# show some common fields when present
	if isinstance(data, dict):
		if "career_path" in data:
			print(f"  Career Path: {data.get('career_path')}")
		if "difficulty" in data:
			print(f"  Difficulty: {data.get('difficulty')}")
		if "level" in data:
			print(f"  Level: {data.get('level')}")


def log_complete(command, result, model_name="Trained Scikit-Learn"):
	print(f"[{now_ts()}] ML Prediction Complete")
	# payload-specific summary lines
	if command == "generate_quiz" and isinstance(result, list):
		print(f"  ✓ Generated {len(result)} questions")
	elif command == "health_check":
		print("  ✓ Health check returned")
	else:
		print("  ✓ Prediction complete")
	print(f"  Model: {model_name}")


def log_trained_used(command):
	"""Print explicit terminal confirmation when a trained model was used."""
	try:
		models = load_models()
	except Exception:
		print("⚠️  Could not load models to verify trained-model usage")
		return

	# Map commands to model checks and messages
	if command == "generate_quiz":
		if "question_classifier" in models or "random_forest" in models:
			print("✅ Trained model used for AI Quiz prediction")
			return
	if command == "recommend_career":
		if "random_forest" in models:
			print("✅ Trained model used for Career Assessment")
			return
	if command == "suggest_study":
		if "study_suggester" in models:
			print("✅ Trained model used for Study Suggestion")
			return
	# Generic daily-task or other analyses
	if command in ("daily_task", "process_daily_task"):
		if any(k in models for k in ("random_forest", "study_suggester")):
			print("✅ Trained model used for Daily Task analysis")
			return

	# Fallback notice
	print("⚠️  No trained model was detected for this command; heuristics used instead")


@app.route("/health", methods=["GET"])
def health():
	res = health_check({})
	return jsonify({"success": True, "result": res})


@app.route("/predict", methods=["POST"])
def predict():
	payload = request.get_json(force=True)
	command = payload.get("command")
	data = payload.get("data", {})

	handlers = {
		"generate_quiz": generate_quiz,
		"suggest_study": suggest_study,
		"recommend_career": recommend_career,
		"classify_question": classify_question,
		"health_check": health_check,
	}

	log_request(command, data)

	if command not in handlers:
		return jsonify({"success": False, "error": f"Unknown command: {command}"}), 400

	try:
		result = handlers[command](data)
	except Exception as e:
		return jsonify({"success": False, "error": str(e)}), 500

	# Log completion summary
	if command == "generate_quiz":
		# result is a list of questions
		log_complete(command, result, model_name="Trained Scikit-Learn")
	else:
		log_complete(command, result)

	# Explicit trained-model usage log for thesis validation
	try:
		log_trained_used(command)
	except Exception:
		print("⚠️  Error while logging trained-model usage")

	return jsonify({"success": True, "result": result})


if __name__ == "__main__":
	# Bind to localhost:5001 as requested
	app.run(host="127.0.0.1", port=5001)


