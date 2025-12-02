# run-tests.ps1
# Script PowerShell pour exécuter les tests unitaires dans le conteneur Docker

param(
    [switch]$Coverage,
    [string]$Filter = ""
)

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "TicketsApp - Tests Unitaires" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si Docker est en cours d'exécution
try {
    docker info | Out-Null
} catch {
    Write-Host "❌ Erreur: Docker n'est pas en cours d'exécution" -ForegroundColor Red
    exit 1
}

# Vérifier si le conteneur web existe
$containerName = docker ps --format "{{.Names}}" | Select-String "ticketsapp.*web" | Select-Object -First 1

if (-not $containerName) {
    Write-Host "⚠️  Le conteneur web n'est pas démarré" -ForegroundColor Yellow
    Write-Host "Démarrage du conteneur..." -ForegroundColor Yellow
    docker-compose up -d web
    Start-Sleep -Seconds 3
    $containerName = docker ps --format "{{.Names}}" | Select-String "ticketsapp.*web" | Select-Object -First 1
}

if (-not $containerName) {
    Write-Host "❌ Erreur: Le conteneur web n'a pas pu être démarré" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Conteneur trouvé: $containerName" -ForegroundColor Green
Write-Host ""

# Installer PHPUnit si nécessaire
Write-Host "📦 Vérification de PHPUnit..." -ForegroundColor Cyan
docker exec $containerName bash -c @"
    if [ ! -f /var/www/html/vendor/bin/phpunit ]; then
        echo 'Installation de Composer et PHPUnit...'
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        cd /var/www/html && composer install --no-interaction
    fi
"@

Write-Host ""
Write-Host "🧪 Exécution des tests..." -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

# Exécuter les tests
$exitCode = 0
if ($Coverage) {
    Write-Host "Mode: Couverture de code" -ForegroundColor Yellow
    docker exec $containerName /var/www/html/vendor/bin/phpunit --coverage-text --coverage-html /var/www/html/coverage
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✓ Rapport de couverture généré dans: ./coverage/index.html" -ForegroundColor Green
    }
    $exitCode = $LASTEXITCODE
} elseif ($Filter) {
    Write-Host "Mode: Filtre sur '$Filter'" -ForegroundColor Yellow
    docker exec $containerName /var/www/html/vendor/bin/phpunit --filter $Filter
    $exitCode = $LASTEXITCODE
} else {
    docker exec $containerName /var/www/html/vendor/bin/phpunit
    $exitCode = $LASTEXITCODE
}

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
if ($exitCode -eq 0) {
    Write-Host "✅ Tests réussis!" -ForegroundColor Green
} else {
    Write-Host "❌ Tests échoués (code: $exitCode)" -ForegroundColor Red
}
Write-Host "==========================================" -ForegroundColor Cyan

exit $exitCode
