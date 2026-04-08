#!/bin/bash

# Script pour exécuter les tests PHPUnit
echo "Exécution des tests PHPUnit..."

if [ -f "phpunit.phar" ]; then
    ./phpunit.phar
else
    echo "Erreur: phpunit.phar non trouvé. Veuillez le télécharger d'abord."
    exit 1
fi