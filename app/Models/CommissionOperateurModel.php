<?php

namespace App\Models;
use CodeIgniter\Model;

class CommissionOperateurModel extends Model {
    protected $table='commission_operateur';
    protected $primaryKey='id';
    protected $allowedFields=['prefixe_autre_operateur','pct_commission' ];
}