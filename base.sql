PRAGMA foreign_keys = ON;

-- ==========================
-- STRUCTURE (SQLite3)
-- ==========================
--
-- L'application ne gere qu'UN SEUL operateur : le notre.
-- La table operateur ne contient donc qu'une ligne.
--
-- Les autres operateurs ne sont connus que par leurs prefixes, dans la table
-- commission_autres_operateurs. On ne stocke ni leurs comptes ni leurs soldes :
-- on ne retient que ce qu'on leur doit, operation par operation.

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

-- Prefixes des AUTRES operateurs vers lesquels un transfert est autorise,
-- avec le pourcentage de commission qu'on leur reverse sur chaque transfert.
CREATE TABLE commission_autres_operateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    prefixe_autre_operateur VARCHAR(5) NOT NULL UNIQUE,
    pct_commission NUMERIC(5,2) NOT NULL DEFAULT 0,
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
    code_secret VARCHAR(10) NOT NULL,
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

-- commission            : ce qu'on doit reverser a l'autre operateur (0 en interne).
-- telephone_destination : numero du destinataire d'un transfert SORTANT vers un
--                         autre operateur. Ce numero n'existe pas dans
--                         compte_client, donc compte_destination reste NULL.
CREATE TABLE historique_operation (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type_operation_id INTEGER NOT NULL REFERENCES type_operation(id),
    compte_source INTEGER REFERENCES compte_client(id),
    compte_destination INTEGER REFERENCES compte_client(id),
    telephone_destination VARCHAR(20),
    montant NUMERIC(15,2) NOT NULL,
    frais NUMERIC(15,2) DEFAULT 0,
    commission NUMERIC(15,2) DEFAULT 0,
    date_operation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- NOTRE OPERATEUR (un seul)
-- ==========================

INSERT INTO operateur (nom) VALUES ('Telma');

INSERT INTO prefixe_operateur (operateur_id, prefixe) VALUES
(1,'034'),
(1,'037'),
(1,'038');

-- ==========================
-- PREFIXES DES AUTRES OPERATEURS
-- ==========================

INSERT INTO commission_autres_operateurs (prefixe_autre_operateur, pct_commission) VALUES
('032', 2.00),
('033', 1.50),
('031', 2.50);

-- ==========================
-- GERANT
-- ==========================

INSERT INTO gerant_operateur (operateur_id, username, pwd) VALUES
(1,'admin_telma','telma123');

-- ==========================
-- CLIENTS (tous chez nous)
-- ==========================

INSERT INTO client (nom, prenom) VALUES
('Rakoto','Jean'),            -- id 1
('Rasoanaivo','Marie'),       -- id 2
('Rakotondraibe','Sarah'),    -- id 3
('Ramanantsoa','Lucas'),      -- id 4
('Razafindrakoto','Tiana');   -- id 5

-- ==========================
-- COMPTES (uniquement sur nos prefixes 034 / 037 / 038)
-- ==========================

INSERT INTO compte_client (operateur_id, client_id, telephone, code_secret, solde) VALUES
(1, 1, '0340100001', '1111', 250000),
(1, 2, '0340100002', '2222', 80000),
(1, 3, '0387800005', '3333', 120000),
(1, 4, '0378900006', '4444', 300000),
(1, 5, '0342300007', '5555', 45000);

-- ==========================
-- TYPES D'OPERATIONS
-- ==========================

INSERT INTO type_operation (nom) VALUES
('Depot'),
('Retrait'),
('Transfert');

-- ==========================
-- BAREMES DES FRAIS
-- ==========================

-- Depot (gratuit)
INSERT INTO operation_operateur (type_operation_id,montant_min,montant_max,frais) VALUES
(1,0,999999999,0);

-- Retrait
INSERT INTO operation_operateur (type_operation_id,montant_min,montant_max,frais) VALUES
(2,100,1000,50),
(2,1001,2500,100),
(2,2501,10000,200),
(2,10001,50000,500),
(2,50001,100000,1000),
(2,100001,500000,2000);

-- Transfert
INSERT INTO operation_operateur (type_operation_id,montant_min,montant_max,frais) VALUES
(3,100,1000,25),
(3,1001,2500,50),
(3,2501,10000,100),
(3,10001,50000,250),
(3,50001,100000,500),
(3,100001,500000,1000);

-- ==========================
-- HISTORIQUE DES OPERATIONS
-- ==========================

-- Depots : l'argent entre, donc compte_destination.
INSERT INTO historique_operation
(type_operation_id,compte_source,compte_destination,telephone_destination,montant,frais,commission)
VALUES
(1,NULL,1,NULL,50000,0,0),
(1,NULL,2,NULL,25000,0,0),
(1,NULL,4,NULL,100000,0,0);

-- Retraits : l'argent sort, donc compte_source.
INSERT INTO historique_operation
(type_operation_id,compte_source,compte_destination,telephone_destination,montant,frais,commission)
VALUES
(2,1,NULL,NULL,5000,200,0),
(2,3,NULL,NULL,1000,50,0),
(2,4,NULL,NULL,80000,1000,0);

-- Transferts
INSERT INTO historique_operation
(type_operation_id,compte_source,compte_destination,telephone_destination,montant,frais,commission)
VALUES
(3,1,2,NULL,10000,100,0),
(3,4,1,NULL,20000,250,0),
(3,3,5,NULL,2500,50,0);

-- Transferts EXTERNES : compte_destination NULL, numero conserve dans
-- telephone_destination, commission = pct du prefixe applique au montant.
--   10.000 vers 032 a 2%    -> commission 200
--    5.000 vers 033 a 1.5%  -> commission  75
--   30.000 vers 032 a 2%    -> commission 600
--    2.000 vers 031 a 2.5%  -> commission  50
INSERT INTO historique_operation
(type_operation_id,compte_source,compte_destination,telephone_destination,montant,frais,commission)
VALUES
(3,1,NULL,'0321234567',10000,100,200),
(3,2,NULL,'0339876543',5000,100,75),
(3,4,NULL,'0327654321',30000,250,600),
(3,5,NULL,'0311122233',2000,50,50);