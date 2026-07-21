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

    /**
     * Regles communes a la creation et a la modification d'un bareme.
     *
     * Retourne la liste des erreurs (vide si tout est valide). Les controles de
     * coherence entre champs sont faits en PHP : le validateur de CodeIgniter
     * compare un champ a une valeur, pas deux champs entre eux.
     */
    private function validerBareme(?int $ignorerId = null): array
    {
        $regles = [
            'type_operation_id' => 'required|integer|is_not_unique[type_operation.id]',
            'montant_min'       => 'required|numeric|greater_than_equal_to[0]',
            'montant_max'       => 'required|numeric|greater_than_equal_to[0]',
            'frais'             => 'required|numeric|greater_than_equal_to[0]',
        ];

        if (! $this->validate($regles)) {
            return $this->validator->getErrors();
        }

        $typeId = (int) $this->request->getPost('type_operation_id');
        $min    = (float) $this->request->getPost('montant_min');
        $max    = (float) $this->request->getPost('montant_max');

        if ($min >= $max) {
            return ['montant_min' => 'Le montant minimum doit être strictement inférieur au montant maximum.'];
        }

        $conflit = (new OperationOperateurModel())->chevauchement($typeId, $min, $max, $ignorerId);

        if ($conflit !== null) {
            return ['montant_min' => 'Cette tranche chevauche une tranche existante ('
                . number_format((float) $conflit['montant_min'], 0, ',', ' ') . ' à '
                . number_format((float) $conflit['montant_max'], 0, ',', ' ') . ' Ar).'];
        }

        return [];
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
            'titre'          => 'Opérations',
            'sousTitre'      => "Grille des frais appliqués selon le type d'opération.",
            'actif'          => 'operations',
            'operations'     => $result,
            'typeOperations' => $typeOperationModel->findAll(),
            // Permet au filtre de rester sur le type choisi apres soumission.
            'typeChoisi'     => $operationId,
        ]);
    }
    public function create()
    {
        if ($redirect = $this->ensureLoggedIn()) {
            return $redirect;
        }

        if ($erreurs = $this->validerBareme()) {
            return redirect()->to('/operateur/operations')->withInput()->with('errors', $erreurs);
        }

        // Champs listes explicitement plutot que getPost() en bloc : le contenu
        // insere ne depend pas de ce que le formulaire envoie.
        (new OperationOperateurModel())->insert([
            'type_operation_id' => $this->request->getPost('type_operation_id'),
            'montant_min'       => $this->request->getPost('montant_min'),
            'montant_max'       => $this->request->getPost('montant_max'),
            'frais'             => $this->request->getPost('frais'),
        ]);

        return redirect()->to('/operateur/operations')
            ->with('success', 'Barème ajouté avec succès.');
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
            'titre'          => 'Modifier un barème',
            'sousTitre'      => "Modifier les informations d'un barème de frais.",
            'actif'          => 'operations',
            'operation'      => $operation,
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

    // La tranche modifiee ne doit pas se comparer a elle-meme.
    if ($erreurs = $this->validerBareme((int) $id)) {
        return redirect()
            ->to('/operateur/operations/' . $id . '/edit')
            ->withInput()
            ->with('errors', $erreurs);
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
