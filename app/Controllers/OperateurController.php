<?php

namespace App\Controllers;

use App\Models\CommissionAutresOperateursModel;
use App\Models\GerantOperateurModel;
use App\Models\HistoriqueOperationModel;
use App\Models\PrefixeOperateurModel;
use App\Models\OperateurModel;


class OperateurController extends BaseController
{
    public function login()
    {
        return view('operateur/login');
    }

    public function authenticate()
    {
        $username = (string) $this->request->getPost('utilisateur');
        $password = (string) $this->request->getPost('motdepasse');

        $gerantModel = new GerantOperateurModel();
        $gerant = $gerantModel
            ->where('username', $username)
            ->where('pwd', $password)
            ->first();

        if ($gerant === null) {
            return redirect()
                ->to('/operateur/login')
                ->with('error', 'Nom d\'utilisateur ou mot de passe incorrect.');
        }

        session()->set([
            'operateur_gerant_id' => $gerant['id'],
            'operateur_id' => $gerant['operateur_id'],
            'operateur_username' => $gerant['username'],
            'operateur_logged_in' => true,
        ]);

        return redirect()->to('/operateur/dashboard');
    }

    public function dashboard()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        $historiqueModel = new HistoriqueOperationModel();

        $gainsAutres = $historiqueModel->gainsAutresOperateurs();

        return view('operateur/dashboard', [
            'titre'           => 'Tableau de bord',
            'sousTitre'       => 'Situation des gains via les différents frais.',
            'actif'           => 'dashboard',
            'revenuTotal'     => $historiqueModel->revenuTotal(),
            'revenusParType'  => $historiqueModel->revenusParType(),
            'gainsAutres'     => $gainsAutres,
            'gainsAutresTotal' => array_sum(array_column($gainsAutres, 'gains')),
        ]);
    }

    // =====================================================================
    // Prefixes des autres operateurs
    // =====================================================================

    public function prefixesAutres()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        $commissionModel = new CommissionAutresOperateursModel();

        return view('operateur/prefixes-autres', [
            'titre'     => 'Préfixes des autres opérateurs',
            'sousTitre' => 'Préfixes vers lesquels vos clients peuvent transférer, et commission reversée.',
            'actif'     => 'prefixes-autres',
            'prefixes'  => $commissionModel->tous(),
        ]);
    }

    public function showAjoutPrefixeAutre()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        return view('operateur/prefixe-autre-ajout', [
            'titre'     => 'Ajouter un préfixe partenaire',
            'sousTitre' => 'Autoriser les transferts vers un autre opérateur.',
            'actif'     => 'prefixes-autres',
        ]);
    }

    public function addPrefixeAutre()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        $prefixe = trim((string) $this->request->getPost('prefixe'));
        $pct     = $this->request->getPost('pct_commission');

        if (! preg_match('/^[0-9]{3}$/', $prefixe)) {
            return redirect()->to('/operateur/prefixes-autres/ajouter')->withInput()
                ->with('error', 'Le préfixe doit contenir exactement 3 chiffres.');
        }

        if (! is_numeric($pct) || (float) $pct < 0 || (float) $pct > 100) {
            return redirect()->to('/operateur/prefixes-autres/ajouter')->withInput()
                ->with('error', 'La commission doit être un pourcentage compris entre 0 et 100.');
        }

        // Un prefixe ne peut pas etre a la fois le notre et celui d'un partenaire :
        // le transfert serait a la fois interne et externe.
        $prefixeModel = new PrefixeOperateurModel();

        if ($prefixeModel->where('prefixe', $prefixe)->first() !== null) {
            return redirect()->to('/operateur/prefixes-autres/ajouter')->withInput()
                ->with('error', 'Ce préfixe est déjà un de vos propres préfixes.');
        }

        $commissionModel = new CommissionAutresOperateursModel();

        if ($commissionModel->parPrefixe($prefixe) !== null) {
            return redirect()->to('/operateur/prefixes-autres/ajouter')->withInput()
                ->with('error', 'Ce préfixe partenaire existe déjà.');
        }

        $commissionModel->insert([
            'prefixe_autre_operateur' => $prefixe,
            'pct_commission'          => (float) $pct,
            'date_creation'           => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/operateur/prefixes-autres')
            ->with('succes', 'Préfixe ' . $prefixe . ' ajouté.');
    }

    public function updateCommission(int $id)
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        $pct = $this->request->getPost('pct_commission');

        if (! is_numeric($pct) || (float) $pct < 0 || (float) $pct > 100) {
            return redirect()->to('/operateur/prefixes-autres')
                ->with('error', 'La commission doit être un pourcentage compris entre 0 et 100.');
        }

        $commissionModel = new CommissionAutresOperateursModel();

        if ($commissionModel->find($id) === null) {
            return redirect()->to('/operateur/prefixes-autres')
                ->with('error', 'Ce préfixe partenaire est introuvable.');
        }

        // Seule la commission est modifiable : le prefixe reste fige, sinon
        // l'historique deja enregistre ne correspondrait plus.
        $commissionModel->update($id, ['pct_commission' => (float) $pct]);

        return redirect()->to('/operateur/prefixes-autres')
            ->with('succes', 'Commission mise à jour.');
    }

    // =====================================================================
    // Montants a envoyer aux autres operateurs
    // =====================================================================

    public function montantsAEnvoyer()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        $historiqueModel = new HistoriqueOperationModel();
        $lignes          = $historiqueModel->montantsAEnvoyer();

        return view('operateur/montants-a-envoyer', [
            'titre'     => 'Montants à envoyer',
            'sousTitre' => 'Ce que vous devez reverser à chaque opérateur partenaire.',
            'actif'     => 'montants-a-envoyer',
            'lignes'    => $lignes,
            'total'     => array_sum(array_map(static fn ($l) => (float) $l['total'], $lignes)),
        ]);
    }

    public function prefixes()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        $operateurId = (int) session()->get('operateur_id');
        $prefixeModel = new PrefixeOperateurModel();

        $prefixes = $prefixeModel
            ->select('prefixe_operateur.id, prefixe_operateur.prefixe, prefixe_operateur.date_creation, COUNT(compte_client.id) AS nombre_comptes')
            ->join('compte_client', "compte_client.operateur_id = prefixe_operateur.operateur_id AND compte_client.telephone LIKE prefixe_operateur.prefixe || '%'", 'left')
            ->where('prefixe_operateur.operateur_id', $operateurId)
            ->groupBy('prefixe_operateur.id, prefixe_operateur.prefixe, prefixe_operateur.date_creation')
            ->orderBy('prefixe_operateur.prefixe', 'ASC')
            ->findAll();

        return view('operateur/prefixes', [
            'prefixes' => $prefixes,
        ]);
    }

