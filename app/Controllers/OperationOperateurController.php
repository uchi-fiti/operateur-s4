<?php

namespace App\Controllers;
use App\Models\OperationOperateurModel;
use App\Models\TypeOperationModel;

class OperationOperateurController extends BaseController
{
    private function ensureLoggedIn()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        return null;
    }

    public function index()
    {
        return redirect()->to('/operateur/operations');
    }
    public function new()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $operationModel = new OperationOperateurModel();
        $typeOperationModel = new TypeOperationModel();

        $operations = $operationModel
            ->select('operation_operateur.*, type_operation.nom AS type_nom')
            ->join('type_operation', 'type_operation.id = operation_operateur.type_operation_id')
            ->orderBy('operation_operateur.id', 'DESC')
            ->findAll();

        return view('operateur/operations', [
            'operations' => $operations,
            'typeOperations' => $typeOperationModel->findAll(),
        ]);
    }
    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $model = new OperationOperateurModel();
        $data = $this->request->getPost();
        $model->insert($data);
        return redirect()->to('/operateur/operations');
    }

    public function modify()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $model = new OperationOperateurModel();
        $id = $this->request->getGet("id");
        $modifiedObject = $model->find($id);
        return view('operateur/operations', ["operation" => $modifiedObject]);
    }

}
