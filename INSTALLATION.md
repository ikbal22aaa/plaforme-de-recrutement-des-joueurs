# 📦 INSTALLATION DE KORAJOB

## 🚀 Guide d'Installation Rapide

### Étape 1 : Installation de XAMPP

1. Téléchargez XAMPP depuis https://www.apachefriends.org/
2. Installez XAMPP sur votre ordinateur
3. Démarrez les services **Apache** et **MySQL**

### Étape 2 : Préparation du Projet

1. Le projet est déjà dans : `D:\xampp\htdocs\Recrutement des joueurs platform`
2. Tous les fichiers sont en place

### Étape 3 : Création de la Base de Données

#### **Méthode 1 : Utilisation de phpMyAdmin (Recommandé)**

1. Ouvrez votre navigateur
2. Accédez à : `http://localhost/phpmyadmin`
3. Cliquez sur "Nouveau" dans le menu de gauche
4. Nom de la base de données : `korajob`
5. Collation : `utf8mb4_unicode_ci`
6. Cliquez sur "Créer"
7. Sélectionnez la base de données `korajob`
8. Cliquez sur l'onglet "Importer"
9. Cliquez sur "Choisir un fichier"
10. Sélectionnez : `D:\xampp\htdocs\Recrutement des joueurs platform\database\korajob.sql`
11. Cliquez sur "Exécuter"

#### **Méthode 2 : Ligne de Commande MySQL**

```bash
# Ouvrez le terminal/invite de commande
cd D:\xampp\mysql\bin

# Connectez-vous à MySQL
mysql -u root -p

# Dans MySQL, exécutez :
CREATE DATABASE korajob CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE korajob;
SOURCE "D:/xampp/htdocs/Recrutement des joueurs platform/database/korajob.sql";
exit;
```

#### **Méthode 3 : Création Automatique**

La base de données se créera automatiquement lors de la première visite du site grâce au fichier `config/database.php` qui contient la fonction `createTables()`.

### Étape 4 : Configuration

Le fichier de configuration est déjà configuré dans `config/database.php` :

```php
DB_HOST = 'localhost'
DB_NAME = 'korajob'
DB_USER = 'root'
DB_PASS = '' (vide par défaut pour XAMPP)
```

### Étape 5 : Accès au Site

1. Assurez-vous que XAMPP est démarré (Apache et MySQL)
2. Ouvrez votre navigateur
3. Accédez à : `http://localhost/Recrutement des joueurs platform/index.php`

## 👥 COMPTES DE TEST

### 🔐 Administrateur
- **Email** : `admin@korajob.com`
- **Mot de passe** : `admin123`

### ⚽ Joueur
- **Email** : `ahmed@test.com`
- **Mot de passe** : `password123`

### 🏃 Entraîneur
- **Email** : `youssef@test.com`
- **Mot de passe** : `password123`

### 🏢 Club
- **Email** : `crb@test.com`
- **Mot de passe** : `password123`

## ✅ VÉRIFICATION DE L'INSTALLATION

### Test de Connexion à la Base de Données

Accédez à : `http://localhost/Recrutement des joueurs platform/test_connection.php`

Vous devriez voir : **"✅ Connexion à la base de données réussie!"**

## 🐛 DÉPANNAGE

### Problème : "Erreur de connexion à la base de données"

**Solution :**
1. Vérifiez que MySQL est démarré dans XAMPP
2. Vérifiez que la base de données `korajob` existe
3. Vérifiez les paramètres dans `config/database.php`

### Problème : "Table 'korajob.users' doesn't exist"

**Solution :**
1. La base de données existe mais les tables ne sont pas créées
2. Importez le fichier `database/korajob.sql` via phpMyAdmin
3. OU visitez simplement le site, les tables se créeront automatiquement

### Problème : Page blanche

**Solution :**
1. Activez l'affichage des erreurs PHP
2. Vérifiez les logs d'erreur dans `C:\xampp\apache\logs\error.log`
3. Vérifiez que tous les fichiers PHP sont présents

### Problème : "Access denied for user 'root'@'localhost'"

**Solution :**
1. Vérifiez le mot de passe MySQL dans `config/database.php`
2. Par défaut XAMPP : pas de mot de passe (champ vide)
3. Si vous avez défini un mot de passe, mettez-le dans `DB_PASS`

## 📂 STRUCTURE DES DOSSIERS

```
Recrutement des joueurs platform/
├── admin/              # Panneau d'administration
├── assets/             # Fichiers CSS, JS, Images
├── config/             # Configuration de la base de données
├── database/           # Fichier SQL ⭐
├── includes/           # Fonctions PHP
├── joueur/            # Dashboard joueur
├── uploads/           # Uploads (vidéos, images)
├── index.php          # Page d'accueil ⭐
├── login.php          # Page de connexion
├── register.php       # Page d'inscription
└── README.md          # Documentation
```

## 🎯 PROCHAINES ÉTAPES

Après l'installation :

1. ✅ Connectez-vous avec le compte admin
2. ✅ Explorez le tableau de bord
3. ✅ Testez les différents types de comptes
4. ✅ Créez vos propres utilisateurs
5. ✅ Personnalisez la plateforme selon vos besoins

## 📞 SUPPORT

En cas de problème :
- Consultez le fichier `README.md`
- Vérifiez les logs d'erreur XAMPP
- Assurez-vous que tous les services sont démarrés

---

**🎉 Félicitations ! Votre plateforme KoraJob est maintenant prête à l'emploi !**





