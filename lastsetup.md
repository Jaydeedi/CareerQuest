**Last Setup Guide**

This document lists the minimal steps to reproduce the development environment and run the project exactly as configured on the original machine. It assumes a Windows laptop (PowerShell) and that you have Git installed.

**Prerequisites:**
- **Python:** Install Python 3.11 (or 3.10+). Use the official installer and check "Add Python to PATH" if you prefer global python commands.
- **Node.js & npm:** Install Node.js (recommended LTS) which includes `npm`.
- **Git:** For cloning the repository.

**1. Clone repository**

Download the project to your laptop:

```powershell
git clone <your-repo-url> careerquestprojectFinal
cd careerquestprojectFinal
```

**2. Create and activate Python virtual environment**

Open PowerShell and run:

```powershell
py -3 -m venv .venv
.\.venv\Scripts\Activate.ps1
# or using the py launcher
# py -3 -m venv .venv
# . .venv/Scripts/activate
```

Notes:
- If `py` isn't available, use the installed `python` executable (e.g. `python -m venv .venv`).

**3. Install Python dependencies for ML service**

Install the packages used by the ML service (Flask + scikit-learn etc.):

```powershell
pip install -r ml_model/requirements.txt
```

If you encounter `numpy` / `scikit-learn` compatibility issues when loading pickled models, use the same versions used originally: for example:

```powershell
pip install --upgrade --force-reinstall scikit-learn==1.3.0 numpy==1.25.3 joblib==1.3.2 scipy
```

Adjust versions if you re-train models on a different environment.

**4. (Optional) Install global convenience script for PowerShell**

We created a helper script to make `python3` available in PowerShell and to set PATH if needed.

File: `scripts/setup_python_windows.ps1`

To run it (once per machine):

```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned -Force
.\scripts\setup_python_windows.ps1
# Follow prompts or allow the script to add aliases to your PowerShell profile
```

This step is optional — you can always use `py -3` or the full path to `python`.

**5. Start the ML Flask service**

From the activated virtual environment, run:

```powershell
python ml_model\app.py
# or
py -3 ml_model\app.py
```

The service listens on `http://127.0.0.1:5001` and exposes two endpoints:
- `GET /health` — basic health check
- `POST /predict` — body: `{ "command": "<task>", "data": { ... } }`

Example `POST /predict` (PowerShell):

```powershell
Invoke-RestMethod -Uri http://127.0.0.1:5001/predict -Method POST -ContentType 'application/json' -Body '{"command":"generate_quiz","data":{"career_path":"fullstack","difficulty":"medium","level":10,"count":5}}'
```

When requests hit `/predict` you'll see detailed logs in the ML terminal including explicit lines like:

- `✅ Trained model used for ...` or `TRAINED MODEL REQUEST` depending on the flow. These logs are used as proof that the scikit-learn models were used.

**6. Install Node dependencies and start the server & client**

Install backend and frontend dependencies and run the application:

```powershell
# From repo root
npm install

# Start development servers (this repo uses one command for frontend/backend dev)
npm run dev
```

Notes:
- Restart the Node server after modifying server-side TypeScript files (e.g., `server/ml-client.ts`).
- The Node backend expects the ML service at `http://127.0.0.1:5001` by default. You can override with `ML_SERVICE_URL` environment variable.

**7. Files changed / important project items**

- `ml_model/app.py` — Flask ML service (run this first).
- `ml_model/prediction_service.py` — ML model loaders and handlers (loads pickles from `ml_model/saved_models`).
- `ml_model/saved_models/` — trained scikit-learn pickles used by the service.
- `scripts/setup_python_windows.ps1` — convenience PowerShell helper for `python3` alias.
- `server/ml-client.ts` — HTTP client used by the Node backend to call the ML service (this must exist and point to `http://127.0.0.1:5001/predict`).

**8. Verify integration (end-to-end checks)**

1. Start ML service (`python ml_model\app.py`) — confirm `GET /health` returns 200:

```powershell
Invoke-RestMethod -Uri http://127.0.0.1:5001/health
```

2. Start Node server (`npm run dev`) and log into the app from the browser.
3. Trigger the following flows and watch the ML terminal for logs:
  - AI Practice Quiz: generate a practice quiz in the UI and verify ML terminal shows `TRAINED MODEL REQUEST` / `✅ Trained model used ...` when the trained model is used.
  - Career Assessment (Interest Questionnaire): submit responses and confirm ML terminal printed the `TRAINED MODEL_REQUEST` summary and `TRAINED MODEL USED FOR CAREER ASSESSMENT` when applicable.
  - Level 20 milestone: reach level 20 (or simulate user data) and confirm ML terminal logs for Level 20 milestone analysis.
  - Daily Task completion: complete a daily quiz/code challenge and confirm `TRAINED MODEL USED FOR DAILY TASK ANALYSIS` logs.

**9. Running tests (optional)**

There is a `test_ml_service.py` intended to validate ML logic. It may call Python modules directly — prefer updating it to exercise the HTTP API if you want end-to-end verification. Example quick HTTP check (PowerShell):

```powershell
Invoke-RestMethod -Uri http://127.0.0.1:5001/predict -Method POST -ContentType 'application/json' -Body '{"command":"health_check"}'
```

**10. Troubleshooting**

- If you see pickle / import errors about `numpy._core`, reinstall `numpy` and `scikit-learn` matching the versions used when the models were serialized (see step 3).
- If the Node backend doesn't show ML logs when triggering UI flows, ensure `server/ml-client.ts` exists and the Node process has been restarted so it picks up the new client implementation.
- If the ML service times out from the Node backend, increase the timeout in `server/ml-client.ts` call or ensure Flask is running and not blocked.

