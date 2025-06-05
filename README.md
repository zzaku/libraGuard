# LibraGuard

## Description
LibraGuard est une application de gestion de bibliothèque qui permet aux utilisateurs de gérer les livres, les emprunts, et les utilisateurs. Elle offre des fonctionnalités pour les administrateurs et les utilisateurs standard.

## Fonctionnalités
- **Gestion des livres** : Ajout, modification, suppression, et consultation des livres.
- **Gestion des emprunts** : Emprunt et retour de livres, suivi des emprunts en cours et en retard.
- **Gestion des utilisateurs** : Inscription, connexion, et gestion des rôles utilisateur.
- **Interface administrateur** : Gestion des utilisateurs et des livres.

## Prérequis
- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Serveur web (Apache, Nginx, etc.)

## Installation
1. Clonez le dépôt :
   ```bash
   git clone https://github.com/votre-utilisateur/libraGuard.git
   ```

2. Configurez votre serveur web pour pointer vers le répertoire du projet.

3. Importez la base de données :
   ```bash
   mysql -u votre_utilisateur -p < SQL/database.sql
   ```

4. Configurez le fichier `config.php` avec vos informations de base de données.

5. Accédez à l'application via votre navigateur.

## Utilisation
- **Connexion** : Utilisez les identifiants par défaut pour l'administrateur (admin@libraguard.com / admin123).
- **Gestion des livres** : Ajoutez, modifiez, ou supprimez des livres via l'interface administrateur.
- **Emprunts** : Les utilisateurs peuvent emprunter et retourner des livres via l'interface utilisateur.

## Contribution
Les contributions sont les bienvenues ! N'hésitez pas à ouvrir une issue ou à soumettre une pull request.
