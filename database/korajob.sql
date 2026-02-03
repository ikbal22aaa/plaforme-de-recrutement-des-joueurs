-- KoraJob Database Schema
-- Création de la base de données et des tables

CREATE DATABASE IF NOT EXISTS korajob CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE korajob;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('joueur', 'entraineur', 'club', 'admin') NOT NULL,
    status ENUM('active', 'inactive', 'pending') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des profils joueurs
CREATE TABLE IF NOT EXISTS joueurs (
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
);

-- Table des profils entraîneurs
CREATE TABLE IF NOT EXISTS entraineurs (
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
);

-- Table des clubs
CREATE TABLE IF NOT EXISTS clubs (
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
);

-- Table des annonces
CREATE TABLE IF NOT EXISTS annonces (
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
);

-- Table des candidatures
CREATE TABLE IF NOT EXISTS candidatures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    joueur_id INT NOT NULL,
    annonce_id INT NOT NULL,
    message TEXT,
    status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (joueur_id) REFERENCES joueurs(id) ON DELETE CASCADE,
    FOREIGN KEY (annonce_id) REFERENCES annonces(id) ON DELETE CASCADE
);

-- Table des messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des notifications
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50),
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des évaluations
CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluator_id INT NOT NULL,
    evaluated_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluated_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insertion des données de test

