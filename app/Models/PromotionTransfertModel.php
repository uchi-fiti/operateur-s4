<?php

namespace App\Models;

use CodeIgniter\Model;

class PromotionTransfertModel extends Model
{
    protected $table = 'promotion_transfert';
    protected $primaryKey = 'id';
    protected $allowedFields = ['valeur'];
    public function getPromotion() 
    {
        $ligne = $this->first();

        return $ligne ? (float) $ligne['valeur'] : null;
    }
}