# create-test-db.ps1
# Script pour créer et initialiser la base de données de test

param(
    [switch]$Reset
)

$DB_NAME = "ticketsApp_test"
$DB_USER = "root"
$DB_PASSWORD = "bODw50eeKjPT/rVW"
$CONTAINER = "ticketsappforsimnet-db-1"
$DUMP_FILE = "backups\ticketsApp_2025-11-30_18-33.sql"

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Base de données de test - TicketsApp" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""

# Vérifier si Docker est en cours d'exécution
try {
    docker info | Out-Null
} catch {
    Write-Host "❌ Erreur: Docker n'est pas en cours d'exécution" -ForegroundColor Red
    exit 1
}

# Vérifier si le conteneur DB existe
$containerExists = docker ps -a --format "{{.Names}}" | Select-String $CONTAINER
if (-not $containerExists) {
    Write-Host "❌ Erreur: Le conteneur $CONTAINER n'existe pas" -ForegroundColor Red
    exit 1
}

Write-Host "✓ Conteneur DB trouvé: $CONTAINER" -ForegroundColor Green

# Supprimer la base de données existante si -Reset
if ($Reset) {
    Write-Host ""
    Write-Host "⚠️  Mode RESET activé - Suppression de la base existante..." -ForegroundColor Yellow
    docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME;" 2>$null
    Write-Host "✓ Base de données supprimée" -ForegroundColor Green
}

# Créer la base de données
Write-Host ""
Write-Host "📦 Création de la base de données '$DB_NAME'..." -ForegroundColor Cyan
docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>$null

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Base de données créée avec succès" -ForegroundColor Green
} else {
    Write-Host "❌ Erreur lors de la création de la base de données" -ForegroundColor Red
    exit 1
}

# Importer le dump SQL
Write-Host ""
Write-Host "📥 Import du dump SQL..." -ForegroundColor Cyan

if (-not (Test-Path $DUMP_FILE)) {
    Write-Host "❌ Erreur: Le fichier $DUMP_FILE n'existe pas" -ForegroundColor Red
    exit 1
}

Get-Content $DUMP_FILE | docker exec -i $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME 2>$null

if ($LASTEXITCODE -eq 0) {
    Write-Host "✓ Dump SQL importé avec succès" -ForegroundColor Green
} else {
    Write-Host "❌ Erreur lors de l'import du dump SQL" -ForegroundColor Red
    exit 1
}

# Vérifier les tables
Write-Host ""
Write-Host "🔍 Vérification des tables..." -ForegroundColor Cyan
$tables = docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "USE $DB_NAME; SHOW TABLES;" 2>$null | Select-Object -Skip 1

$tableCount = ($tables | Measure-Object).Count
Write-Host "✓ $tableCount tables trouvées:" -ForegroundColor Green
$tables | ForEach-Object { Write-Host "  - $_" -ForegroundColor White }

# Statistiques
Write-Host ""
Write-Host "📊 Statistiques de la base de test:" -ForegroundColor Cyan

$stats = @{
    "users" = docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "SELECT COUNT(*) FROM $DB_NAME.user;" 2>$null | Select-Object -Skip 1
    "tickets" = docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "SELECT COUNT(*) FROM $DB_NAME.ticket;" 2>$null | Select-Object -Skip 1
    "produits" = docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "SELECT COUNT(*) FROM $DB_NAME.produit;" 2>$null | Select-Object -Skip 1
}

Write-Host "  - Utilisateurs: $($stats.users)" -ForegroundColor White
Write-Host "  - Tickets: $($stats.tickets)" -ForegroundColor White
Write-Host "  - Produits: $($stats.produits)" -ForegroundColor White

Write-Host ""
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "✅ Base de données de test prête!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Vous pouvez maintenant lancer les tests:" -ForegroundColor Yellow
Write-Host "  .\run-tests.ps1" -ForegroundColor White
Write-Host ""

exit 0
