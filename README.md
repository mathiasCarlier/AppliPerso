# Application de Gestion de Commandes - Cinéma

## Description du Projet
Cette application web, développée avec Symfony, est conçue pour gérer efficacement les commandes de produits alimentaires dans un cinéma. Elle répond aux besoins spécifiques d'un établissement cinématographique en permettant :
- La gestion en temps réel des commandes clients
- Le suivi des stocks et des produits
- L'administration des utilisateurs et des droits
- La génération de rapports et statistiques

### Contexte
Projet développé dans le cadre du BTS SIO SLAM, cette application répond aux exigences professionnelles d'un cinéma moderne en matière de gestion de commandes et de service client.

### Objectifs
- Optimiser la gestion des commandes alimentaires
- Améliorer l'efficacité du service
- Faciliter le suivi des ventes
- Assurer une traçabilité complète des opérations

## Prérequis Techniques

### Environnement de Développement
- PHP 8.0 ou supérieur
- Composer
- Symfony CLI
- MySQL 8.0 ou MariaDB 10.4+
- Node.js 14+ et npm

### Configuration Système Recommandée
- Processeur : Intel Core i5 ou équivalent
- RAM : 8 Go minimum
- Espace disque : 1 Go minimum
- Système d'exploitation : Windows 10/11, Linux, macOS

### Dépendances Principales
- Symfony 6.x
- Doctrine ORM
- Twig Template Engine
- Bootstrap 5
- SweetAlert2

## Structure du Projet

```plaintext
AppliCinema/
├── src/
│   ├── Controller/           # Contrôleurs de l'application
│   │   ├── CommandeController.php
│   │   ├── ProduitController.php
│   │   └── SecurityController.php
│   ├── Entity/              # Entités Doctrine
│   │   ├── Avoir.php
│   │   ├── Categorie.php
│   │   ├── Client.php
│   │   ├── Commande.php
│   │   ├── Composer.php
│   │   ├── Famille.php
│   │   ├── LigneCommande.php
│   │   ├── Possede.php
│   │   ├── Produit.php
│   │   ├── Responsable.php
│   │   ├── Role.php
│   │   ├── Salle.php
│   │   ├── SousCategorie.php
│   │   ├── Statut.php
│   │   └── Taille.php
│   └── Repository/          # Repositories
├── templates/               # Templates Twig
│   ├── accueil/
│   ├── commande/
│   ├── compte/
│   ├── inscription/
│   ├── produit/
│   ├── report/
│   ├── responsable/
│   ├── security/
│   └── base.html.twig
├── public/                 # Ressources publiques
│   ├── css/
│   ├── js/
│   └── images/
├── config/                # Configuration
│   ├── packages/
│   └── routes/
└── docs/                  # Documentation
    ├── technical/        # Documentation technique
    ├── user/            # Guides utilisateurs
    └── maintenance/     # Guides de maintenance
```
```

