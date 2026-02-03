<?php
// Fichier de test pour vérifier la connexion à la base de données
// À supprimer en production

echo "<h1>Test de connexion KoraJob</h1>";

// Test de connexion à la base de données
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✅ Connexion à la base de données réussie</p>";
    
    // Test des tables
    $tables = ['users', 'joueurs', 'entraineurs', 'clubs', 'annonces', 'candidatures', 'messages', 'notifications', 'evaluations'];
    
    foreach ($tables as $table) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        echo "<p>📊 Table '$table': $count enregistrement(s)</p>";
    }
    
    // Test des utilisateurs
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE user_type = 'admin'");
    $admin_count = $stmt->fetchColumn();
    
    if ($admin_count > 0) {
        echo "<p style='color: green;'>✅ Administrateur trouvé</p>";
        
        // Afficher les informations de l'admin
        $stmt = $pdo->query("SELECT nom, email FROM users WHERE user_type = 'admin' LIMIT 1");
        $admin = $stmt->fetch();
        echo "<p><strong>Admin:</strong> " . htmlspecialchars($admin['nom']) . " (" . htmlspecialchars($admin['email']) . ")</p>";
    } else {
        echo "<p style='color: red;'>❌ Aucun administrateur trouvé</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur de connexion: " . $e->getMessage() . "</p>";
}

// Test des fichiers
echo "<h2>Test des fichiers</h2>";

$required_files = [
    'index.php',
    'login.php',
    'register.php',
    'config/database.php',
    'includes/functions.php',
    'assets/css/style.css',
    'assets/js/main.js'
];

foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "<p style='color: green;'>✅ $file</p>";
    } else {
        echo "<p style='color: red;'>❌ $file manquant</p>";
    }
}

// Test des permissions
echo "<h2>Test des permissions</h2>";

$directories = ['uploads', 'assets/images'];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        if (mkdir($dir, 0755, true)) {
            echo "<p style='color: green;'>✅ Dossier '$dir' créé</p>";
        } else {
            echo "<p style='color: red;'>❌ Impossible de créer le dossier '$dir'</p>";
        }
    } else {
        if (is_writable($dir)) {
            echo "<p style='color: green;'>✅ Dossier '$dir' accessible en écriture</p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Dossier '$dir' non accessible en écriture</p>";
        }
    }
}

// Informations système
echo "<h2>Informations système</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>Serveur:</strong> " . $_SERVER['SERVER_SOFTWARE'] ?? 'Inconnu' . "</p>";
echo "<p><strong>Document Root:</strong> " . $_SERVER['DOCUMENT_ROOT'] ?? 'Inconnu' . "</p>";

// Test des extensions PHP
echo "<h2>Extensions PHP</h2>";
$required_extensions = ['pdo', 'pdo_mysql', 'mbstring', 'openssl'];

foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<p style='color: green;'>✅ Extension '$ext' chargée</p>";
    } else {
        echo "<p style='color: red;'>❌ Extension '$ext' manquante</p>";
    }
}

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ul>";
echo "<li>Si tous les tests sont verts, votre installation est prête</li>";
echo "<li>Connectez-vous avec: admin@korajob.com / admin123</li>";
echo "<li>Supprimez ce fichier après les tests</li>";
echo "</ul>";
?>

