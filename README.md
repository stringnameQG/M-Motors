# M-Motors

Application professionnelle à destination de l'entreprise **M-Motors** pour la **vente et la location de véhicules**, ainsi que la **gestion des dossiers clients**. La solution permet une gestion 100% dématérialisée des documents liés aux transactions.

---

## Fonctionnalités principales

- **Vente de véhicules** : Mise en ligne et gestion des annonces.
- **Location de véhicules** : Réservation et suivi des locations.
- **Gestion des dossiers clients** : Centralisation des informations et documents.
- **Mise en avant de l’entreprise** : Présentation des services et valeurs de M-Motors.
- **Gestion des comptes** :
  - Compte **client** (accès limité).
  - Compte **gérant** (accès admin : `/vehicule`, `/dossier`, `/admin`, `/user`).
- **Stockage cloud** : Enregistrement et gestion des documents via **Cloudinary**.

---

## Prérequis

- PHP **8.4 ou supérieur**
- MySQL **8.0.32 ou supérieur**
- Composer **2.x**
- Extensions PHP requises : `ctype`, `iconv`
- Symfony CLI ([téléchargement](https://symfony.com/download))
- Un compte **Cloudinary** (pour la gestion des images) (https://cloudinary.com/)

---

## Installation

### 1. Cloner le projet
```bash
git clone [URL_DU_DÉPÔT]
cd M-Motors
```

---

### 2. Installer les dépendances
```bash
composer install
```

---

### 3. Configurer l’environnement
Copiez le fichier .env et adaptez les variables suivantes :
```bash
APP_ENV=dev
APP_SECRET=[générer_une_clé_via_`php bin/console secrets:generate-keys`]
DATABASE_URL="mysql://[utilisateur]:[mot_de_passe]@127.0.0.1:3306/m_motors?serverVersion=8.0.32&charset=utf8mb4"
MESSENGER_TRANSPORT_DSN=doctrine://default?auto_setup=0
MAILER_DSN=smtp://user\:pass@smtp.example.com\:port
CLOUDINARY_CLOUD_NAME=[ton_cloud_name]
CLOUDINARY_API_KEY=[ta_clé_api]
CLOUDINARY_API_SECRET=[ton_secret_api]
```

---

### 4. Initialiser la base de données
```bash
php bin/console doctrine\:database\:create
php bin/console doctrine\:migrations\:migrate
```

---

### 5. Lancer le serveur
Avec Symfony CLI :
```bash
symfony serve
```

---

Accès et rôles
```bash
| Route | Rôle requis |
| --- | --- |
| /login, /register | Public |
| /vente/, /location/, /commerce/vehicule/ | Public |
| /vehicule, /dossier, /admin, /user | ROLE_ADMIN |
```

Hiérarchie des rôles : ROLE_ADMIN > ROLE_USER.

---

Structure du projet

Backend : Symfony 8.4 (PHP)

Gestion des utilisateurs, véhicules, dossiers.
Messagerie asynchrone (Doctrine Messenger).
Notifications (Symfony Notifier).

Frontend : JavaScript, HTML, CSS
Base de données : MySQL
Stockage : Cloudinary (images et documents)

---

Tests
Lancer les tests unitaires :
```bash
php bin/phpunit
```