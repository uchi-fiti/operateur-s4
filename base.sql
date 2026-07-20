CREATE TABLE operateur (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(100) NOT NULL
);

CREATE TABLE prefixe_operateur (
    id SERIAL PRIMARY KEY,
    operateur_id INTEGER NOT NULL REFERENCES operateur(id),
    prefixe VARCHAR(5) NOT NULL UNIQUE
);
CREATE TABLE gerant_operateur (
    id SERIAL PRIMARY KEY,
    operateur_id INTEGER NOT NULL REFERENCES operateur(id),
    username VARCHAR(50) UNIQUE NOT NULL,
    pwd VARCHAR(100) NOT NULL
);
CREATE TABLE client (
    id SERIAL PRIMARY KEY,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE compte_client (
    id SERIAL PRIMARY KEY,
    client_id INTEGER NOT NULL REFERENCES client(id),
    code_secret VARCHAR(10) UNIQUE NOT NULL,
    solde NUMERIC(15,2) DEFAULT 0
);

CREATE TABLE type_operation (
    id SERIAL PRIMARY KEY,
    nom VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE operation_operateur (
    id SERIAL PRIMARY KEY,
    type_operation_id INTEGER NOT NULL REFERENCES type_operation(id),

    montant_min NUMERIC(15,2) NOT NULL,
    montant_max NUMERIC(15,2) NOT NULL,

    frais NUMERIC(15,2) NOT NULL
);

CREATE TABLE historique_operation (
    id SERIAL PRIMARY KEY,

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

INSERT INTO client (telephone, nom) VALUES
('0340100001','Jean Rakoto'),
('0340100002','Marie Rasoanaivo'),
('0331200003','Paul Randria'),
('0324500004','Naina Andriam'),
('0387800005','Sarah Rakotondraibe'),
('0378900006','Lucas Ramanantsoa'),
('0342300007','Tiana Razafindrakoto'),
('0334500008','Mickael Andrianina');

-- ==========================
-- COMPTES
-- ==========================

INSERT INTO compte_client (client_id, code_secret, solde) VALUES
(1,'1111',250000),
(2,'2222',80000),
(3,'3333',150000),
(4,'4444',50000),
(5,'5555',120000),
(6,'6666',300000),
(7,'7777',45000),
(8,'8888',98000);

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

-- Dépôt (gratuit)

INSERT INTO operation_operateur
(type_operation_id,montant_min,montant_max,frais)
VALUES
(1,0,999999999,0);

-- Retrait

INSERT INTO operation_operateur
(type_operation_id,montant_min,montant_max,frais)
VALUES
(2,100,1000,50),
(2,1001,2500,100),
(2,2501,10000,200),
(2,10001,50000,500),
(2,50001,100000,1000),
(2,100001,500000,2000);

-- Transfert

INSERT INTO operation_operateur
(type_operation_id,montant_min,montant_max,frais)
VALUES
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

INSERT INTO historique_operation
(type_operation_id,compte_source,compte_destination,montant,frais)
VALUES
(1,NULL,1,50000,0),
(1,NULL,2,25000,0),
(1,NULL,6,100000,0);

-- Retraits

INSERT INTO historique_operation
(type_operation_id,compte_source,compte_destination,montant,frais)
VALUES
(2,1,NULL,5000,200),
(2,3,NULL,30000,500),
(2,5,NULL,1000,50),
(2,6,NULL,80000,1000);

-- Transferts

INSERT INTO historique_operation
(type_operation_id,compte_source,compte_destination,montant,frais)
VALUES
(3,1,2,10000,100),
(3,2,4,5000,100),
(3,6,1,20000,250),
(3,3,5,15000,250),
(3,5,8,2500,50),
(3,8,7,7000,100);