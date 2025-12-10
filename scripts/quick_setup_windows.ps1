<#
Quick setup wrapper for Windows (PowerShell)

Usage (from repo root):
  powershell -ExecutionPolicy Bypass -File .\scripts\quick_setup_windows.ps1 [-RunAliasHelper] [-InstallNode]

Options:
  -RunAliasHelper   : Run `scripts/setup_python_windows.ps1` after creating venv (adds python/python3 aliases)
  -InstallNode      : Run `npm install` in the repo root if `npm` is available

This script creates a `.venv`, installs Python requirements for the ML service, and optionally runs the alias helper.
#>

param(
    [switch]$RunAliasHelper,
    [switch]$InstallNode
)

function Write-ErrAndExit($msg) {
    Write-Error $msg
    exit 1
}

Write-Output "Quick setup: creating virtual environment and installing ML dependencies..."

# Locate Python (prefer py -3)
$pythonExe = $null
try { $pythonExe = (& py -3 -c "import sys; print(sys.executable)") 2>$null } catch {}
if (-not $pythonExe) {
    try { $pythonExe = (& python -c "import sys; print(sys.executable)") 2>$null } catch {}
}

if (-not $pythonExe) {
    Write-ErrAndExit "Python not found. Install Python 3.10+ and ensure 'py' or 'python' is on PATH."
}

Write-Output "Using Python: $pythonExe"

# Create venv if missing
if (-not (Test-Path -Path .venv)) {
    Write-Output "Creating virtual environment at .\.venv..."
    & py -3 -m venv .venv || Write-ErrAndExit "Failed to create virtual environment"
} else {
    Write-Output ".venv already exists — skipping creation"
}

$venvPython = Join-Path (Resolve-Path .venv).Path 'Scripts\python.exe'
if (-not (Test-Path $venvPython)) {
    Write-ErrAndExit "Virtual environment python not found at $venvPython"
}

Write-Output "Upgrading pip in venv..."
& $venvPython -m pip install --upgrade pip setuptools wheel | Write-Output

Write-Output "Installing ML requirements from ml_model/requirements.txt..."
if (-not (Test-Path 'ml_model\requirements.txt')) {
    Write-ErrAndExit "ml_model/requirements.txt not found"
}

& $venvPython -m pip install -r ml_model\requirements.txt
if ($LASTEXITCODE -ne 0) {
    Write-Warning "pip install returned non-zero exit code. You can re-run: & $venvPython -m pip install -r ml_model\requirements.txt"
}

if ($RunAliasHelper) {
    if (Test-Path '.\scripts\setup_python_windows.ps1') {
        Write-Output "Running alias helper (setup_python_windows.ps1)..."
        powershell -ExecutionPolicy Bypass -File .\scripts\setup_python_windows.ps1
    } else {
        Write-Warning "Alias helper not found at .\scripts\setup_python_windows.ps1"
    }
}

if ($InstallNode) {
    if (Get-Command npm -ErrorAction SilentlyContinue) {
        Write-Output "Running npm install in repo root..."
        npm install
    } else {
        Write-Warning "npm not found — skipping Node dependency install"
    }
}

Write-Output "Quick setup complete. To activate the venv in this session run:"
Write-Output "  .\.venv\Scripts\Activate.ps1"
Write-Output "Start the ML service: python ml_model\app.py (from activated venv)"

exit 0
