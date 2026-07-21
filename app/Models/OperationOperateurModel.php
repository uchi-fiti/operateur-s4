<?php

namespace App\Models;

use CodeIgniter\Model;

class OperationOperateurModel extends Model
{
    protected $table = 'operation_operateur';
    protected $primaryKey = 'id';
    protected $allowedFields = ['type_operation_id', 'montant_min', 'montant_max', 'frais'];

    /**
     * Frais applicables a un montant pour un type d'operation donne.
     *
     * Retourne null si le montant ne tombe dans aucun intervalle du bareme :
     * l'operation doit alors etre refusee, pas facturee a zero.
     */
    public function fraisPour(int $typeOperationId, float $montant): ?float
    {
        $ligne = $this->where('type_operation_id', $typeOperationId)
            ->where('montant_min <=', $montant)
            ->where('montant_max >=', $montant)
            ->orderBy('montant_min', 'ASC')
            ->first();

        return $ligne ? (float) $ligne['frais'] : null;
    }

    /**
     * Bornes du bareme d'un type d'operation, pour composer un message d'erreur
     * lisible quand le montant saisi est hors intervalle.
     */
    public function bornes(int $typeOperationId): ?array
    {
        $ligne = $this->db->table('operation_operateur')
            ->select('MIN(montant_min) AS minimum, MAX(montant_max) AS maximum')
            ->where('type_operation_id', $typeOperationId)
            ->get()
            ->getRowArray();

        if (! $ligne || $ligne['minimum'] === null) {
            return null;
        }

        return [
            'minimum' => (float) $ligne['minimum'],
            'maximum' => (float) $ligne['maximum'],
        ];
    }
    /**
     * Cherche une tranche du meme type qui recouvre l'intervalle [min, max].
     *
     * Deux intervalles se chevauchent des que chacun commence avant la fin de
     * l'autre. Un chevauchement rendrait fraisPour() ambigu : il prendrait
     * silencieusement la tranche au montant_min le plus bas.
     *
     * $ignorerId permet a une modification de ne pas se comparer a elle-meme.
     */
    public function chevauchement(int $typeOperationId, float $min, float $max, ?int $ignorerId = null): ?array
    {
        $requete = $this->where('type_operation_id', $typeOperationId)
            ->where('montant_min <=', $max)
            ->where('montant_max >=', $min);

        if ($ignorerId !== null) {
            $requete->where('id !=', $ignorerId);
        }

        return $requete->orderBy('montant_min', 'ASC')->first();
    }

    public function getGainsVenantDeFrais(){
        return $this->selectSum("frais", "fraisTotaux");
    }
}
