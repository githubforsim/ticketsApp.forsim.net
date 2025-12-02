# 🧪 Tests Unitaires - TicketsApp

## 📋 Vue d'ensemble

Cette suite de tests unitaires garantit le bon fonctionnement des composants critiques de l'application TicketsApp. Les tests couvrent les modèles (Models), les fonctions de base de données, et les opérations CRUD essentielles.

## 🎯 Couverture des tests

### Tests implémentés

| Composant | Fichier de test | Tests |
|-----------|----------------|-------|
| **UserModel** | `tests/Models/UserModelTest.php` | 5 tests |
| **TicketModel** | `tests/Models/TicketModelTest.php` | 13 tests |
| **AdminModel** | `tests/Models/AdminModelTest.php` | 11 tests |
| **Database Functions** | `tests/Config/DatabaseTest.php` | 9 tests |

**Total : 38 tests unitaires**

### Fonctionnalités testées

#### UserModel
- ✅ Récupération utilisateur par username
- ✅ Vérification existence username
- ✅ Récupération du rôle utilisateur
- ✅ Vérification password hashé (bcrypt)
- ✅ Gestion utilisateurs inexistants

#### TicketModel
- ✅ Création de ticket
- ✅ Récupération tickets ouverts
- ✅ Récupération tickets résolus
- ✅ Récupération tickets fermés
- ✅ Changement de statut (Open/Solve/Close)
- ✅ Envoi de messages
- ✅ Mise à jour titre/description
- ✅ Log des événements
- ✅ Récupération urgences/produits/types

#### AdminModel
- ✅ Création d'utilisateur
- ✅ Vérification existence username
- ✅ Récupération de tous les utilisateurs
- ✅ Récupération de tous les tickets (Open/Solved/Closed)
- ✅ Mise à jour mot de passe utilisateur
- ✅ Association utilisateur ↔ produits
- ✅ Envoi de messages en tant qu'admin
- ✅ Récupération détails ticket

#### Database Functions
- ✅ Connexion PDO
- ✅ Configuration error mode (exceptions)
- ✅ Exécution de requêtes
- ✅ Gestion erreurs SQL
- ✅ Nettoyage données de test
- ✅ Vérification constantes DB

## 🚀 Installation

### Prérequis
- Docker et Docker Compose
- Conteneur web en cours d'exécution
- PHP 8.3+ dans le conteneur
- Accès à la base de données MySQL

### Installation de PHPUnit

Les scripts `run-tests.ps1` (Windows) et `run-tests.sh` (Linux/Mac) installent automatiquement Composer et PHPUnit lors de la première exécution.

Installation manuelle (si nécessaire) :
```bash
# Dans le conteneur Docker
docker exec -it <nom_conteneur_web> bash
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
cd /var/www/html
composer install
```

## ▶️ Exécution des tests

### Windows (PowerShell)

```powershell
# Exécuter tous les tests
.\run-tests.ps1

# Exécuter un test spécifique
.\run-tests.ps1 -Filter UserModelTest

# Générer le rapport de couverture de code
.\run-tests.ps1 -Coverage
```

### Linux/Mac (Bash)

```bash
# Rendre le script exécutable
chmod +x run-tests.sh

# Exécuter tous les tests
./run-tests.sh

# Exécuter un test spécifique
./run-tests.sh --filter UserModelTest

# Générer le rapport de couverture de code
./run-tests.sh --coverage
```

### Manuellement dans le conteneur

```bash
# Entrer dans le conteneur
docker exec -it <nom_conteneur_web> bash

# Exécuter tous les tests
./vendor/bin/phpunit

# Exécuter un fichier de test spécifique
./vendor/bin/phpunit tests/Models/UserModelTest.php

# Exécuter une méthode de test spécifique
./vendor/bin/phpunit --filter testGetUserByUsername

# Avec couverture de code
./vendor/bin/phpunit --coverage-text
```

## 📊 Résultats attendus

### Sortie typique de tests réussis

```
PHPUnit 10.5.x by Sebastian Bergmann and contributors.

Runtime:       PHP 8.3.x
Configuration: /var/www/html/phpunit.xml

.....................................                       38 / 38 (100%)

Time: 00:02.145, Memory: 10.00 MB

OK (38 tests, 125 assertions)
```

### Interprétation
- **Point (.)** : Test réussi
- **F** : Test échoué (Failure)
- **E** : Erreur durant le test (Error)
- **S** : Test ignoré (Skipped)

## 🔧 Configuration

### phpunit.xml
Le fichier `phpunit.xml` configure :
- Bootstrap : `tests/bootstrap.php`
- Répertoire des tests : `tests/`
- Couverture de code : `www/app/src/` (excluant Views)
- Variables d'environnement pour la DB de test

### tests/bootstrap.php
Initialise l'environnement de test :
- Chargement de l'autoloader Composer
- Définition des constantes
- Connexion à la base de données de test
- Fonctions utilitaires (cleanup, etc.)

