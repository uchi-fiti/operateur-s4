<?php

namespace App\Models;
use CodeIgniter\Model;

class HistoriqueOperationModel extends Model {
    protected $table='historique_operation';
    protected $primaryKey='id';
    protected $allowedFields=['type_operation_id','compte_source','compte_destination','montant','frais','date_operation'];
}