<?php

namespace App\Controllers;

use App\Models\GerantOperateurModel;
use App\Models\PrefixeOperateurModel;

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

        return view('operateur/dashboard');
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

        return view('operateur/comptes-clients');
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