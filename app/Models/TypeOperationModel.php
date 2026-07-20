<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeOperationModel extends Model
{
    protected $table = 'type_operation';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nom'];

    /**
     * Identifiant d'un type d'operation a partir de son nom ('Depot', 'Retrait',
     * 'Transfert'). Retourne null si le type n'existe pas en base.
     */
    public function idParNom(string $nom): ?int
    {
        $ligne = $this->where('nom', $nom)->first();

        return $ligne ? (int) $ligne['id'] : null;
    }
}