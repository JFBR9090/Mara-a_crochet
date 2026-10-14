# deploy.ps1 - Compila y despliega Maraña Crochet a XAMPP (htdocs/marana)
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Desplegando Maraña Crochet a XAMPP..." -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

$Destination = "C:\xampp\htdocs\marana"

# 1. Compilar el proyecto Vite
Write-Host "[1/5] Compilando frontend Vite..." -ForegroundColor Yellow
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error en la compilación. Abortando despliegue." -ForegroundColor Red
    exit 1
}

# 2. Generar autoloader optimizado de Composer (PHP 8.2+)
Write-Host "[2/5] Optimizando Autoloading PSR-4 de Composer (PHP 8.2+)..." -ForegroundColor Yellow
composer dump-autoload -o

# 3. Asegurar que existan las carpetas en htdocs
Write-Host "[3/5] Creando carpetas en $Destination..." -ForegroundColor Yellow
if (-not (Test-Path $Destination)) {
    New-Item -ItemType Directory -Path $Destination -Force | Out-Null
}
if (-not (Test-Path "$Destination\api")) {
    New-Item -ItemType Directory -Path "$Destination\api" -Force | Out-Null
}
if (-not (Test-Path "$Destination\vendor")) {
    New-Item -ItemType Directory -Path "$Destination\vendor" -Force | Out-Null
}
if (-not (Test-Path "$Destination\php_app")) {
    New-Item -ItemType Directory -Path "$Destination\php_app" -Force | Out-Null
}

# 4. Copiar dist, api, vendor y php_app a htdocs/marana
Write-Host "[4/5] Copiando archivos de la aplicación y backend..." -ForegroundColor Yellow
Copy-Item -Path "dist\*" -Destination $Destination -Recurse -Force
Copy-Item -Path "api\*" -Destination "$Destination\api" -Recurse -Force
Copy-Item -Path "vendor\*" -Destination "$Destination\vendor" -Recurse -Force
Copy-Item -Path "php_app\*" -Destination "$Destination\php_app" -Recurse -Force
Copy-Item -Path "composer.json" -Destination $Destination -Force

# 5. Crear .htaccess para soporte de rutas SPA y APIs
Write-Host "[5/5] Configurando directivas de Apache en .htaccess..." -ForegroundColor Yellow
$HtaccessContent = @"
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /marana/
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /marana/index.html [L]
</IfModule>
"@
Set-Content -Path "$Destination\.htaccess" -Value $HtaccessContent -Encoding UTF8

Write-Host "==========================================" -ForegroundColor Green
Write-Host " ¡Despliegue completado con éxito!" -ForegroundColor Green
Write-Host " Accede en tu navegador a:" -ForegroundColor Green
Write-Host "   Frontend React/Vite:    http://localhost/marana" -ForegroundColor White
Write-Host "   API Diagnóstico PHP:    http://localhost/marana/api/status.php" -ForegroundColor Cyan
Write-Host "   API Productos (JSON):   http://localhost/marana/api/products.php" -ForegroundColor White
Write-Host "   Tienda PHP Nativa:      http://localhost/marana/php_app" -ForegroundColor White
Write-Host "   Panel Taller (PHP 8.2): http://localhost/marana/php_app/admin/status.php" -ForegroundColor Cyan
Write-Host "   phpMyAdmin (MySQL):     http://localhost/phpmyadmin (BD: marana_crochet)" -ForegroundColor White
Write-Host "==========================================" -ForegroundColor Green
