CREATE DATABASE IF NOT EXISTS libraGuard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE libraGuard;

CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role_id INT NOT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME,
    FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS livres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    auteur VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL UNIQUE,
    date_publication DATE,
    description TEXT,
    statut ENUM('disponible', 'emprunté', 'en réparation') DEFAULT 'disponible',
    photo_url VARCHAR(255),
    date_ajout DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS emprunts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    livre_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_emprunt DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_retour_prevue DATE NOT NULL,
    date_retour_effective DATETIME,
    statut ENUM('en cours', 'retourné', 'en retard') DEFAULT 'en cours',
    FOREIGN KEY (livre_id) REFERENCES livres(id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (nom, description) VALUES 
('admin', 'Administrateur avec accès complet'),
('utilisateur', 'Utilisateur standard du système');

-- Insertion d'un administrateur par défaut (mot de passe: admin123)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role_id) VALUES 
('Admin', 'System', 'admin@libraguard.com', '$2y$10$tFp6FRrp0a4eFsMcrkixDOIBtNmT/mIdvSVuoN.eI.Q0ebvM.i1PG', 1);

INSERT INTO livres (titre, auteur, isbn, date_publication, description, statut) VALUES
('Développement Web mobile avec HTML, CSS et JavaScript Pour les Nuls', 'William HARREL', '9782412051374', '2023-11-09', 'Un livre indispensable à tous les concepteurs ou développeurs de sites Web pour iPhone, iPad, smartphones et tablettes !', 'disponible'),
('PHP et MySQL pour les Nuls', 'Janet VALADE', '9782412051356', '2023-11-14', 'Le livre best-seller sur PHP & MySQL !', 'disponible'); 