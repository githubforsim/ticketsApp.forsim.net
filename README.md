# 🎫 TicketsApp - Système de Gestion de Tickets de Support

[![PHP Version](https://img.shields.io/badge/PHP-8.3-blue.svg)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-9.2-orange.svg)](https://www.mysql.com/)
[![Docker](https://img.shields.io/badge/Docker-Ready-brightgreen.svg)](https://www.docker.com/)
[![Tests](https://img.shields.io/badge/Tests-38%20passing-success.svg)](./TESTS.md)

Application web professionnelle de gestion de tickets de support/incidents, développée avec architecture MVC en PHP.

## 📋 Table des matières

- [Fonctionnalités](#-fonctionnalités)
- [Captures d'écran](#-captures-décran)
- [Installation rapide](#-installation-rapide)
- [Documentation](#-documentation)
- [Tests](#-tests)
- [Technologies](#-technologies)
- [Support](#-support)

## ✨ Fonctionnalités

### 🎯 Gestion complète des tickets
- **Création** de tickets avec titre, description, urgence, type et produit
- **Suivi** du statut : Nouveau → En cours → Résolu → Fermé
- **Messagerie intégrée** pour communication utilisateur/admin
- **Pièces jointes** : ajout et suppression de fichiers
- **Historique complet** de tous les événements

### 👥 Gestion des utilisateurs
- **Deux rôles** : Utilisateur standard et Administrateur
- **Authentification sécurisée** avec mots de passe hashés (bcrypt)
- **Multi-produits** : chaque utilisateur accède uniquement à ses produits assignés

### 📊 Tableau de bord
- **Vue d'ensemble** des tickets par statut
- **Filtres** : tickets ouverts, résolus, fermés
- **Recherche** et tri avancés
- **Statistiques** temps réel (admin)

### 🔒 Sécurité
- Protection XSS avec `htmlspecialchars()`
- Content Security Policy (CSP)
- Headers de sécurité (HSTS, X-Frame-Options)
- Sessions PHP sécurisées
- Validation des entrées utilisateur

## 🖼️ Captures d'écran

> *À venir : Screenshots du dashboard, détails ticket, chat, admin panel*

## 🚀 Installation rapide

### Prérequis
- [Docker Desktop](https://www.docker.com/products/docker-desktop) installé
- 4 GB RAM minimum
- Windows 10/11, macOS, ou Linux

### Étapes d'installation

1. **Cloner le repository**
   ```bash
   git clone https://github.com/githubforsim/TicketsApp.git
   cd TicketsApp
   ```

2. **Démarrer les conteneurs Docker**
   ```bash
   docker-compose up -d
   ```

3. **Accéder à l'application**
   - **Application** : http://localhost:8081
   - **PhpMyAdmin** : http://localhost:8080

4. **Connexion par défaut**
   - Admin : `Frederic` / (voir base de données)
   - User : `UserTest` / (voir base de données)

### Installation détaillée

Voir [aboutThisApp.md](./aboutThisApp.md) pour la documentation complète.

## 📚 Documentation

| Document | Description |
|----------|-------------|
| [aboutThisApp.md](./aboutThisApp.md) | Documentation complète du projet |
| [TESTS.md](./TESTS.md) | Guide des tests unitaires |
| [docs/html/](./docs/html/index.html) | Documentation Doxygen générée |

## 🧪 Tests

Suite complète de **36 tests unitaires** avec base de données de test dédiée :
- ✅ UserModel (5 tests)
- ✅ TicketModel (13 tests)
- ✅ AdminModel (11 tests)
- ✅ Database Functions (9 tests)

### Créer la base de données de test

**Windows (PowerShell)** :
```powershell
.\create-test-db.ps1
```

**Linux/Mac** :
```bash
chmod +x create-test-db.sh
./create-test-db.sh
```

### Exécuter les tests

**Windows (PowerShell)** :
```powershell
.\run-tests.ps1
```

**Linux/Mac** :
```bash
chmod +x run-tests.sh
./run-tests.sh
```

**Avec couverture de code** :
```powershell
.\run-tests.ps1 -Coverage
```

Voir [TESTS.md](./TESTS.md) pour plus de détails.

## 🛠️ Technologies

### Backend
- **PHP 8.3** - Langage principal
- **MySQL 9.2** - Base de données
- **Apache 2.4** - Serveur web
- **PDO** - Couche d'abstraction base de données

### Frontend
- **HTML5 / CSS3** - Structure et style
- **JavaScript** - Interactivité
- **Bootstrap** (si applicable)

### DevOps
- **Docker** - Conteneurisation
- **Docker Compose** - Orchestration
- **PHPUnit 10.5** - Tests unitaires
- **Traefik** - Reverse proxy (production)
- **Let's Encrypt** - Certificats SSL (production)

### Architecture
- **Pattern MVC** (Modèle-Vue-Contrôleur)
- **Routing centralisé** via `routes.php`
- **Séparation des responsabilités**

## 📁 Structure du projet

```
TicketsApp/
├── www/                          # Code source application
│   ├── app/
│   │   └── src/
│   │       ├── Controllers/      # Logique métier
│   │       ├── Models/           # Accès base de données
│   │       └── Views/            # Templates HTML
│   ├── config/                   # Configuration
│   │   ├── constants.php         # Constantes DB
│   │   ├── database.php          # Connexion PDO
│   │   └── routes.php            # Routage
│   └── index.php                 # Point d'entrée
├── tests/                        # Tests unitaires
│   ├── Models/                   # Tests des modèles
│   ├── Config/                   # Tests configuration
│   └── bootstrap.php             # Bootstrap tests
├── docker/                       # Configuration Docker
├── docs/                         # Documentation générée
├── backups/                      # Sauvegardes SQL
├── docker-compose.yml            # Orchestration Docker
├── phpunit.xml                   # Configuration PHPUnit
├── composer.json                 # Dépendances PHP
├── run-tests.ps1                 # Script tests Windows
├── run-tests.sh                  # Script tests Linux/Mac
├── TESTS.md                      # Documentation tests
└── aboutThisApp.md               # Documentation projet
```

## 🔧 Configuration

### Environnement de développement (local)

Les ports ont été modifiés pour éviter les conflits avec le firewall Windows :
- **HTTP** : 8081 (au lieu de 80)
- **HTTPS** : 8443 (au lieu de 443)
- **PhpMyAdmin** : 8080

### Base de données

Configuration dans `www/config/constants.php` :
```php
define('DB_SERVER', 'db');
define('DB_NAME', 'ticketsApp');
define('DB_USER', 'root');
define('DB_PASSWORD', 'Plouzane**');
```

### Volume Docker

Les données MySQL sont persistées dans un volume nommé `db_data` pour éviter les problèmes de permissions Windows.

## 📊 Schéma de base de données

L'application utilise **12 tables** :

| Table | Description |
|-------|-------------|
| `user` | Utilisateurs (username, mail, role) |
| `ticket` | Tickets de support |
| `statut` | Statuts des tickets (5 états) |
| `urgence` | Niveaux d'urgence |
| `type` | Types de tickets (Bug, Amélioration, Question) |
| `produit` | Produits/services |
| `user_produit` | Association utilisateurs ↔ produits |
| `attachments` | Pièces jointes |
| `chat_messages` | Messages échangés |
| `evenement` | Historique événements |
| `statut_evenement` | Types d'événements |
| `ticket_save` | Versions sauvegardées |

## 🔄 Workflow utilisateur typique

1. **Connexion** → Authentification
2. **Création ticket** → Formulaire (titre, description, urgence, type, produit, pièces jointes)
3. **Ticket visible** → Dashboard (statut: Nouveau)
4. **Admin traite** → Changement statut "En cours"
5. **Échanges messages** → Chat intégré
6. **Admin résout** → Statut "Résolu"
7. **User valide** → Fermeture ou réouverture
8. **Ticket fermé** → Archivé mais consultable

## 🆘 Support

### Problèmes courants

**Docker ne démarre pas**
- Vérifier que Docker Desktop est lancé
- Vérifier les ports 8081, 8443, 8080 ne sont pas utilisés

**Erreur de connexion à la base de données**
- Vérifier que le conteneur MySQL est démarré : `docker-compose ps`
- Vérifier les logs : `docker-compose logs db`

**Tests échouent**
- Vérifier que les conteneurs sont démarrés
- Réinstaller les dépendances : `docker exec <container> composer install`

### Logs

```bash
# Logs Apache
docker logs ticketsapp-web-1

# Logs MySQL
docker logs ticketsapp-db-1

# Logs en temps réel
docker-compose logs -f
```

## 👨‍💻 Développement

### Ajouter une fonctionnalité

1. Créer le Model dans `www/app/src/Models/`
2. Créer le Controller dans `www/app/src/Controllers/`
3. Créer les Views dans `www/app/src/Views/`
4. Ajouter les routes dans `www/config/routes.php`
5. Écrire les tests dans `tests/`

### Lancer les tests avant commit

```bash
.\run-tests.ps1
```

### Générer la documentation Doxygen

```bash
doxygen Doxyfile
```

## 📄 Licence

Développé par **FORSIM** pour la gestion interne des tickets de support.

## 🤝 Contribution

Contributions bienvenues ! Veuillez :
1. Fork le projet
2. Créer une branche feature (`git checkout -b feature/AmazingFeature`)
3. Commit les changements (`git commit -m 'Add AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrir une Pull Request

## 📞 Contact

**FORSIM**
- Email : contact@forsim.net
- Website : https://forsim.net

---

**Version** : 1.0.0  
**Dernière mise à jour** : Décembre 2024  
**Statut** : ✅ Production Ready
