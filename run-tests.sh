#!/usr/bin/env bash

# run-tests.sh
# Script pour exécuter les tests unitaires dans le conteneur Docker

set -e

echo "=========================================="
echo "TicketsApp - Tests Unitaires"
echo "=========================================="
echo ""

# Couleurs pour l'output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Vérifier si Docker est en cours d'exécution
if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}❌ Erreur: Docker n'est pas en cours d'exécution${NC}"
    exit 1
fi

# Vérifier si le conteneur web existe
if ! docker ps -a --format '{{.Names}}' | grep -q "ticketsapp.*web"; then
    echo -e "${RED}❌ Erreur: Le conteneur web n'existe pas${NC}"
    echo "Veuillez démarrer l'application avec: docker-compose up -d"
    exit 1
fi

# Récupérer le nom du conteneur web
CONTAINER_NAME=$(docker ps --format '{{.Names}}' | grep "ticketsapp.*web" | head -n 1)

if [ -z "$CONTAINER_NAME" ]; then
    echo -e "${YELLOW}⚠️  Le conteneur web n'est pas démarré${NC}"
    echo "Démarrage du conteneur..."
    docker-compose up -d web
    sleep 3
    CONTAINER_NAME=$(docker ps --format '{{.Names}}' | grep "ticketsapp.*web" | head -n 1)
fi

echo -e "${GREEN}✓ Conteneur trouvé: $CONTAINER_NAME${NC}"
echo ""

# Installer PHPUnit si nécessaire
echo "📦 Vérification de PHPUnit..."
docker exec $CONTAINER_NAME bash -c "
    if [ ! -f /var/www/html/vendor/bin/phpunit ]; then
        echo 'Installation de Composer et PHPUnit...'
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
        cd /var/www/html && composer install --no-interaction
    fi
"

echo ""
echo "🧪 Exécution des tests..."
echo "=========================================="

# Exécuter les tests
if [ "$1" == "--coverage" ]; then
    echo -e "${YELLOW}Mode: Couverture de code${NC}"
    docker exec $CONTAINER_NAME /var/www/html/vendor/bin/phpunit --coverage-text --coverage-html /var/www/html/coverage
    echo ""
    echo -e "${GREEN}✓ Rapport de couverture généré dans: ./coverage/index.html${NC}"
elif [ "$1" == "--filter" ] && [ -n "$2" ]; then
    echo -e "${YELLOW}Mode: Filtre sur '$2'${NC}"
    docker exec $CONTAINER_NAME /var/www/html/vendor/bin/phpunit --filter "$2"
else
    docker exec $CONTAINER_NAME /var/www/html/vendor/bin/phpunit
fi

# Capturer le code de sortie
EXIT_CODE=$?

echo ""
echo "=========================================="
if [ $EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}✅ Tests réussis!${NC}"
else
    echo -e "${RED}❌ Tests échoués (code: $EXIT_CODE)${NC}"
fi
echo "=========================================="

exit $EXIT_CODE
