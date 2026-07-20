<?php

namespace App\Models;
use CodeIgniter\Model;

class OperationOperateurModel extends Model {
    protected $table='operation_operateur';
    protected $primaryKey='id';
    protected $allowedFields=['type_operation_id','montant_min','montant_max','frais'];
}