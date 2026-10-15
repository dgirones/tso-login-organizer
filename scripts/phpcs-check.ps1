# TSO Login Organizer - PHP lint + prefix + $_POST safety + PHPCS
param(
    [string[]]$Paths = @(),
    [switch]$PhpLintOnly
)

$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$Prefix = 'tsolo_'
$Fail = 0

function Write-Step($msg) { Write-Host "==> $msg" -ForegroundColor Cyan }
function Write-Ok($msg) { Write-Host "OK: $msg" -ForegroundColor Green }
function Write-Fail($msg) { Write-Host "FAIL: $msg" -ForegroundColor Red; $script:Fail = 1 }

if ($Paths.Count -gt 0) {
    $PhpFiles = @()
    foreach ($p in $Paths) {
        $full = Join-Path $Root $p
        if (-not (Test-Path $full)) { Write-Fail "Path not found: $p"; continue }
        if ((Get-Item $full).PSIsContainer) {
            $PhpFiles += Get-ChildItem $full -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch '[\\/](vendor|tests)[\\/]' }
        } else { $PhpFiles += Get-Item $full }
    }
} else {
    $PhpFiles = Get-ChildItem $Root -Recurse -Filter *.php | Where-Object { $_.FullName -notmatch '[\\/](vendor|tests)[\\/]' }
}

if ($PhpFiles.Count -eq 0) { Write-Fail 'No PHP files to check.'; exit 1 }

Write-Step 'PHP syntax (php -l)'
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    Write-Host 'WARN: php not found in PATH - skip syntax check' -ForegroundColor Yellow
} else {
    foreach ($f in $PhpFiles) {
        $out = & php -l $f.FullName 2>&1
        if ($LASTEXITCODE -ne 0) { Write-Fail "$($f.FullName.Substring($Root.Length + 1)): $out" }
    }
    if ($Fail -eq 0) { Write-Ok 'php -l' }
}

Write-Step "Duplicate function definitions ($Prefix*)"
$funcHits = @{}
foreach ($f in $PhpFiles) {
    $lines = Get-Content $f.FullName
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -match "^\s*function\s+($([regex]::Escape($Prefix))\w+)\s*\(") {
            $name = $Matches[1]
            $rel = $f.FullName.Substring($Root.Length + 1)
            if (-not $funcHits.ContainsKey($name)) { $funcHits[$name] = @() }
            $funcHits[$name] += "${rel}:$($i + 1)"
        }
    }
}
$dupes = @($funcHits.GetEnumerator() | Where-Object { $_.Value.Count -gt 1 })
foreach ($d in $dupes) { Write-Fail "Duplicate function $($d.Key): $($d.Value -join ', ')" }
if ($dupes.Count -eq 0) { Write-Ok 'no duplicate plugin functions' }

function Test-TsoloLineHasPhpcsIgnore($lines, $index) {
    if ($index -ge 0 -and $index -lt $lines.Count) {
        if ($lines[$index] -match 'phpcs:ignore|phpcs:disable') { return $true }
    }
    if ($index -gt 0) {
        if ($lines[$index - 1] -match 'phpcs:ignore|phpcs:disable') { return $true }
    }
    return $false
}

Write-Step 'Unsafe $_POST reads (no phpcs:ignore, outside storage)'
$AllowedPostFiles = @('tsolo-storage.php')
foreach ($f in $PhpFiles) {
    $leaf = Split-Path $f.Name -Leaf
    if ($AllowedPostFiles -contains $leaf) { continue }
    $lines = Get-Content $f.FullName
    for ($i = 0; $i -lt $lines.Count; $i++) {
        if ($lines[$i] -notmatch '\$_POST\s*\[') { continue }
        $trim = $lines[$i].Trim()
        if ($trim -match '^\*|^//|^#|@param') { continue }
        if (Test-TsoloLineHasPhpcsIgnore $lines $i) { continue }
        $rel = $f.FullName.Substring($Root.Length + 1)
        Write-Fail "${rel}:$($i + 1): add phpcs:ignore after nonce verify or use tsolo storage helpers - $trim"
    }
}
if ($Fail -eq 0) { Write-Ok 'no unannotated $_POST outside storage' }

Write-Step 'Prefix audit'
$prefixScript = Join-Path $Root 'scripts\prefix-audit.ps1'
if (Test-Path $prefixScript) {
    & $prefixScript
    if ($LASTEXITCODE -ne 0) { $Fail = 1 }
} else {
    Write-Host 'WARN: prefix-audit.ps1 not found - skipped' -ForegroundColor Yellow
}

if ($PhpLintOnly) {
    if ($Fail -eq 0) { Write-Ok 'phpcs-check (PhpLintOnly) complete' }
    exit $Fail
}

Write-Step 'PHPCS (WordPress standard, if installed)'
$phpcsCmd = Get-Command phpcs -ErrorAction SilentlyContinue
if (-not $phpcsCmd) {
    Write-Host 'WARN: phpcs not in PATH' -ForegroundColor Yellow
} else {
    $targets = @(
        (Join-Path $Root 'tso-login-organizer.php'),
        (Join-Path $Root 'uninstall.php'),
        (Join-Path $Root 'includes')
    )
    $existing = @($targets | Where-Object { Test-Path $_ })
    if ($existing.Count -gt 0) {
        & phpcs --standard=WordPress --extensions=php --report=summary @existing
        if ($LASTEXITCODE -ne 0) { $Fail = 1 } else { Write-Ok 'PHPCS summary' }
    }
}

if ($Fail -eq 0) {
    Write-Host ''
    Write-Ok 'All phpcs-check gates passed.'
    exit 0
}
Write-Host ''
Write-Fail 'phpcs-check failed - fix issues above before ZIP.'
exit 1
