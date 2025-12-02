# TicketsApp - Système de gestion de tickets de support

## 📋 Vue d'ensemble

**TicketsApp** est une application web de **gestion de tickets de support/incidents** développée en PHP avec architecture MVC (Modèle-Vue-Contrôleur). Elle permet aux utilisateurs de créer, suivre et gérer des demandes de support pour différents produits.

---

## 🎯 Fonctionnalités principales

### 1. Système d'authentification
- Connexion utilisateur avec authentification par mot de passe haché (bcrypt)
- Deux rôles : **admin** et **user**
- Gestion de la réinitialisation de mot de passe
- Sessions PHP pour maintenir l'état de connexion

### 2. Gestion des tickets
Les tickets peuvent avoir **5 statuts** :
- **Nouveau** (nouveau ticket) - statut_id = 1
- **En cours** (en traitement) - statut_id = 2
- **Résolu** (résolu, en attente validation) - statut_id = 3  
- **Fermé** (terminé et validé) - statut_id = 4
- **En attente** (suspendu) - statut_id = 5

**Chaque ticket contient** :
- Titre et description
- Date de création
- Niveau d'urgence (Normale, Urgente)
- Type (Bug, Amélioration, Question)
- Produit associé
- Utilisateur demandeur
- Pièces jointes (fichiers)

### 3. Système de messages
- Chat intégré pour chaque ticket
- Messages entre utilisateurs et administrateurs
- Historique des conversations
- Messages disponibles pour tickets ouverts, résolus et fermés

### 4. Historique et événements
- Suivi de tous les changements via la table `evenement`
- Enregistrement automatique des événements :
  - **Opened** (statut_evenement_id = 1) : ticket ouvert/réouvert
  - **Solved** (statut_evenement_id = 2) : ticket marqué résolu
  - **Closed** (statut_evenement_id = 3) : ticket fermé
  - **Attachment added** (statut_evenement_id = 5) : ajout de pièce jointe
  - **Attachment deleted** (statut_evenement_id = 4) : suppression de pièce jointe
  - **Text Changed** (statut_evenement_id = 7) : modification du titre/description
  - **Message** (statut_evenement_id = 8) : message envoyé
- Comparaison d'états via la table `ticket_save` (versions sauvegardées)
- Affichage chronologique des modifications avec détails
- Historique par utilisateur et par produit

### 5. Gestion multi-produits
- Association utilisateurs ↔ produits
- Filtrage des tickets par produit
- Chaque utilisateur ne voit que ses produits assignés

---

## 👥 Rôles et permissions

### Utilisateur standard (user)
- Créer de nouveaux tickets
- Voir ses propres tickets (ouverts, résolus, fermés)
- Ajouter des messages aux tickets
- Ajouter/supprimer des pièces jointes
- Modifier les détails de ses tickets
- Voir uniquement les produits qui lui sont assignés

### Administrateur (admin)
- Toutes les permissions utilisateur
- Voir TOUS les tickets de tous les utilisateurs
- Créer de nouveaux utilisateurs
- Gérer les produits
- Changer le statut des tickets (ouvrir/résoudre/fermer)
- Accéder à l'historique complet des événements
- Modifier les mots de passe des utilisateurs
- Vue d'ensemble avec statistiques

---

## 🗄️ Structure de la base de données

### Tables principales :
1. **`user`** : utilisateurs (username, mail, entreprise, pwd, role)
2. **`ticket`** : tickets de support
3. **`statut`** : statuts des tickets (Nouveau, En cours, Résolu, Fermé, En attente)
4. **`urgence`** : niveaux d'urgence (Normale, Urgente)
5. **`type`** : types de tickets (Bug, Amélioration, Question)
6. **`produit`** : produits/services
7. **`user_produit`** : association utilisateurs ↔ produits
8. **`attachments`** : pièces jointes
9. **`chat_messages`** : messages échangés entre utilisateurs et admins
10. **`evenement`** : historique des événements (logs de tous les changements)
11. **`statut_evenement`** : types d'événements (Opened, Solved, Closed, Attachment added/deleted, etc.)
12. **`ticket_save`** : sauvegardes/versions de tickets pour comparaison d'états

---

## 🏗️ Architecture technique

### Pattern MVC :
- **Models** : `TicketModel`, `UserModel`, `AdminModel` - gestion base de données
- **Views** : fichiers PHP dans `/app/src/Views/` - interface utilisateur
- **Controllers** : `TicketController`, `AdminController`, `LoginController` - logique métier

### Routage :
- Routes masquées (URLs propres) : `/ticketsApp/admin/tickets/details/5`
- Système de routage dans `routes.php`
- Gestion GET/POST séparée

### Sécurité :
- Mots de passe hashés avec `password_hash()`
- Sessions PHP sécurisées
- Content Security Policy (CSP)
- Protection XSS avec `htmlspecialchars()`
- Headers de sécurité (HSTS, X-Frame-Options, etc.)

### Stack technique :
- **Backend** : PHP 8.3
- **Base de données** : MySQL 9.2
- **Serveur web** : Apache 2.4
- **Conteneurisation** : Docker + Docker Compose
- **Reverse proxy** : Traefik (en production)
- **Certificats SSL** : Let's Encrypt (en production)

