<?php

namespace App\Controllers;

use App\Models\GerantOperateurModel;

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

        return view('operateur/prefixes');
    }

    public function comptesClients()
    {
        if (! session()->get('operateur_logged_in')) {
            return redirect()->to('/operateur/login');
        }

        return view('operateur/comptes-clients');
    }
}