# deploy.ps1 - Compila y despliega Maraña Crochet a XAMPP (htdocs/marana)
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host " Desplegando Maraña Crochet a XAMPP..." -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

$Destination = "C:\xampp\htdocs\marana"

# 1. Compilar el proyecto Vite
Write-Host "[1/4] Compilando frontend Vite..." -ForegroundColor Yellow
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host "Error en la compilación. Abortando despliegue." -ForegroundColor Red
    exit 1
}

# 2. Asegurar que exista la carpeta en htdocs
Write-Host "[2/4] Creando carpetas en $Destination..." -ForegroundColor Yellow
if (-not (Test-Path $Destination)) {
    New-Item -ItemType Directory -Path $Destination -Force | Out-Null
}
if (-not (Test-Path "$Destination\api")) {
    New-Item -ItemType Directory -Path "$Destination\api" -Force | Out-Null
}

# 3. Copiar dist (HTML, CSS, JS) a htdocs/marana
Write-Host "[3/4] Copiando archivos de la aplicación..." -ForegroundColor Yellow
Copy-Item -Path "dist\*" -Destination $Destination -Recurse -Force

# 4. Copiar endpoints de API y configuración a htdocs/marana/api
Write-Host "[4/4] Copiando backend PHP API..." -ForegroundColor Yellow
Copy-Item -Path "api\*" -Destination "$Destination\api" -Recurse -Force

# 5. Crear .htaccess para soporte de rutas SPA y APIs
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
Write-Host "   Frontend: http://localhost/marana" -ForegroundColor White
Write-Host "   API:      http://localhost/marana/api/products.php" -ForegroundColor White
Write-Host "   phpMyAdmin: http://localhost/phpmyadmin (BD: marana_crochet)" -ForegroundColor White
Write-Host "==========================================" -ForegroundColor Green
