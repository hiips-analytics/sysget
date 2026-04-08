# SysGET
## Systeme de Gestion d'Emploi du Temps

## Installation

1. **Cloner le dépôt** :
   ```bash
   git clone https://github.com/hiips-analytics/sysget.git
   cd sysget
   ```

2. **Installer les dépendances PHP** :
   ```bash
   composer dump-autoload && composer install
   ```

## Effectuer les tests

1. Commandes d'Exécution
   ```bash
   # Tests unitaires
   ./phpunit.phar tests/Unit/

   # Tests d'intégration  
   ./phpunit.phar tests/Integration/SessionCreationWorkflowTest.php

   # Tous les tests
   ./phpunit.phar

   # Avec couverture (nécessite Xdebug)
   ./phpunit.phar --coverage-html coverage/
   ```