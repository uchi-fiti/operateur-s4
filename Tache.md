- Conception de la base de donnee [ok] Fait ensemble
    - Comprehension du sujet
    - Debat sur les colonnes a mettre
    - Creation d'une base de donnee brouillon
    - Correction de la base de donnee brouillon

- Debat des maquettes [ok] Fait ensemble
    - Specification des pages necessaires
    - Separation de l'application en cote client ou operateur
    - Forme generale des pages demandees
    - Details des pages

- Design Fitiavana [ok]
    - Choix de la palette de couleur globale
    - Choix de la police globale
    - Creation du template

- Donnees Noah [ok]
    - Creation de donnees 
    - Push git
    - Configuration du framework


## Cote client (Fitiavana) [ok]
- page connection client
	- input numero de telephone
	- fonction verifier si numero existe
- page code secret
	- input code secret
	- fonction verifier si code secret valide
- page solde
	- afficher le solde
- page depot
	- input montant a deposer
	- fonction depot
- page retrait
	- input montant a retirer
	- fonction depot
- page transferer
	- input numero destinataire
	- input montant a transferer
	- code secret
	- fonction transferer
	- fonction verifier destinataire
- page historique
	- fonction historique (get all historique)

# Cote operateur (NOah) [ok]
- Fonctionnalite Operateur Noah
- Page des prefixes NOAH
    - Liste des prefixes pour l'operateur gere par l'utilisateur connecte
    - Compte relie a ce prefixe
    - Formulaire d'insertion de prefixe :
        - Ne peut pas inserer un prefixe deja existant

- Page operation NOAH
    - Historique des operations
    - Filtre par type d'operation
    - Ajout d'operation (frais , min , max)

- Page detail client NOAH
    - Prefixe et nombre de comptes relies a ce prefixe