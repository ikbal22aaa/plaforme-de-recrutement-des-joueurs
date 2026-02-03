<?php
/**
 * Gestionnaire sécurisé pour l'upload de vidéos
 */

// Configuration des uploads
$allowed_video_types = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mkv'];
$max_file_size = 100 * 1024 * 1024; // 100MB
$upload_dir = '../uploads/videos/';
$thumbnail_dir = '../uploads/thumbnails/';

/**
 * Valide le fichier vidéo uploadé
 */
function validateVideoFile($file) {
    global $allowed_video_types, $max_file_size;
    
    $errors = [];
    
    // Vérifier les erreurs d'upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Erreur lors de l\'upload du fichier';
        return $errors;
    }
    
    // Vérifier la taille
    if ($file['size'] > $max_file_size) {
        $errors[] = 'Le fichier est trop volumineux (max 100MB)';
    }
    
    // Vérifier le type MIME
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    $allowed_mimes = [
        'video/mp4',
        'video/avi',
        'video/quicktime',
        'video/x-ms-wmv',
        'video/x-flv',
        'video/webm',
        'video/x-matroska'
    ];
    
    if (!in_array($mime_type, $allowed_mimes)) {
        $errors[] = 'Type de fichier non autorisé. Formats acceptés: ' . implode(', ', $allowed_video_types);
    }
    
    // Vérifier l'extension
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($file_extension, $allowed_video_types)) {
        $errors[] = 'Extension de fichier non autorisée';
    }
    
    return $errors;
}

/**
 * Génère un nom de fichier unique et sécurisé
 */
function generateUniqueFilename($original_name, $upload_dir) {
    $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $basename = pathinfo($original_name, PATHINFO_FILENAME);
    $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
    
    do {
        $filename = $basename . '_' . uniqid() . '.' . $extension;
        $filepath = $upload_dir . $filename;
    } while (file_exists($filepath));
    
    return $filename;
}

/**
 * Upload un fichier vidéo
 */
function uploadVideoFile($file, $upload_dir) {
    global $max_file_size;
    
    // Créer le dossier s'il n'existe pas
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Valider le fichier
    $errors = validateVideoFile($file);
    if (!empty($errors)) {
        return ['success' => false, 'errors' => $errors];
    }
    
    // Générer un nom de fichier unique
    $filename = generateUniqueFilename($file['name'], $upload_dir);
    $filepath = $upload_dir . $filename;
    
    // Déplacer le fichier
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Vérifier que le fichier a bien été déplacé
        if (file_exists($filepath)) {
            return [
                'success' => true,
                'filename' => $filename,
                'filepath' => $filepath,
                'size' => filesize($filepath)
            ];
        } else {
            return ['success' => false, 'errors' => ['Erreur lors de l\'enregistrement du fichier']];
        }
    } else {
        return ['success' => false, 'errors' => ['Impossible de déplacer le fichier uploadé']];
    }
}

/**
 * Génère une miniature pour la vidéo (optionnel)
 */
function generateThumbnail($video_path, $thumbnail_dir, $filename) {
    // Cette fonction nécessite ffmpeg pour fonctionner
    // Pour l'instant, on retourne null
    return null;
}

/**
 * Nettoie un ancien fichier vidéo
 */
function deleteVideoFile($filename, $upload_dir) {
    $filepath = $upload_dir . $filename;
    if (file_exists($filepath)) {
        return unlink($filepath);
    }
    return true;
}

/**
 * Formate la taille du fichier en format lisible
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}
?>

