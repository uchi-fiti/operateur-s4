<?php

namespace App\Models;
use CodeIgniter\Model;

class GerantOperateurModel extends Model {
    protected $table='gerant_operateur';
    protected $primaryKey='id';
    protected $allowedFields=['operateur_id','username','pwd'];
}