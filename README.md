USER :
# Blog CodeIgniter & Bootstrap

Un blog moderne développé avec CodeIgniter 4 et Bootstrap, conçu pour démontrer mes compétences en développement web PHP, gestion d’utilisateurs, et intégration front-end responsive.

## ✨ Fonctionnalités principales

- Authentification (inscription, connexion, déconnexion)
- Gestion des utilisateurs (admin, utilisateurs standards)
- Création, édition et suppression d’articles (CRUD)
- Affichage dynamique des articles de blog
- Interface responsive grâce à Bootstrap
- Sécurité : gestion des sessions, validation des formulaires
- Système de rôles (admin/utilisateur)
- Pages d’erreur personnalisées

## 🛠️ Stack technique

- **Backend** : PHP 8+, CodeIgniter 4
- **Frontend** : HTML5, CSS3, Bootstrap 4/5
- **Base de données** : MySQL
- **Autres** : Composer, MVC, sessions PHP

## 🚀 Installation locale

1. **Cloner le dépôt**
	```bash
	git clone <url-du-repo>
	cd exemple-blog-codeigniter-bootstrap-code
	```

2. **Installer les dépendances**
	```bash
	composer install
	```

3. **Configurer l’environnement**
	- Copier `.env.example` en `.env` et adapter les paramètres (base de données, etc.)
	- Importer la base de données depuis `db_backup/ci4 2(2).sql`

4. **Lancer le serveur**
	```bash
	php spark serve
	```
	Accéder à l’application sur [http://localhost:8080](http://localhost:8080)

## 👤 Accès de démonstration

| Email                    | Mot de passe | Rôle   |
|--------------------------|--------------|--------|
| romaincalmelet@gmail.com | 1234         | Admin  |
| josh@gmail.com           | qwertz       | User   |
| ben@gmail.com            | Salut-salu1  | User   |


## 📂 Structure du projet

- `app/` : Contrôleurs, modèles, vues, configuration
- `public/` : Fichiers accessibles publiquement (index.php, assets)
- `db_backup/` : Sauvegarde de la base de données
- `vendor/` : Dépendances Composer

## 📝 À propos

Ce projet a été réalisé pour mettre en avant mes compétences en développement web PHP, architecture MVC, et intégration front-end. N’hésitez pas à me contacter pour toute question ou suggestion !