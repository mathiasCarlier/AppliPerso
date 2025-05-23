# Documentation de la Base de Données

## Vue d'ensemble
La base de données `carlierm_cinema` est conçue pour gérer l'ensemble des opérations d'un système de commandes de cinéma. Elle gère les produits, les commandes, les utilisateurs et leurs rôles.

## Installation
1. Assurez-vous d'avoir MySQL/MariaDB installé
2. Utilisez le fichier `carlierm_cinema (1).sql` pour créer la base de données
3. Exécutez le script avec la commande :
   ```sql
   mysql -u root -p < carlierm_cinema.sql
   ```

## Structure de la Base de Données

### Tables Principales

#### Responsable
- Gestion des utilisateurs du système
- Stockage des informations d'authentification
- Gestion des rôles et permissions

#### Commande
- Enregistrement des commandes clients
- Suivi des statuts
- Liaison avec les produits et quantités

#### Produit
- Catalogue des produits disponibles
- Gestion des prix et disponibilités
- Catégorisation des produits

#### Categorie
- Classification des produits
- Organisation hiérarchique du catalogue

### Tables Supplémentaires

#### Client
- Informations des clients
- Historique des commandes
- Coordonnées de contact

#### Taille
- Gestion des différentes tailles de produits
- Prix associés via la table Avoir

#### Statut
- États possibles des commandes
- Gestion du workflow des commandes

#### SousCategorie
- Sous-catégorisation fine des produits
- Rattachement à une catégorie principale

### Relations Clés

1. Commande - Produit
   - Relation many-to-many via table de liaison
   - Gestion des quantités et prix unitaires

2. Produit - Categorie
   - Organisation hiérarchique des produits
   - Gestion des sous-catégories

3. Responsable - Role
   - Attribution des permissions
   - Gestion des accès

### Relations Additionnelles

4. Produit - Taille (via Avoir)
   - Association des tailles disponibles
   - Gestion des prix par taille

5. Client - Commande
   - Historique des commandes par client
   - Suivi client

6. Commande - Statut
   - Gestion du cycle de vie des commandes
   - Traçabilité des changements d'état

## Maintenance

### Sauvegarde
```bash
mysqldump -u [user] -p carlierm_cinema > backup_[date].sql
```

### Restauration
```bash
mysql -u [user] -p carlierm_cinema < backup_[date].sql
```

## Sécurité
- Les mots de passe sont hashés
- Utilisation de transactions pour l'intégrité des données
- Gestion des droits utilisateurs via rôles

## Optimisation
- Index sur les clés primaires et étrangères
- Optimisation des requêtes fréquentes
- Cache des requêtes via Doctrine

## Notes Importantes
1. Ne pas modifier directement la structure de la base sans mise à jour des entités Symfony
2. Maintenir les contraintes d'intégrité lors des modifications
3. Suivre les conventions de nommage établies