public function comptesClients()
{
    if (! session()->get('operateur_logged_in')) {
        return redirect()->to('/operateur/login');
    }

    $operateurId = (int) session()->get('operateur_id');

    $prefixModel    = new PrefixeOperateurModel();

    $result = $prefixModel->select("prefixe_operateur.prefixe AS prefixe, COUNT(compte_client.id) AS nombre_comptes")
        ->join('compte_client', "compte_client.operateur_id = prefixe_operateur.operateur_id AND compte_client.telephone LIKE prefixe_operateur.prefixe || '%'", 'left')
        ->where('prefixe_operateur.operateur_id', $operateurId)
        ->groupBy('prefixe_operateur.id, prefixe_operateur.prefixe, prefixe_operateur.date_creation')
        ->orderBy('prefixe_operateur.prefixe', 'ASC')
        ->findAll();

    return view('operateur/comptes-clients', [
        'results' => $result,
    ]);

}

    public function addPrefix()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        $prefixe = (string) $this->request->getPost('prefixe');
        $operateurId = (int) session()->get('operateur_id');

        $prefixeModel = new PrefixeOperateurModel();

        $prefixeInstance = $prefixeModel
            ->where('prefixe', $prefixe)
            ->first();

        if($prefixeInstance !== null) {
            return redirect()
                ->to('/operateur/prefixes')
                ->with('error', 'Le préfixe est déjà pris.');
        }
        $prefixeModel->insert([
            'operateur_id' => $operateurId,
            'prefixe' => $prefixe,
            'date_creation' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/operateur/prefixes');
    }
}