PRAGMA foreign_keys = ON;

-- ==========================
-- STRUCTURE (SQLite3)
-- ==========================

CREATE TABLE operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100) NOT NULL
);

CREATE TABLE prefixe_operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operateur_id INTEGER NOT NULL REFERENCES operateur(id),
    prefixe VARCHAR(5) NOT NULL UNIQUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE gerant_operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operateur_id INTEGER NOT NULL REFERENCES operateur(id),
    username VARCHAR(50) UNIQUE NOT NULL,
    pwd VARCHAR(100) NOT NULL
);

CREATE TABLE client (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(100),
    prenom VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE compte_client (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    operateur_id INTEGER NOT NULL REFERENCES operateur(id),
    client_id INTEGER NOT NULL REFERENCES client(id),
    telephone VARCHAR(20) UNIQUE NOT NULL,
    code_secret VARCHAR(10) UNIQUE NOT NULL,
    solde NUMERIC(15,2) DEFAULT 0
);

CREATE TABLE type_operation (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE operation_operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL REFERENCES type_operation(id),
    montant_min NUMERIC(15,2) NOT NULL,
    montant_max NUMERIC(15,2) NOT NULL,
    frais NUMERIC(15,2) NOT NULL
);

CREATE TABLE historique_operation (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL REFERENCES type_operation(id),
    compte_source INTEGER REFERENCES compte_client(id),
    compte_destination INTEGER REFERENCES compte_client(id),
    montant NUMERIC(15,2) NOT NULL,
    frais NUMERIC(15,2) DEFAULT 0,
    date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- OPERATEURS
-- ==========================

INSERT INTO operateur (nom) VALUES
('Telma'),
('Airtel'),
('Orange Madagascar');

-- ==========================
-- PREFIXES
-- ==========================

INSERT INTO prefixe_operateur (operateur_id, prefixe) VALUES
(1,'034'),
(1,'038'),
(1,'037'),
(2,'033'),
(3,'032');

-- ==========================
-- GERANTS
-- ==========================

INSERT INTO gerant_operateur (operateur_id, username, pwd) VALUES
(1,'admin_telma','telma123'),
(2,'admin_airtel','airtel123'),
(3,'admin_orange','orange123');

-- ==========================
-- CLIENTS
-- ==========================

INSERT INTO client (nom, prenom) VALUES
('Jean','Rakoto'),
('Marie','Rasoanaivo'),
('Paul','Randria'),
('Naina','Andriam'),
('Sarah','Rakotondraibe'),
('Lucas','Ramanantsoa'),
('Tiana','Razafindrakoto'),
('Mickael','Andrianina');

-- ==========================
-- COMPTES
-- ==========================

INSERT INTO compte_client (operateur_id, client_id, telephone, code_secret, solde) VALUES
(1, 1, '0340100001', '1111', 250000),
(1, 2, '0340100002', '2222', 80000),
(2, 3, '0331200003', '3333', 150000),
(3, 4, '0324500004', '4444', 50000),
(1, 5, '0387800005', '5555', 120000),
(3, 6, '0378900006', '6666', 300000),
(1, 7, '0342300007', '7777', 45000),
(2, 8, '0334500008', '8888', 98000);

-- ==========================
-- TYPES D'OPERATIONS
-- ==========================

INSERT INTO type_operation (id, nom) VALUES
(1, 'Depot'),
(2, 'Retrait'),
(3, 'Transfert');

-- ==========================
-- BAREMES DES FRAIS
-- ==========================

-- Dépôt (gratuit)
INSERT INTO operation_operateur (type_operation_id, montant_min, montant_max, frais) VALUES
(1,0,999999999,0);

-- Retrait
INSERT INTO operation_operateur (type_operation_id, montant_min, montant_max, frais) VALUES
(2,100,1000,50),
(2,1001,2500,100),
(2,2501,10000,200),
(2,10001,50000,500),
(2,50001,100000,1000),
(2,100001,500000,2000);

-- Transfert
INSERT INTO operation_operateur (type_operation_id, montant_min, montant_max, frais) VALUES
(3,100,1000,25),
(3,1001,2500,50),
(3,2501,10000,100),
(3,10001,50000,250),
(3,50001,100000,500),
(3,100001,500000,1000);

-- ==========================
-- HISTORIQUE DES OPERATIONS
-- ==========================

-- Dépôts
INSERT INTO historique_operation (type_operation_id, compte_source, compte_destination, montant, frais) VALUES
(1,NULL,1,50000,0),
(1,NULL,2,25000,0),
(1,NULL,6,100000,0);

-- Retraits
INSERT INTO historique_operation (type_operation_id, compte_source, compte_destination, montant, frais) VALUES
(2,1,NULL,5000,200),
(2,3,NULL,30000,500),
(2,5,NULL,1000,50),
(2,6,NULL,80000,1000);

-- Transferts
INSERT INTO historique_operation (type_operation_id, compte_source, compte_destination, montant, frais) VALUES
(3,1,2,10000,100),
(3,2,4,5000,100),
(3,6,1,20000,250),
(3,3,5,15000,250),
(3,5,8,2500,50),
(3,8,7,7000,100);