**11. Environment variables**

You may set the following env vars (optional):

- `ML_SERVICE_URL` — override default ML service base URL (default `http://127.0.0.1:5001`).
- `HUGGINGFACE_API_KEY` — if you want to enable HF generation in AI Quiz flows.

Set env vars in PowerShell for the session:

```powershell
$env:ML_SERVICE_URL = "http://127.0.0.1:5001"
$env:HUGGINGFACE_API_KEY = "your_key_here"
```

--

If you'd like, I can also:
- produce a one-line install script for Windows that automates steps 2–4,
- update `test_ml_service.py` to hit the HTTP endpoints for automated verification,
- or create a short checklist you can tick while setting up the other laptop.

Tell me which of those you'd like me to deliver next.
 
---

**Appendix: PowerShell helper script**

Below is the exact `scripts/setup_python_windows.ps1` helper used in this project. You can copy it into the same path on the new laptop or run it from the repo root with PowerShell.

File: `scripts/setup_python_windows.ps1`

```powershell
# Adds persistent `python` and `python3` aliases that call `py -3`, and adds Python install folders to User PATH.
# Run this from the project root in PowerShell:
#   powershell -ExecutionPolicy Bypass -File .\scripts\setup_python_windows.ps1

try {
  $pythonExe = & py -3 -c "import sys; print(sys.executable)" 2>$null
} catch {
  $pythonExe = $null
}

if (-not $pythonExe -or $pythonExe -eq "") {
  Write-Error "Could not locate Python via 'py -3'. Ensure Python is installed and the 'py' launcher is available."
  exit 1
}

$pythonDir = Split-Path $pythonExe
Write-Output "Detected python.exe at: $pythonExe"
Write-Output "Python install folder: $pythonDir"

# Ensure profile exists
if (-not (Test-Path $PROFILE)) {
  New-Item -ItemType File -Path $PROFILE -Force | Out-Null
  Write-Output "Created PowerShell profile: $PROFILE"
}

$profileText = Get-Content $PROFILE -Raw
$funcPython = "function python { py -3 @args }"
$funcPython3 = "function python3 { py -3 @args }"

if ($profileText -notmatch [regex]::Escape($funcPython)) {
  Add-Content -Path $PROFILE -Value "`n# Alias to call py -3 as python`n$funcPython`n"
  Write-Output "Added 'python' alias to profile"
} else {
  Write-Output "'python' alias already present in profile"
}

if ($profileText -notmatch [regex]::Escape($funcPython3)) {
  Add-Content -Path $PROFILE -Value "`n# Alias to call py -3 as python3`n$funcPython3`n"
  Write-Output "Added 'python3' alias to profile"
} else {
  Write-Output "'python3' alias already present in profile"
}

# Add install folders to User PATH if missing
$userPath = [Environment]::GetEnvironmentVariable("Path","User")
$pathsToAdd = @($pythonDir, (Join-Path $pythonDir 'Scripts'))
$changed = $false
foreach ($p in $pathsToAdd) {
  if (-not ($userPath -and ($userPath -split ';' | ForEach-Object { $_.Trim() } | Where-Object { $_ -ne '' } | Where-Object { $_ -ieq $p }))) {
    $userPath = ($userPath + ";" + $p).TrimStart(';')
    Write-Output "Queued adding to User PATH: $p"
    $changed = $true
  } else {
    Write-Output "Already in User PATH: $p"
  }
}

if ($changed) {
  [Environment]::SetEnvironmentVariable("Path", $userPath, "User")
  Write-Output "Updated User PATH. Restart PowerShell to apply changes."
} else {
  Write-Output "No changes required to User PATH."
}

Write-Output "Done. Restart PowerShell (or start a new session) to use the aliases and PATH changes."
```

**Quick automated installer (one-line)**

If you prefer a single command that runs the helper in the repo root, open PowerShell (Admin if needed) and run:

```powershell
powershell -ExecutionPolicy Bypass -Command "Invoke-Expression ((Get-Content .\scripts\setup_python_windows.ps1 -Raw))"
```

This evaluates the script in your session — or run the file directly:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\setup_python_windows.ps1
```

--

If you want, I can also add a small wrapper `scripts/quick_setup_windows.ps1` that will create the venv and install `ml_model/requirements.txt` automatically. Would you like that created now?
I added a helper script `scripts/quick_setup_windows.ps1` to automate venv creation and ML dependency installation. Usage:

```powershell
# From repo root - create venv, install ML deps, and run the alias helper
powershell -ExecutionPolicy Bypass -File .\scripts\quick_setup_windows.ps1 -RunAliasHelper

# Add Node deps as well (if needed)
powershell -ExecutionPolicy Bypass -File .\scripts\quick_setup_windows.ps1 -RunAliasHelper -InstallNode
```

The script will:
- Create `.venv` if missing
- Upgrade `pip`, `setuptools`, and `wheel` inside the venv
- Install `ml_model/requirements.txt` using the venv Python
- Optionally run `scripts/setup_python_windows.ps1` to add `python`/`python3` aliases
- Optionally run `npm install` if `-InstallNode` is passed and `npm` is available

After running, activate the venv in your session:

```powershell
.\.venv\Scripts\Activate.ps1
```

Then start the ML service:

```powershell
python ml_model\app.py
```
