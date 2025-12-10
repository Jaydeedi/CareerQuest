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