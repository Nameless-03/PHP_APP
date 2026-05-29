$root = Join-Path (Get-Location) "vendor\laravel\sail\runtimes"

if (-not (Test-Path $root)) {
    Write-Host "Error: No se encontro $root" -ForegroundColor Red
    exit 1
}

$count = 0
$items = Get-ChildItem -Path $root -Recurse -File -Filter "start-container"

foreach ($file in $items) {
    $filePath = $file.FullName
    $content = [System.IO.File]::ReadAllText($filePath)
    $newContent = $content -replace "`r`n", "`n"

    if ($newContent -ne $content) {
        [System.IO.File]::WriteAllText(
            $filePath,
            $newContent,
            (New-Object System.Text.UTF8Encoding($false))
        )
        Write-Host "[OK] LF fixed: $($file.Name)" -ForegroundColor Green
        $count++
    }
}

if ($count -gt 0) {
    Write-Host "`nSe corrigieron $count archivo(s). Ejecuta:" -ForegroundColor Green
    Write-Host "docker compose up -d --build" -ForegroundColor Cyan
} else {
    Write-Host "Todos los scripts ya estan en LF" -ForegroundColor Green
}