## 🗃️ Base de données de test

### Base de données dédiée créée

Les tests utilisent maintenant une base de données dédiée `ticketsApp_test` avec :
- **12 tables** identiques à la production
- **Données initiales** du dump SQL
- **Isolation complète** de la base de production

### Création/réinitialisation de la base de test

#### Windows (PowerShell)
```powershell
# Créer la base de test
.\create-test-db.ps1

# Réinitialiser la base de test
.\create-test-db.ps1 -Reset
```

#### Linux/Mac (Bash)
```bash
# Rendre le script exécutable
chmod +x create-test-db.sh

# Créer la base de test
./create-test-db.sh

# Réinitialiser la base de test
./create-test-db.sh --reset
```

### Configuration automatique

La base de test est automatiquement utilisée via :
- `phpunit.xml` : Variable d'environnement `DB_NAME=ticketsApp_test`
- `tests/bootstrap.php` : Fonction `getTestDbConnection()` avec fallback

### Avantages

✅ **Isolation** : Les tests ne modifient pas les données de production  
✅ **Reproductibilité** : État initial cohérent pour chaque exécution  
✅ **Sécurité** : Pas de risque de perte de données  
✅ **Performance** : Base légère optimisée pour les tests

---

## 🗃️ Base de données de test (ancien - avant création DB dédiée)

<details>
<summary>Cliquez pour voir l'ancienne configuration</summary>

Les tests utilisent la même base de données que l'application (par défaut) mais :
- Nettoient automatiquement après chaque test (setUp/tearDown)
- Utilisent des transactions quand possible

### Création d'une base de test dédiée (déjà fait ✅)

La base de données de test `ticketsApp_test` a été créée et initialisée avec le dump SQL.

</details>

---

## 📝 Écrire de nouveaux tests

### Structure d'un test

```php
<?php

use PHPUnit\Framework\TestCase;

class MyNewTest extends TestCase
{
    private PDO $db;
    
    protected function setUp(): void
    {
        // Initialisation avant chaque test
        $this->db = getTestDbConnection();
    }
    
    protected function tearDown(): void
    {
        // Nettoyage après chaque test
        cleanupTestData($this->db, 'table_name', 'condition');
    }
    
    public function testMyFeature(): void
    {
        // Arrange (Préparation)
        $expected = 'expected_value';
        
        // Act (Action)
        $result = myFunction();
        
        // Assert (Vérification)
        $this->assertEquals($expected, $result);
    }
}
```

### Bonnes pratiques
1. **Nom explicite** : `testNomMethode` ou `testComportementAttendu`
2. **Isolation** : Chaque test doit être indépendant
3. **Nettoyage** : Toujours nettoyer les données de test
4. **Assertions claires** : Utiliser des messages explicites
5. **Arrange-Act-Assert** : Structure en 3 phases

## 🐛 Débogage

### Afficher les erreurs détaillées

```bash
./vendor/bin/phpunit --debug
./vendor/bin/phpunit --verbose
```

### Tester un seul cas

```bash
./vendor/bin/phpunit --filter testGetUserByUsername tests/Models/UserModelTest.php
```

### Vérifier la configuration

```bash
./vendor/bin/phpunit --version
./vendor/bin/phpunit --list-tests
```

## 📈 Rapport de couverture

Le rapport de couverture HTML est généré dans `./coverage/index.html`

```powershell
# Windows
.\run-tests.ps1 -Coverage
start coverage\index.html

# Linux/Mac
./run-tests.sh --coverage
xdg-open coverage/index.html
```

## ⚠️ Limitations connues

1. **Base de données partagée** : Les tests utilisent la DB principale par défaut
2. **Dépendances** : Certains tests nécessitent des données existantes (produits, statuts)
3. **Isolation** : Les tests modifient temporairement la base de données
4. **Performance** : Les tests DB peuvent être lents (2-5 secondes)

## 🔄 Intégration Continue (CI/CD)

Pour intégrer dans un pipeline CI/CD :

```yaml
# Exemple GitHub Actions
- name: Run Tests
  run: |
    docker-compose up -d
    docker exec ticketsapp-web-1 composer install
    docker exec ticketsapp-web-1 ./vendor/bin/phpunit
```

## 📚 Ressources

- [Documentation PHPUnit](https://phpunit.de/documentation.html)
- [Best Practices PHPUnit](https://phpunit.de/manual/current/en/writing-tests-for-phpunit.html)
- [Assertions disponibles](https://phpunit.de/manual/current/en/appendixes.assertions.html)

## 🆘 Support

En cas de problème :
1. Vérifier que Docker est démarré
2. Vérifier que les conteneurs sont en cours d'exécution : `docker-compose ps`
3. Vérifier les logs : `docker-compose logs web`
4. Réinstaller les dépendances : `docker exec <container> composer install`

---

**Dernière mise à jour** : Décembre 2024  
**Version PHPUnit** : 10.5+  
**PHP Requis** : 8.3+
