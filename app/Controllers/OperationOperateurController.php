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
        $operationId = $this->request->getGet("typeOperation");
        $operations = $operationModel
            ->select('operation_operateur.*, type_operation.nom AS type_nom')
            ->join('type_operation', 'type_operation.id = operation_operateur.type_operation_id');

        if($operationId !== null && $operationId !== ''){
            $operations->where("operation_operateur.type_operation_id", $operationId);
        }
            $result = $operations->orderBy('operation_operateur.id', 'DESC')
            ->findAll();

        return view('operateur/operations', [
            'operations' => $result,
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

    public function edit($id)
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        $model = new OperationOperateurModel();
        $operation = $model->find($id);

        if (!$operation) {
            return redirect()->to('/operateur/operations');
        }

        $typeOperationModel = new TypeOperationModel();
        $typeOperations = $typeOperationModel->findAll();

        return view('operateur/edit_operation', [
            'operation' => $operation,
            'typeOperations' => $typeOperations,
        ]);
    }
    public function update($id)
{
    if ($redirect = $this->ensureLoggedIn()) {
        return $redirect;
    }

    $model = new OperationOperateurModel();

    $operation = $model->find($id);

    if (!$operation) {
        return redirect()->to('/operateur/operations');
    }

    $rules = [
        'type_operation_id' => 'required|integer',
        'montant_min'       => 'required',
        'montant_max'       => 'required',
        'frais'             => 'required'
    ];

    if (!$this->validate($rules)) {
        return redirect()
            ->back()
            ->withInput()
            ->with('errors', $this->validator->getErrors());
    }

    $model->update($id, [
        'type_operation_id' => $this->request->getPost('type_operation_id'),
        'montant_min'       => $this->request->getPost('montant_min'),
        'montant_max'       => $this->request->getPost('montant_max'),
        'frais'             => $this->request->getPost('frais')
    ]);

    return redirect()
        ->to('/operateur/operations')
        ->with('success', 'Barème modifié avec succès.');
}
}
