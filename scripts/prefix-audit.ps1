# TSO Login Organizer - prefix audit
$ErrorActionPreference = 'Stop'
$Root = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
$AllowedFiles = @('tsolo-storage.php', 'tsolo-compat-tml.php')
$Violations = @()

$Patterns = @(
    @{ Label = 'class TSO_ (not TSOLO)'; Regex = 'class TSO_[^L]' },
    @{ Label = 'tsoLo JS global'; Regex = 'tsoLo[A-Z]' },
    @{ Label = 'GLOBALS[tso_]'; Regex = "\`$GLOBALS\['tso_" },
    @{ Label = 'function tso_'; Regex = '\bfunction tso_[a-z]' },
    @{ Label = 'wp_ajax_tso_ (not tsolo)'; Regex = 'wp_ajax_tso_[^l]' },
    @{ Label = 'bare tso_ option API'; Regex = "(get|update|delete)_option\s*\(\s*'tso_[^l]" },
    @{ Label = 'load_plugin_textdomain'; Regex = 'load_plugin_textdomain\s*\(' }
)

Get-ChildItem $Root -Recurse -Include *.php,*.js |
    Where-Object { $_.FullName -notmatch '[\\/](vendor|tests)[\\/]' } |
    ForEach-Object {
        $rel = $_.FullName.Substring($Root.Length + 1)
        $leaf = Split-Path $rel -Leaf
        if ($AllowedFiles -contains $leaf) { return }

        foreach ($p in $Patterns) {
            Select-String -Path $_.FullName -Pattern $p.Regex -CaseSensitive -AllMatches | ForEach-Object {
                $Violations += [pscustomobject]@{
                    Rule = $p.Label
                    File = $rel
                    Line = $_.LineNumber
                    Text = $_.Line.Trim()
                }
            }
        }
    }

if ($Violations.Count -eq 0) {
    Write-Host 'OK: no prefix violations outside allowed files.' -ForegroundColor Green
    exit 0
}

Write-Host "FAIL: $($Violations.Count) violation(s):" -ForegroundColor Red
$Violations | Format-Table -AutoSize
exit 1
