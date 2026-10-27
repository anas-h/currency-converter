# Convertisseur de devises

Application Symfony 7.4 / PHP 8.3 de conversion de devises avec authentification,
basée sur l'API gratuite [Frankfurter](https://frankfurter.dev) (taux de référence
de la Banque centrale européenne).

## Démarrage

Prérequis : Docker et Docker Compose v2.

```bash
make start      # construit les images, démarre, installe les dépendances, migre
make test       # lance les tests
```

Application : <http://localhost:8000>. Autres commandes : `make help`.

Ports utilisés : 8000 (web) et 5433 (PostgreSQL), modifiables via `HTTP_PORT` et `POSTGRES_PORT`.

## Fonctionnalités

- Inscription, connexion et déconnexion (mots de passe hachés, CSRF, limitation
  des tentatives de connexion).
- Conversion d'un montant entre les devises proposées par Frankfurter, avec une
  arithmétique décimale exacte (`brick/money`), sans `float`.
- Historique des conversions de l'utilisateur connecté, avec le taux et la date
  du taux utilisés.
- Cache des taux (une heure) pour ne pas solliciter l'API à chaque conversion.

## Structure

```
src/
├── Controller/   Login, inscription, conversion, historique
├── Entity/       User, Conversion (Doctrine)
├── Repository/
├── Model/        Objets non persistés : Rate, ConversionResult, ConversionRequest (DTO du formulaire)
├── Service/
│   ├── Rate/     ExchangeRateProviderInterface, FrankfurterRateProvider, CachedRateProvider (décorateur)
│   ├── CurrencyConverter    Calcul de la conversion
│   └── ConversionHistory    Enregistrement et lecture de l'historique
├── Form/         RegistrationType, ConversionType
└── Exception/    UnknownCurrencyException, RateProviderUnavailableException
```

Choix principaux :

- Le fournisseur de taux est derrière une interface. En test, il est remplacé par
  `App\Tests\Double\InMemoryRateProvider` (`config/services_test.yaml`) : aucun
  appel réseau pendant les tests.
- Le cache est un décorateur de cette interface, transparent pour le reste du code.
- Les montants sont stockés en `NUMERIC` et manipulés en chaînes décimales.
- Pile Docker : php-fpm, nginx, PostgreSQL 16. Le code est monté en volume et le
  conteneur PHP tourne avec l'UID de l'hôte.

## Qualité

- `make test` : PHPUnit, tests unitaires et fonctionnels (base `app_test`, chaque
  test dans une transaction annulée via `dama/doctrine-test-bundle`).
- `make phpstan` : analyse statique niveau 6.
- `make cs` : PHP CS Fixer, règles Symfony.
- `make ci` : enchaîne tout. Un workflow GitHub Actions fait de même.
