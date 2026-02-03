<?php
// Test simple pour vérifier que PHP fonctionne
echo "<h1>Test KoraJob</h1>";
echo "<p>✅ PHP fonctionne correctement</p>";

// Test de connexion à la base de données
try {
    require_once 'config/database.php';
    echo "<p>✅ Connexion à la base de données réussie</p>";
    
    // Test simple
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    $count = $stmt->fetchColumn();
    echo "<p>📊 Nombre d'utilisateurs: $count</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Erreur: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>Instructions pour lancer le projet:</h2>";
echo "<ol>";
echo "<li><strong>Avec XAMPP:</strong> Placez le dossier dans htdocs et accédez à http://localhost/nom-du-dossier</li>";
echo "<li><strong>Avec PHP Runner:</strong> Clic droit sur index.php → 'Run with PHP'</li>";
echo "<li><strong>Avec serveur intégré:</strong> php -S localhost:8000</li>";
echo "</ol>";

echo "<p><a href='index.php' class='btn btn-primary'>Voir le site KoraJob</a></p>";
?>
