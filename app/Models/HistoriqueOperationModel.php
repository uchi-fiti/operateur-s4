<?php

namespace App\Models;
use CodeIgniter\Model;

class HistoriqueOperationModel extends Model
{
    protected $table = 'historique_operation';
    protected $primaryKey = 'id';
    protected $allowedFields = ['type_operation_id', 'compte_source', 'compte_destination', 'montant', 'frais', 'date_operation'];

    /**
     * Toutes les operations touchant un compte, qu'il soit source ou destination,
     * de la plus recente a la plus ancienne.
     *
     * Les jointures ramenent le nom du type et les coordonnees des deux comptes
     * afin que la vue puisse composer ses phrases sans requete supplementaire.
     */
    public function pourCompte(int $compteId): array
    {
        return $this->db->table('historique_operation h')
            ->select('h.id, h.montant, h.frais, h.date_operation, h.compte_source, h.compte_destination')
            ->select('t.nom AS type_nom')
            ->select('src.telephone AS telephone_source')
            ->select('dst.telephone AS telephone_destination')
            ->join('type_operation t', 't.id = h.type_operation_id')
            ->join('compte_client src', 'src.id = h.compte_source', 'left')
            ->join('compte_client dst', 'dst.id = h.compte_destination', 'left')
            ->groupStart()
                ->where('h.compte_source', $compteId)
                ->orWhere('h.compte_destination', $compteId)
            ->groupEnd()
            ->orderBy('h.date_operation', 'DESC')
            ->orderBy('h.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
