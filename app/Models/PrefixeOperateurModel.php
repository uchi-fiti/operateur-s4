<?php

namespace App\Models;
use CodeIgniter\Model;

class PrefixeOperateurModel extends Model {
    protected $table='prefixe_operateur';
    protected $primaryKey='id';
    protected $allowedFields=['operateur_id','prefixe'];
}