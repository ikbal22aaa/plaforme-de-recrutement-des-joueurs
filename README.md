# KoraJob - Plateforme de Recrutement Footballistique

KoraJob est une plateforme web innovante spécialement conçue pour moderniser et optimiser le recrutement footballistique. Elle connecte joueurs, entraîneurs et clubs pour faciliter la recherche de talents et d'opportunités sportives.

## 🚀 Fonctionnalités Principales

### Pour les Joueurs
- ✅ Création de profil complet avec vidéos de performance
- ✅ Recherche et candidature aux offres de clubs
- ✅ Système de notation et évaluation
- ✅ Messagerie directe avec les recruteurs
- ✅ Suivi des candidatures

### Pour les Entraîneurs
- ✅ Profil détaillé avec expérience et spécialités
- ✅ Évaluation des joueurs
- ✅ Publication d'offres d'emploi
- ✅ Gestion des formations proposées

### Pour les Clubs
- ✅ Recherche avancée de joueurs avec filtres
- ✅ Publication d'annonces de recrutement
- ✅ Organisation d'essais
- ✅ Gestion d'espace club complet

### Pour les Administrateurs
- ✅ Tableau de bord complet
- ✅ Gestion des utilisateurs et validation des comptes
- ✅ Modération des contenus
- ✅ Système de notifications
- ✅ Statistiques détaillées

## 🛠️ Technologies Utilisées

- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Backend**: PHP 7.4+
- **Base de données**: MySQL 5.7+
- **Serveur**: Apache (XAMPP recommandé)
- **Outils**: Visual Studio Code, phpMyAdmin

## 📋 Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur
- Apache Server
- XAMPP (recommandé pour le développement local)

## 🔧 Installation

### 1. Cloner le projet
```bash
git clone https://github.com/votre-username/korajob.git
cd korajob
```

### 2. Configuration de la base de données

#### Option A: Utilisation de XAMPP
1. Démarrez XAMPP
2. Activez Apache et MySQL
3. Ouvrez phpMyAdmin (http://localhost/phpmyadmin)
4. Créez une nouvelle base de données nommée `korajob`
5. Importez le fichier `database/korajob.sql`

#### Option B: Importation manuelle
```sql
-- Créer la base de données
CREATE DATABASE korajob CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Utiliser la base de données
USE korajob;

-- Importer le fichier SQL
SOURCE database/korajob.sql;
```

### 3. Configuration des fichiers

#### Modifier la configuration de la base de données
Éditez le fichier `config/database.php` si nécessaire :
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'korajob');
define('DB_USER', 'root');
define('DB_PASS', '');
```

### 4. Démarrage du serveur

#### Avec XAMPP
1. Placez le dossier du projet dans `C:\xampp\htdocs\`
2. Démarrez Apache et MySQL dans XAMPP
3. Accédez à `http://localhost/korajob`

#### Avec un serveur local
```bash
# Dans le dossier du projet
php -S localhost:8000
```

## 👥 Comptes de Test

### Administrateur
- **Email**: admin@korajob.com
- **Mot de passe**: admin123

### Joueur
- **Email**: ahmed@test.com
- **Mot de passe**: password123

### Entraîneur
- **Email**: youssef@test.com
- **Mot de passe**: password123

### Club
- **Email**: crb@test.com
- **Mot de passe**: password123

## 📁 Structure du Projet

```
korajob/
├── assets/
│   ├── css/
│   │   └── style.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── config/
│   └── database.php
├── includes/
│   └── functions.php
├── admin/
│   └── dashboard.php
├── joueur/
│   └── dashboard.php
├── entraineur/
├── club/
├── database/
│   └── korajob.sql
├── index.php
├── login.php
├── register.php
├── joueurs.php
├── entraineurs.php
├── contact.php
└── README.md
```

## 🎯 Utilisation

### 1. Inscription
- Accédez à la page d'inscription
- Choisissez votre type de compte (Joueur, Entraîneur, Club)
- Remplissez le formulaire avec vos informations
- Attendez la validation par l'administrateur

### 2. Connexion
- Utilisez vos identifiants pour vous connecter
- Accédez à votre tableau de bord personnalisé

### 3. Gestion du profil
- Complétez votre profil avec toutes les informations
- Ajoutez des vidéos de performance (pour les joueurs)
- Mettez à jour vos informations régulièrement

### 4. Recherche et recrutement
- Utilisez les filtres avancés pour trouver des profils
- Contactez directement les utilisateurs
- Suivez vos candidatures et messages

## 🔒 Sécurité

- Mots de passe hashés avec PHP password_hash()
- Protection contre les injections SQL avec PDO
- Validation et nettoyage des données d'entrée
- Sessions sécurisées
- Protection CSRF (à implémenter)

## 🚀 Déploiement

### Hébergement Web
1. Téléchargez tous les fichiers sur votre serveur
2. Configurez la base de données MySQL
3. Importez le fichier SQL
4. Modifiez les paramètres de connexion
5. Configurez les permissions des dossiers

### Variables d'environnement
Créez un fichier `.env` pour la production :
```
DB_HOST=votre-serveur-mysql
DB_NAME=korajob_prod
DB_USER=votre-utilisateur
DB_PASS=votre-mot-de-passe
```

## 🐛 Dépannage

### Problèmes courants

#### Erreur de connexion à la base de données
- Vérifiez que MySQL est démarré
- Vérifiez les paramètres dans `config/database.php`
- Assurez-vous que la base de données `korajob` existe

#### Erreur 404 sur les pages
- Vérifiez que le mod_rewrite d'Apache est activé
- Vérifiez la configuration du serveur web

#### Problèmes d'upload de fichiers
- Vérifiez les permissions du dossier `uploads/`
- Vérifiez la configuration PHP pour `upload_max_filesize`

## 📈 Améliorations Futures

- [ ] Système de paiement intégré
- [ ] Application mobile (React Native)
- [ ] Analyse vidéo automatique avec IA
- [ ] Système de géolocalisation avancé
- [ ] Intégration avec les réseaux sociaux
- [ ] API REST complète
- [ ] Système de notifications push
- [ ] Chat en temps réel
- [ ] Système de recommandations

## 🤝 Contribution

Les contributions sont les bienvenues ! Pour contribuer :

1. Fork le projet
2. Créez une branche pour votre fonctionnalité (`git checkout -b feature/AmazingFeature`)
3. Committez vos changements (`git commit -m 'Add some AmazingFeature'`)
4. Push vers la branche (`git push origin feature/AmazingFeature`)
5. Ouvrez une Pull Request

## 📄 Licence

Ce projet est sous licence MIT. Voir le fichier `LICENSE` pour plus de détails.

## 📞 Support

Pour toute question ou problème :
- Email: support@korajob.com
- Issues GitHub: [Créer une issue](https://github.com/votre-username/korajob/issues)

## 🙏 Remerciements

- Bootstrap pour le framework CSS
- Font Awesome pour les icônes
- La communauté PHP et MySQL
- Tous les contributeurs du projet

---

**Développé avec ❤️ pour le football algérien**

