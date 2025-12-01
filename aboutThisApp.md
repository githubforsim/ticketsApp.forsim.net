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
Les tickets peuvent avoir **3 statuts** :
- **En cours** (ouvert) - statut_id = 1
- **En attente validation client** (résolu) - statut_id = 2  
- **Réalisée** (fermé) - statut_id = 3

**Chaque ticket contient** :
- Titre et description
- Date de création
- Niveau d'urgence (Normale, Urgente)
- Type (Correction, Evolution)
- Produit associé
- Utilisateur demandeur
- Pièces jointes (fichiers)

### 3. Système de messages
- Chat intégré pour chaque ticket
- Messages entre utilisateurs et administrateurs
- Historique des conversations
- Messages disponibles pour tickets ouverts, résolus et fermés

### 4. Historique et événements
- Suivi de tous les changements (événements)
- Comparaison d'états (sauvegarde des versions)
- Logs détaillés de toutes les actions
- Affichage chronologique des modifications

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
3. **`statut`** : statuts des tickets (En cours, Résolu, Fermé)
4. **`urgence`** : niveaux d'urgence (Normale, Urgente)
5. **`type`** : types de tickets (Correction, Evolution)
6. **`produit`** : produits/services
7. **`user_produit`** : association utilisateurs ↔ produits
8. **`attachments`** : pièces jointes
9. **`evenement`** : historique des modifications
10. **`ticket_save`** : sauvegardes pour comparaison

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
- **Backend** : PHP 8.4
- **Base de données** : MySQL 8
- **Serveur web** : Apache 2.4
- **Conteneurisation** : Docker + Docker Compose
- **Reverse proxy** : Traefik
- **Certificats SSL** : Let's Encrypt

---

## 📊 Workflow typique

1. **Utilisateur se connecte** → authentification
2. **Crée un ticket** → choix produit, urgence, type, description + pièces jointes
3. **Ticket visible** dans liste "Tickets en cours"
4. **Échanges via messages** entre user et admin
5. **Admin résout** → statut "En attente validation"
6. **User valide ou refuse** → peut rouvrir le ticket
7. **Ticket fermé** → archivé mais toujours consultable

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
```

### Services Docker
- **web** : Apache + PHP (port 80, 443)
- **db** : MySQL (port 3306)
- **phpmyadmin** : Interface de gestion MySQL (port 8080)
- **traefik** : Reverse proxy avec SSL automatique

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

### Base de données
- **Host** : db
- **Database** : ticketsApp
- **User** : root
- **Password** : configuré dans docker-compose.yml

### URLs
- **Application** : https://ticketsapp.forsim.net
- **PhpMyAdmin** : http://localhost:8080

---

## 🔧 Maintenance

### Sauvegarde
La base de données est persistée dans le dossier `./db/`

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

- Documentation générée : `/docs/html/index.html`
- PhpMyAdmin : http://localhost:8080
- Interface principale : https://ticketsapp.forsim.net
