<?php

namespace App\Models;

use CodeIgniter\Model;

class CommissionAutresOperateursModel extends Model
{
    protected $table = 'commission_autres_operateurs';
    protected $primaryKey = 'id';
    protected $allowedFields = ['prefixe_autre_operateur', 'pct_commission', 'date_creation'];

    /**
     * Tous les prefixes autorises, du plus recent au plus ancien.
     */
    public function tous(): array
    {
        return $this->orderBy('prefixe_autre_operateur', 'ASC')->findAll();
    }

    /**
     * Retourne la ligne dont le prefixe correspond au debut du numero, ou null
     * si le numero n'appartient a aucun autre operateur autorise.
     *
     * La comparaison se fait sur le prefixe stocke (et non sur les 3 premiers
     * caracteres en dur), pour rester correct si un prefixe de longueur
     * differente est ajoute un jour.
     */
    public function pourTelephone(string $telephone): ?array
    {
        $lignes = $this->findAll();

        foreach ($lignes as $ligne) {
            $prefixe = (string) $ligne['prefixe_autre_operateur'];

            if ($prefixe !== '' && str_starts_with($telephone, $prefixe)) {
                return $ligne;
            }
        }

        return null;
    }

    public function parPrefixe(string $prefixe): ?array
    {
        return $this->where('prefixe_autre_operateur', $prefixe)->first();
    }

    /**
     * Commission due a l'autre operateur pour un montant transfere.
     * Arrondie a l'ariary, la table ne stockant pas de centimes utiles.
     */
    public function commissionPour(float $pourcentage, float $montant): float
    {
        return round($montant * $pourcentage / 100);
    }
}