---

## 📊 Workflow typique

1. **Utilisateur se connecte** → authentification
2. **Crée un ticket** → choix produit, urgence, type (Bug/Amélioration/Question), description + pièces jointes
3. **Ticket visible** dans liste "Tickets en cours" (statut: Nouveau ou En cours)
4. **Échanges via messages** (chat_messages) entre user et admin
5. **Admin résout** → statut "Résolu" (En attente validation client)
6. **User valide ou refuse** → peut rouvrir le ticket (retour à "En cours") ou fermer (statut "Fermé")
7. **Ticket fermé** → archivé mais toujours consultable avec historique complet

---

## 🎨 Interface utilisateur

- **Dashboard** avec liste des tickets
- **Sidebar** de navigation avec filtres (ouverts/résolus/fermés)
- **Détails ticket** avec historique complet
- **Système de chat** intégré pour chaque ticket
- **Page de comparaison** pour voir les modifications
- **Gestion utilisateurs** (admin uniquement)

---

## 🚀 Installation et déploiement

### Prérequis
- Docker et Docker Compose
- Nom de domaine configuré (ticketsapp.forsim.net)

### Démarrage
```bash
# Démarrer les conteneurs
docker-compose up -d

# Vérifier les logs
docker-compose logs -f

# Accéder à l'application
# En local : http://localhost:8081
# En production : https://ticketsapp.forsim.net
```

### Initialisation de la base de données
- Le fichier `ticket_app.sql` (ou le backup le plus récent dans `backups/`) est monté dans `/docker-entrypoint-initdb.d/dump.sql`
- MySQL initialise automatiquement la base de données au premier démarrage du conteneur
- Les données sont persistées dans un volume Docker nommé `db_data`

### Services Docker
- **web** : Apache + PHP (port 8081 pour HTTP, 8443 pour HTTPS en développement local)
- **db** : MySQL (port 3306)
- **phpmyadmin** : Interface de gestion MySQL (port 8080)
- **traefik** : Reverse proxy avec SSL automatique (en production uniquement)

### Structure des répertoires
```
/srv/TicketsApp/
├── www/                    # Code source de l'application
│   ├── app/
│   │   ├── src/
│   │   │   ├── Controllers/
│   │   │   ├── Models/
│   │   │   └── Views/
│   │   └── public/
│   │       ├── css/
│   │       └── js/
│   ├── config/            # Configuration et routage
│   └── index.php          # Point d'entrée
├── db/                    # Données MySQL
├── certs/                 # Certificats SSL
├── docker/                # Dockerfiles
└── docker-compose.yml
```

---

## 📝 Configuration

### Fichiers de configuration
- **`config/constants.php`** : Constantes de connexion à la base de données
  - DB_USER, DB_PASSWORD, DB_NAME, DB_SERVER
  - NOM_ADMIN : nom de l'administrateur principal
- **`config/database.php`** : Fonctions de connexion PDO
- **`config/routes.php`** : Système de routage centralisé (switch/case pour GET et POST)

### Base de données
- **Host** : db (nom du service Docker)
- **Database** : ticketsApp
- **User** : root
- **Password** : configuré dans constants.php et docker-compose.yml
- **Port** : 3306 (interne au réseau Docker)

### URLs
- **Application (Production)** : https://ticketsapp.forsim.net
- **Application (Développement local)** : http://localhost:8081
- **PhpMyAdmin** : http://localhost:8080

---

## 🔧 Maintenance

### Notes importantes pour le développement local (Windows)
- **Ports modifiés** : 8081 (HTTP) et 8443 (HTTPS) au lieu de 80/443 pour éviter les conflits avec le firewall Windows
- **Volume nommé** : La base de données utilise un volume Docker nommé (`db_data`) plutôt qu'un bind mount pour éviter les problèmes de permissions Windows
- **PHP 8.3** : Version stable utilisée au lieu de 8.4 pour compatibilité

### Sauvegarde
La base de données est persistée dans un volume Docker nommé `db_data`

### Backups manuels
Les sauvegardes SQL sont stockées dans le dossier `./backups/` avec horodatage

### Logs
```bash
# Logs Apache
docker logs ticketsapp-web-1

# Logs MySQL
docker logs ticketsapp-db-1

# Logs Traefik
docker logs traefik
```

---

## 📄 Licence

Développé par FORSIM pour la gestion interne des tickets de support.

---

## 🔗 Liens utiles

- **Documentation générée (Doxygen)** : `/docs/html/index.html`
- **PhpMyAdmin** : http://localhost:8080
- **Interface principale (local)** : http://localhost:8081
- **Interface principale (production)** : https://ticketsapp.forsim.net

## 🔑 Utilisateurs par défaut

D'après les dumps SQL, voici les utilisateurs de test :
- **Admin** : `Frederic` / `frederic.zitta@forsim.net` (rôle: admin)
- **User** : `UserTest` / `usertest@gmail.com` (rôle: user)
- Les mots de passe sont hashés avec bcrypt dans la base de données