-- Admin par défaut
INSERT INTO users (nom, email, password, user_type, status) VALUES 
('Administrateur', 'admin@korajob.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- Utilisateurs de test
INSERT INTO users (nom, email, password, user_type, status) VALUES 
('Ahmed Benali', 'ahmed@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'joueur', 'active'),
('Mohamed Khelil', 'mohamed@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'joueur', 'active'),
('Karim Boudjema', 'karim@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'joueur', 'active'),
('Youssef Mansouri', 'youssef@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'entraineur', 'active'),
('Rachid Belhocine', 'rachid@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'entraineur', 'active'),
('CR Belouizdad', 'crb@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'club', 'active'),
('JS Kabylie', 'jsk@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'club', 'active'),
('MC Alger', 'mca@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'club', 'active');

-- Profils joueurs
INSERT INTO joueurs (user_id, age, position, objectif, wilaya, niveau, taille, poids, pied_fort, description) VALUES 
(2, 22, 'Attaquant', 'Jouer en première division', 'Alger', 'Semi-professionnel', 1.80, 75.5, 'droit', 'Jeune attaquant talentueux avec une excellente technique et une grande vitesse.'),
(3, 25, 'Milieu central', 'Intégrer un grand club', 'Oran', 'Professionnel', 1.75, 70.0, 'droit', 'Milieu de terrain créatif avec une excellente vision du jeu.'),
(4, 20, 'Défenseur central', 'Développer mes compétences', 'Constantine', 'Amateur', 1.85, 80.0, 'gauche', 'Défenseur solide avec un bon jeu de tête et une capacité de relance.');

-- Profils entraîneurs
INSERT INTO entraineurs (user_id, nationalite, langues, experience, anciens_clubs, specialite, diplomes) VALUES 
(5, 'Algérienne', 'Arabe, Français', '15 ans d\'expérience dans la formation des jeunes', 'CR Belouizdad, JS Kabylie', 'Formation des jeunes', 'Diplôme UEFA B, Licence STAPS'),
(6, 'Algérienne', 'Arabe, Français, Anglais', '20 ans d\'expérience', 'MC Alger, USM Alger', 'Tactique', 'Diplôme UEFA A, Master en Sciences du Sport');

-- Profils clubs
INSERT INTO clubs (user_id, nom_club, ville, wilaya, niveau, description, telephone, email) VALUES 
(7, 'CR Belouizdad', 'Alger', 'Alger', 'Professionnel', 'Club historique d\'Alger, multiple champion d\'Algérie', '021 23 45 67', 'contact@crbelouizdad.dz'),
(8, 'JS Kabylie', 'Tizi Ouzou', 'Tizi Ouzou', 'Professionnel', 'Club légendaire de Kabylie, connu pour sa formation', '026 12 34 56', 'contact@jskabylie.dz'),
(9, 'MC Alger', 'Alger', 'Alger', 'Professionnel', 'Club populaire d\'Alger, riche en histoire', '021 98 76 54', 'contact@mcalger.dz');

-- Annonces
INSERT INTO annonces (club_id, titre, description, position_recherchee, niveau_requis, age_min, age_max, wilaya, date_limite) VALUES 
(1, 'Recherche attaquant pour la saison 2024-2025', 'Nous recherchons un attaquant talentueux pour renforcer notre équipe première. Profil recherché : technique, vitesse et finition.', 'Attaquant', 'Professionnel', 18, 28, 'Alger', '2024-12-31'),
(2, 'Recrutement gardien de but', 'Poste de gardien de but disponible dans notre équipe réserve. Expérience requise en division amateur.', 'Gardien de but', 'Amateur', 20, 30, 'Tizi Ouzou', '2024-11-30'),
(3, 'Milieu défensif recherché', 'Nous cherchons un milieu défensif solide pour notre équipe première. Qualités requises : physique, technique et leadership.', 'Milieu défensif', 'Professionnel', 22, 32, 'Alger', '2024-12-15');

-- Candidatures
INSERT INTO candidatures (joueur_id, annonce_id, message, status) VALUES 
(1, 1, 'Bonjour, je suis très intéressé par ce poste. J\'ai une excellente technique et je suis très motivé.', 'pending'),
(2, 3, 'Je pense correspondre parfaitement au profil recherché. Mon expérience en milieu professionnel sera un atout.', 'pending'),
(3, 2, 'Je suis jeune mais très motivé. J\'aimerais avoir l\'opportunité de vous rencontrer.', 'pending');

-- Messages
INSERT INTO messages (sender_id, receiver_id, subject, message) VALUES 
(2, 7, 'Candidature pour le poste d\'attaquant', 'Bonjour, je suis intéressé par le poste d\'attaquant. Pouvons-nous nous rencontrer ?'),
(5, 2, 'Proposition d\'entraînement', 'Bonjour Ahmed, j\'ai vu votre profil et je serais intéressé pour vous entraîner. Contactez-moi si cela vous intéresse.'),
(7, 2, 'Réponse à votre candidature', 'Merci pour votre candidature. Nous vous contacterons bientôt pour un essai.');

-- Notifications
INSERT INTO notifications (user_id, title, message, type) VALUES 
(2, 'Nouvelle candidature', 'Votre candidature a été envoyée avec succès', 'success'),
(2, 'Message reçu', 'Vous avez reçu un nouveau message de CR Belouizdad', 'info'),
(5, 'Nouveau joueur intéressé', 'Ahmed Benali est intéressé par vos services d\'entraînement', 'info');

-- Évaluations
INSERT INTO evaluations (evaluator_id, evaluated_id, rating, comment) VALUES 
(5, 2, 4, 'Très bon joueur avec un excellent potentiel. Technique solide et mentalité positive.'),
(6, 3, 5, 'Joueur exceptionnel, très créatif et intelligent tactiquement.'),
(7, 2, 4, 'Bon profil pour notre équipe. À suivre de près.');

-- Mise à jour des ratings
UPDATE joueurs SET rating = (
    SELECT AVG(rating) FROM evaluations WHERE evaluated_id = joueurs.user_id
) WHERE user_id IN (2, 3, 4);

UPDATE entraineurs SET rating = (
    SELECT AVG(rating) FROM evaluations WHERE evaluated_id = entraineurs.user_id
) WHERE user_id IN (5, 6);

-- Index pour améliorer les performances
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_type ON users(user_type);
CREATE INDEX idx_users_status ON users(status);
CREATE INDEX idx_joueurs_position ON joueurs(position);
CREATE INDEX idx_joueurs_wilaya ON joueurs(wilaya);
CREATE INDEX idx_joueurs_niveau ON joueurs(niveau);
CREATE INDEX idx_entraineurs_specialite ON entraineurs(specialite);
CREATE INDEX idx_clubs_wilaya ON clubs(wilaya);
CREATE INDEX idx_annonces_status ON annonces(status);
CREATE INDEX idx_annonces_position ON annonces(position_recherchee);
CREATE INDEX idx_candidatures_status ON candidatures(status);
CREATE INDEX idx_messages_receiver ON messages(receiver_id);
CREATE INDEX idx_notifications_user ON notifications(user_id);
CREATE INDEX idx_notifications_read ON notifications(is_read);
CREATE INDEX idx_evaluations_evaluated ON evaluations(evaluated_id);

