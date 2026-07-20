<?php

namespace App\Models;

use CodeIgniter\Model;

class CompteClientModel extends Model
{
    protected $table = 'compte_client';
    protected $primaryKey = 'id';
    protected $allowedFields = ['operateur_id', 'client_id', 'telephone', 'code_secret', 'solde'];

    /**
     * Retourne le compte correspondant a un numero de telephone, ou null.
     */
    public function parTelephone(string $telephone): ?array
    {
        return $this->where('telephone', $telephone)->first();
    }

    /**
     * Compte enrichi du nom et du prenom du client (pour l'affichage).
     */
    public function avecClient(int $compteId): ?array
    {
        return $this->db->table('compte_client c')
            ->select('c.*, cl.nom, cl.prenom')
            ->join('client cl', 'cl.id = c.client_id')
            ->where('c.id', $compteId)
            ->get()
            ->getRowArray();
    }

    /**
     * Applique une variation (positive ou negative) au solde d'un compte.
     *
     * La mise a jour est faite en SQL relatif (solde = solde + x) plutot qu'en
     * lisant puis reecrivant la valeur : deux operations simultanees sur le meme
     * compte ne peuvent donc pas s'ecraser l'une l'autre.
     */
    public function ajusterSolde(int $compteId, float $variation): void
    {
        $this->db->table('compte_client')
            ->set('solde', 'solde + ' . $this->db->escape($variation), false)
            ->where('id', $compteId)
            ->update();
    }
}
