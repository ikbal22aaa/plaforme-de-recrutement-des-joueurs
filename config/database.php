<?php
// Configuration de la base de données
// Pour l'hébergement local (XAMPP)
define('DB_HOST', 'localhost');
define('DB_NAME', 'korajob');
define('DB_USER', 'root');
define('DB_PASS', '');

// Pour l'hébergement gratuit (InfinityFree par exemple)
// Décommentez et modifiez ces lignes selon votre hébergeur :
// define('DB_HOST', 'sqlXXX.epizy.com'); // Remplacez XXX par votre numéro
// define('DB_NAME', 'epiz_XXXXXX_korajob'); // Remplacez XXXXXX par votre ID
// define('DB_USER', 'epiz_XXXXXX'); // Remplacez XXXXXX par votre ID
// define('DB_PASS', 'votre_mot_de_passe'); // Votre mot de passe de base de données

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Fonction pour créer les tables si elles n'existent pas
function createTables($pdo) {
    // Table des utilisateurs
    $sql_users = "CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        user_type ENUM('joueur', 'entraineur', 'club', 'admin') NOT NULL,
        status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Table des profils joueurs
    $sql_joueurs = "CREATE TABLE IF NOT EXISTS joueurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        age INT,
        position VARCHAR(50),
        objectif TEXT,
        wilaya VARCHAR(100),
        niveau VARCHAR(50),
        taille DECIMAL(5,2),
        poids DECIMAL(5,2),
        pied_fort ENUM('droit', 'gauche', 'ambidextre'),
        video_url VARCHAR(500),
        description TEXT,
        rating DECIMAL(3,2) DEFAULT 0.00,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Table des profils entraîneurs
    $sql_entraineurs = "CREATE TABLE IF NOT EXISTS entraineurs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nationalite VARCHAR(100),
        langues TEXT,
        experience TEXT,
        anciens_clubs TEXT,
        specialite VARCHAR(100),
        diplomes TEXT,
        rating DECIMAL(3,2) DEFAULT 0.00,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Table des clubs
    $sql_clubs = "CREATE TABLE IF NOT EXISTS clubs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        nom_club VARCHAR(200) NOT NULL,
        ville VARCHAR(100),
        wilaya VARCHAR(100),
        niveau VARCHAR(50),
        description TEXT,
        logo_url VARCHAR(500),
        site_web VARCHAR(200),
        telephone VARCHAR(20),
        email VARCHAR(100),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Table des annonces
    $sql_annonces = "CREATE TABLE IF NOT EXISTS annonces (
        id INT AUTO_INCREMENT PRIMARY KEY,
        club_id INT NOT NULL,
        titre VARCHAR(200) NOT NULL,
        description TEXT NOT NULL,
        position_recherchee VARCHAR(100),
        niveau_requis VARCHAR(50),
        age_min INT,
        age_max INT,
        wilaya VARCHAR(100),
        date_limite DATE,
        status ENUM('active', 'inactive', 'expired') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
    )";
    
    // Table des candidatures
    $sql_candidatures = "CREATE TABLE IF NOT EXISTS candidatures (
        id INT AUTO_INCREMENT PRIMARY KEY,
        joueur_id INT NOT NULL,
        annonce_id INT NOT NULL,
        message TEXT,
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (joueur_id) REFERENCES joueurs(id) ON DELETE CASCADE,
        FOREIGN KEY (annonce_id) REFERENCES annonces(id) ON DELETE CASCADE
    )";
    
    // Table des messages
    $sql_messages = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        sender_id INT NOT NULL,
        receiver_id INT NOT NULL,
        subject VARCHAR(200),
        message TEXT NOT NULL,
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Table des notifications
    $sql_notifications = "CREATE TABLE IF NOT EXISTS notifications (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(200) NOT NULL,
        message TEXT NOT NULL,
        type VARCHAR(50),
        is_read BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Table des évaluations
    $sql_evaluations = "CREATE TABLE IF NOT EXISTS evaluations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        evaluator_id INT NOT NULL,
        evaluated_id INT NOT NULL,
        rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (evaluated_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    
    // Exécution des requêtes
    $pdo->exec($sql_users);
    $pdo->exec($sql_joueurs);
    $pdo->exec($sql_entraineurs);
    $pdo->exec($sql_clubs);
    $pdo->exec($sql_annonces);
    $pdo->exec($sql_candidatures);
    $pdo->exec($sql_messages);
    $pdo->exec($sql_notifications);
    $pdo->exec($sql_evaluations);
    
    // Créer un admin par défaut
    $admin_check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $admin_check->execute();
    $admin_count = $admin_check->fetchColumn();
    
    if ($admin_count == 0) {
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $admin_insert = $pdo->prepare("INSERT INTO users (nom, email, password, user_type, status) VALUES (?, ?, ?, 'admin', 'active')");
        $admin_insert->execute(['Administrateur', 'admin@korajob.com', $admin_password]);
    }
}

// Créer les tables
createTables($pdo);
?>

