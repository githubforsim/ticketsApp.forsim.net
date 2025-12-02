#!/usr/bin/env pwsh
# Script simplifie pour lancer les tests PHPUnit

$containerName = "ticketsappforsimnet-web-1"

# Configurer l encodage de la console
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

Write-Host "`n========================================" -ForegroundColor Cyan
Write-Host "   LANCEMENT DES TESTS UNITAIRES" -ForegroundColor Cyan
Write-Host "========================================`n" -ForegroundColor Cyan

Write-Host "Verification du container..." -ForegroundColor Yellow
$containerStatus = docker inspect -f '{{.State.Running}}' $containerName 2>$null
if ($containerStatus -ne "true") {
    Write-Host "`nERREUR: Container non demarre" -ForegroundColor Red
    Write-Host "Lancez: docker-compose up -d`n" -ForegroundColor Yellow
    exit 1
}
Write-Host "OK - Container actif`n" -ForegroundColor Green

Write-Host "Execution de PHPUnit...`n" -ForegroundColor Yellow

# Executer et capturer la sortie
$rawOutput = docker exec $containerName vendor/bin/phpunit --testdox 2>&1
$exitCode = $LASTEXITCODE

# Traiter ligne par ligne pour ajouter des couleurs
foreach ($line in $rawOutput) {
    $lineStr = $line.ToString()
    
    # ORDRE CRITIQUE: Verifier les symboles AVANT tout le reste
    
    # Filtrer les erreurs SQL internes
    if ($lineStr -match 'Erreur de requête|SQLSTATE') {
        continue
    }
    # 1. Tests avec warnings (⚠)
    elseif ($lineStr -match '⚠') {
        Write-Host $lineStr -ForegroundColor Yellow
    }
    # 2. Tests skipped (↩)
    elseif ($lineStr -match '↩') {
        Write-Host $lineStr -ForegroundColor DarkYellow
    }
    # 3. Tests reussis (✔)
    elseif ($lineStr -match '✔') {
        Write-Host $lineStr -ForegroundColor Green
    }
    # 4. Tests echoues (✗)
    elseif ($lineStr -match '✗') {
        Write-Host $lineStr -ForegroundColor Red
    }
    # 5. Titres des classes
    elseif ($lineStr -eq 'Admin Model' -or $lineStr -eq 'Database' -or $lineStr -eq 'Ticket Model' -or $lineStr -eq 'User Model') {
        Write-Host "`n$lineStr" -ForegroundColor Cyan -BackgroundColor Black
    }
    # 6. Header PHPUnit
    elseif ($lineStr -match 'PHPUnit.*by Sebastian Bergmann') {
        Write-Host $lineStr -ForegroundColor Magenta
    }
    # 7. Runtime et Configuration
    elseif ($lineStr -match '^(Runtime|Configuration):') {
        Write-Host $lineStr -ForegroundColor DarkGray
    }
    # 8. Barre de progression
    elseif ($lineStr -match '\.+.*\d+ / \d+') {
        Write-Host $lineStr -ForegroundColor Gray
    }
    # 9. Ligne de statistiques
    elseif ($lineStr -match '^Tests:') {
        Write-Host $lineStr -ForegroundColor Cyan
    }
    # 10. Temps d execution
    elseif ($lineStr -match '^Time:') {
        Write-Host "`n$lineStr" -ForegroundColor Gray
    }
    # 11. Statuts finaux
    elseif ($lineStr -match 'OK, but there were issues!') {
        Write-Host $lineStr -ForegroundColor Green
    }
    elseif ($lineStr -match 'FAILURES!') {
        Write-Host $lineStr -ForegroundColor Red
    }
    # 12. Messages d erreur REELS (pas dans les noms de tests)
    elseif ($lineStr -match '^Erreur' -or $lineStr -match '^Error' -or $lineStr -match 'Exception:') {
        Write-Host $lineStr -ForegroundColor Red
    }
    # 13. Reste en blanc
    else {
        Write-Host $lineStr
    }
}

Write-Host "`n========================================" -ForegroundColor Cyan

# Analyser la sortie pour determiner le vrai statut
$outputString = $rawOutput -join "`n"
if ($outputString -match "OK, but there were issues!") {
    Write-Host "   TESTS OK (avec avertissements)" -ForegroundColor Green
    $exitCode = 0
} elseif ($outputString -match "OK \(") {
    Write-Host "   TOUS LES TESTS REUSSIS" -ForegroundColor Green
    $exitCode = 0
} elseif ($exitCode -eq 0) {
    Write-Host "   TESTS TERMINES AVEC SUCCES" -ForegroundColor Green
} else {
    Write-Host "   TESTS TERMINES AVEC ERREURS" -ForegroundColor Red
}

Write-Host "========================================`n" -ForegroundColor Cyan

exit $exitCode
