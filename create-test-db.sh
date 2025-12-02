#!/usr/bin/env bash

# create-test-db.sh
# Script pour créer et initialiser la base de données de test

set -e

DB_NAME="ticketsApp_test"
DB_USER="root"
DB_PASSWORD="bODw50eeKjPT/rVW"
CONTAINER="ticketsappforsimnet-db-1"
DUMP_FILE="backups/ticketsApp_2025-11-30_18-33.sql"

# Couleurs
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}==========================================${NC}"
echo -e "${CYAN}Base de données de test - TicketsApp${NC}"
echo -e "${CYAN}==========================================${NC}"
echo ""

# Vérifier si Docker est en cours d'exécution
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Erreur: Docker n'est pas en cours d'exécution${NC}"
    exit 1
fi

# Vérifier si le conteneur DB existe
if ! docker ps -a --format '{{.Names}}' | grep -q "$CONTAINER"; then
    echo -e "${RED}❌ Erreur: Le conteneur $CONTAINER n'existe pas${NC}"
    exit 1
fi

echo -e "${GREEN}✓ Conteneur DB trouvé: $CONTAINER${NC}"

# Supprimer la base de données existante si --reset
if [ "$1" == "--reset" ]; then
    echo ""
    echo -e "${YELLOW}⚠️  Mode RESET activé - Suppression de la base existante...${NC}"
    docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "DROP DATABASE IF EXISTS $DB_NAME;" 2>/dev/null || true
    echo -e "${GREEN}✓ Base de données supprimée${NC}"
fi

# Créer la base de données
echo ""
echo -e "${CYAN}📦 Création de la base de données '$DB_NAME'...${NC}"
docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;" 2>/dev/null

echo -e "${GREEN}✓ Base de données créée avec succès${NC}"

# Importer le dump SQL
echo ""
echo -e "${CYAN}📥 Import du dump SQL...${NC}"

if [ ! -f "$DUMP_FILE" ]; then
    echo -e "${RED}❌ Erreur: Le fichier $DUMP_FILE n'existe pas${NC}"
    exit 1
fi

cat $DUMP_FILE | docker exec -i $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD $DB_NAME 2>/dev/null

echo -e "${GREEN}✓ Dump SQL importé avec succès${NC}"

# Vérifier les tables
echo ""
echo -e "${CYAN}🔍 Vérification des tables...${NC}"
TABLE_COUNT=$(docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "USE $DB_NAME; SHOW TABLES;" 2>/dev/null | tail -n +2 | wc -l)
echo -e "${GREEN}✓ $TABLE_COUNT tables trouvées${NC}"

docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "USE $DB_NAME; SHOW TABLES;" 2>/dev/null | tail -n +2 | while read table; do
    echo "  - $table"
done

# Statistiques
echo ""
echo -e "${CYAN}📊 Statistiques de la base de test:${NC}"

USERS=$(docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "SELECT COUNT(*) FROM $DB_NAME.user;" 2>/dev/null | tail -n +2)
TICKETS=$(docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "SELECT COUNT(*) FROM $DB_NAME.ticket;" 2>/dev/null | tail -n +2)
PRODUITS=$(docker exec $CONTAINER mysql -u$DB_USER -p$DB_PASSWORD -e "SELECT COUNT(*) FROM $DB_NAME.produit;" 2>/dev/null | tail -n +2)

echo "  - Utilisateurs: $USERS"
echo "  - Tickets: $TICKETS"
echo "  - Produits: $PRODUITS"

echo ""
echo -e "${CYAN}==========================================${NC}"
echo -e "${GREEN}✅ Base de données de test prête!${NC}"
echo -e "${CYAN}==========================================${NC}"
echo ""
echo -e "${YELLOW}Vous pouvez maintenant lancer les tests:${NC}"
echo "  ./run-tests.sh"
echo ""

exit 0
