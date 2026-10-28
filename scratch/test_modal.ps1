$res = Invoke-WebRequest -Uri "http://localhost/marana/index.php" -UseBasicParsing
Write-Host "Index Status: $($res.StatusCode)"
Write-Host "Contains auth modal overlay: $($res.Content.Contains('marana-auth-modal-overlay'))"
Write-Host "Contains role tabs: $($res.Content.Contains('role-tab-user') -and $res.Content.Contains('role-tab-admin'))"
Write-Host "Contains guest box: $($res.Content.Contains('auth-guest-box'))"

$resAuth = Invoke-WebRequest -Uri "http://localhost/marana/auth.php?reason=buy_required" -UseBasicParsing
Write-Host "Auth Status: $($resAuth.StatusCode)"
Write-Host "Contains buy_required banner: $($resAuth.Content.Contains('Paso Obligatorio para Completar tu Compra'))"
