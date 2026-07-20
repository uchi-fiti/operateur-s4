<?php

namespace App\Models;
use CodeIgniter\Model;

class HistoriqueOperationModel extends Model
{
    protected $table = 'historique_operation';
    protected $primaryKey = 'id';


    protected $allowedFields = ['type_operation_id', 'compte_source', 'compte_destination', 'montant', 'frais', 'date_operation', 'commission'];



    /**
     * Toutes les operations touchant un compte, qu'il soit source ou destination,
     * de la plus recente a la plus ancienne.
     *
     * Les jointures ramenent le nom du type et les coordonnees des deux comptes
     * afin que la vue puisse composer ses phrases sans requete supplementaire.
     */
    /**
     * Revenus (frais encaisses) par jour pour un operateur, sur les $jours
     * derniers jours, jour courant inclus.
     *
     * Les frais sont a la charge de celui qui declenche l'operation : le compte
     * destination pour un depot (compte_source est NULL), le compte source pour
     * un retrait ou un transfert. COALESCE ramene donc, dans tous les cas, le
     * compte qui a paye les frais, ce qui permet de rattacher l'operation a son
     * operateur via compte_client.operateur_id.
     *
     * Retourne un tableau ['AAAA-MM-JJ' => revenus]. Les journees sans operation
     * sont absentes : c'est a l'appelant de les completer a zero.
     */
    public function revenusParJour(int $operateurId, int $jours = 7): array
    {
        $depuis = date('Y-m-d', strtotime('-' . ($jours - 1) . ' days'));

        $sql = 'SELECT DATE(h.date_operation) AS jour, SUM(h.frais) AS revenus
                FROM historique_operation h
                JOIN compte_client c
                  ON c.id = COALESCE(h.compte_source, h.compte_destination)
                WHERE c.operateur_id = ?
                  AND DATE(h.date_operation) >= ?
                GROUP BY DATE(h.date_operation)
                ORDER BY jour ASC';

        $lignes = $this->db->query($sql, [$operateurId, $depuis])->getResultArray();

        $revenus = [];

        foreach ($lignes as $ligne) {
            $revenus[$ligne['jour']] = (float) $ligne['revenus'];
        }

        return $revenus;
    }

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
