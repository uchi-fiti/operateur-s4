<?php

namespace App\Models;
use CodeIgniter\Model;

class HistoriqueOperationModel extends Model
{
    protected $table = 'historique_operation';
    protected $primaryKey = 'id';
    protected $allowedFields = ['type_operation_id', 'compte_source', 'compte_destination', 'telephone_destination', 'montant', 'frais', 'commission', 'date_operation'];

    /**
     * Notre revenu total : la somme des frais encaisses.
     *
     * La commission n'en fait pas partie : elle est payee par le client mais
     * reversee integralement a l'autre operateur, elle ne fait que transiter.
     */
    public function revenuTotal(): float
    {
        $ligne = $this->db->table('historique_operation')
            ->selectSum('frais', 'total')
            ->get()
            ->getRowArray();

        return (float) ($ligne['total'] ?? 0);
    }

    /**
     * Notre revenu ventile par type d'operation (pour le camembert).
     * Retourne [['libelle' => 'Depot', 'revenus' => 0.0], ...]
     */
    public function revenusParType(): array
    {
        $lignes = $this->db->table('historique_operation h')
            ->select('t.nom AS libelle, SUM(h.frais) AS revenus')
            ->join('type_operation t', 't.id = h.type_operation_id')
            ->groupBy('t.id, t.nom')
            ->orderBy('t.id', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn ($ligne) => [
            'libelle' => $ligne['libelle'],
            'revenus' => (float) $ligne['revenus'],
        ], $lignes);
    }

    /**
     * Gains des AUTRES operateurs, ventiles par prefixe : ce qu'ils encaissent
     * grace aux commissions sur nos transferts sortants.
     *
     * Le rattachement se fait par jointure sur le prefixe plutot que par
     * SUBSTR(...,1,3), pour rester juste si un prefixe d'une autre longueur est
     * ajoute.
     */
    public function gainsAutresOperateurs(): array
    {
        $lignes = $this->db->table('historique_operation h')
            ->select('a.prefixe_autre_operateur AS prefixe, SUM(h.commission) AS gains')
            ->join(
                'commission_autres_operateurs a',
                "h.telephone_destination LIKE a.prefixe_autre_operateur || '%'",
                'inner'
            )
            ->where('h.commission >', 0)
            ->groupBy('a.prefixe_autre_operateur')
            ->orderBy('a.prefixe_autre_operateur', 'ASC')
            ->get()
            ->getResultArray();

        return array_map(static fn ($ligne) => [
            'prefixe' => $ligne['prefixe'],
            'gains'   => (float) $ligne['gains'],
        ], $lignes);
    }

    /**
     * Situation des montants a envoyer a chaque autre operateur : pour chaque
     * prefixe, la somme des montants transferes plus les commissions dues.
     *
     * Les prefixes sans aucun transfert sortant apparaissent quand meme, a zero,
     * pour que la page liste tous les operateurs partenaires.
     */
    public function montantsAEnvoyer(): array
    {
        $sql = "SELECT a.prefixe_autre_operateur AS prefixe,
                       a.pct_commission          AS pct_commission,
                       COALESCE(SUM(h.montant), 0)                    AS montants,
                       COALESCE(SUM(h.commission), 0)                 AS commissions,
                       COALESCE(SUM(h.montant + h.commission), 0)     AS total,
                       COUNT(h.id)                                    AS nombre
                FROM commission_autres_operateurs a
                LEFT JOIN historique_operation h
                       ON h.telephone_destination LIKE a.prefixe_autre_operateur || '%'
                GROUP BY a.id, a.prefixe_autre_operateur, a.pct_commission
                ORDER BY a.prefixe_autre_operateur ASC";

        return $this->db->query($sql)->getResultArray();
    }

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
            ->select('h.id, h.montant, h.frais, h.commission, h.date_operation, h.compte_source, h.compte_destination')
            ->select('t.nom AS type_nom')
            ->select('src.telephone AS telephone_source')
            // Transfert interne : le numero vient du compte destinataire.
            // Transfert externe : le compte n'existe pas chez nous, le numero a
            // ete conserve tel quel dans historique_operation.
            ->select('COALESCE(dst.telephone, h.telephone_destination) AS telephone_destination', false)
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
