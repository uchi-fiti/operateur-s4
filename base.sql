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