<?php

namespace App\Models;
use CodeIgniter\Model;

class CompteClientModel extends Model {
    protected $table='compte_client';
    protected $primaryKey='id';
    protected $allowedFields=['operateur_id','client_id','telephone','code_secret','solde'];
}