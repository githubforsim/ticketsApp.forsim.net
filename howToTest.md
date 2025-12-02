# Comment tester l'application TicketsApp

## 🧪 Commande simple pour lancer les tests

```powershell
Get-Content test.ps1 | Invoke-Expression
```

**Ou copiez-collez directement ce code dans PowerShell :**
```powershell
$containerName = "ticketsappforsimnet-web-1"; [Console]::OutputEncoding = [System.Text.Encoding]::UTF8; Write-Host "`n========================================" -ForegroundColor Cyan; Write-Host "   LANCEMENT DES TESTS UNITAIRES" -ForegroundColor Cyan; Write-Host "========================================`n" -ForegroundColor Cyan; Write-Host "Verification du container..." -ForegroundColor Yellow; $containerStatus = docker inspect -f '{{.State.Running}}' $containerName 2>$null; if ($containerStatus -ne "true") { Write-Host "`nERREUR: Container non demarre" -ForegroundColor Red; Write-Host "Lancez: docker-compose up -d`n" -ForegroundColor Yellow; exit 1 }; Write-Host "OK - Container actif`n" -ForegroundColor Green; Write-Host "Execution de PHPUnit...`n" -ForegroundColor Yellow; $rawOutput = docker exec $containerName vendor/bin/phpunit --testdox 2>&1; $exitCode = $LASTEXITCODE; foreach ($line in $rawOutput) { $lineStr = $line.ToString(); if ($lineStr -match 'Erreur de requête|SQLSTATE') { continue } elseif ($lineStr -match '✔') { Write-Host $lineStr -ForegroundColor Green } elseif ($lineStr -match '↩') { Write-Host $lineStr -ForegroundColor DarkYellow } elseif ($lineStr -match '⚠') { Write-Host $lineStr -ForegroundColor Yellow } elseif ($lineStr -match '✗') { Write-Host $lineStr -ForegroundColor Red } elseif ($lineStr -eq 'Admin Model' -or $lineStr -eq 'Database' -or $lineStr -eq 'Ticket Model' -or $lineStr -eq 'User Model') { Write-Host "`n$lineStr" -ForegroundColor Cyan -BackgroundColor Black } elseif ($lineStr -match 'PHPUnit.*by Sebastian Bergmann') { Write-Host $lineStr -ForegroundColor Magenta } elseif ($lineStr -match '^(Runtime|Configuration):') { Write-Host $lineStr -ForegroundColor DarkGray } elseif ($lineStr -match '\.+.*\d+ / \d+') { Write-Host $lineStr -ForegroundColor Gray } elseif ($lineStr -match '^Tests:') { Write-Host $lineStr -ForegroundColor Cyan } elseif ($lineStr -match '^Time:') { Write-Host "`n$lineStr" -ForegroundColor Gray } elseif ($lineStr -match 'OK, but there were issues!') { Write-Host $lineStr -ForegroundColor Green } elseif ($lineStr -match 'FAILURES!') { Write-Host $lineStr -ForegroundColor Red } else { Write-Host $lineStr } }; Write-Host "`n========================================" -ForegroundColor Cyan; $outputString = $rawOutput -join "`n"; if ($outputString -match "OK, but there were issues!") { Write-Host "   TESTS OK (avec avertissements)" -ForegroundColor Green } elseif ($outputString -match "OK \(") { Write-Host "   TOUS LES TESTS REUSSIS" -ForegroundColor Green } elseif ($exitCode -eq 0) { Write-Host "   TESTS TERMINES AVEC SUCCES" -ForegroundColor Green } else { Write-Host "   TESTS TERMINES AVEC ERREURS" -ForegroundColor Red }; Write-Host "========================================`n" -ForegroundColor Cyan
```

Cette commande unique :
- ✅ Vérifie que le container Docker est actif
- 🧪 Lance tous les tests unitaires PHPUnit (36 tests)
- 📊 Affiche les résultats détaillés avec couleurs :
  - ✔ en **vert** = Test réussi (30 tests)
  - ↩ en **jaune foncé** = Test ignoré (1 test)
  - ⚠ en **jaune** = Avertissement (5 tests)
  - Titres en **cyan**, erreurs en **rouge**
- 🎯 Indique le statut final (succès/avertissements/erreurs)

> **Note:** Les messages d'erreur de tests internes (comme `nonexistent_table_xyz`) sont masqués pour un affichage plus propre. Ces erreurs font partie des tests de gestion d'erreur.

---

## 📋 Résultat attendu

```
🔍 Vérification du container...
✅ Container actif

🧪 Exécution des tests...
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

[Détails des tests...]

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 RÉSUMÉ
   Tests      : 36
   ✅ Réussis : 30
   ⏭️  Skipped : 6
   ❌ Échecs  : 0
   ⚠️  Erreurs : 0
   ⚡ Risky   : 0
   📝 Assertions: 93

🎯 STATUT: OK (avec avertissements)
```

---

## 🔧 Commandes avancées

### Exécuter un test spécifique
```powershell
.\run-tests.ps1 -Filter "testGetUserByUsername"
```

### Exécuter avec couverture de code
```powershell
.\run-tests.ps1 -Coverage
```

### Gérer la base de données de test

#### Créer/vérifier la DB test
```powershell
.\create-test-db.ps1
```

#### Réinitialiser la DB test
```powershell
.\create-test-db.ps1 -Reset
```

---

## 📂 Structure des tests

```
tests/
├── bootstrap.php              # Initialisation de l'environnement de test
├── Config/
│   └── DatabaseTest.php      # Tests des fonctions de base de données
└── Models/
    ├── AdminModelTest.php    # Tests du modèle Admin
    ├── TicketModelTest.php   # Tests du modèle Ticket
    └── UserModelTest.php     # Tests du modèle User
```

---

## 🗄️ Base de données de test

- **Base de production** : `ticketsApp`
- **Base de test** : `ticketsApp_test`

Les tests utilisent une base de données séparée pour garantir :
- ✅ Isolation complète des données de production
- ✅ Tests reproductibles
- ✅ Possibilité de reset à tout moment

---

## ⚙️ Prérequis

1. **Docker Desktop** doit être lancé
2. **Containers actifs** :
   ```powershell
   docker-compose up -d
   ```
3. **Base de test créée** (si première exécution) :
   ```powershell
   .\create-test-db.ps1
   ```

---

## 📖 Documentation complète

Pour plus de détails sur les tests :
- Voir **[TESTS.md](TESTS.md)** - Documentation complète des tests
- Voir **[TEST_RESULTS.txt](TEST_RESULTS.txt)** - Derniers résultats d'exécution

---

## 🐛 En cas de problème

### Le container n'est pas démarré
```powershell
docker-compose up -d
```

### La base de test n'existe pas
```powershell
.\create-test-db.ps1
```

### Réinstaller les dépendances PHPUnit
```powershell
docker exec ticketsappforsimnet-web-1 sh -c "cd /ticketsApp && composer install"
```

### Voir les logs du container
```powershell
docker logs ticketsappforsimnet-web-1
```
