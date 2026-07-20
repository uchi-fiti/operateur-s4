<?php

namespace App\Controllers;

use App\Models\CommissionAutresOperateursModel;
use App\Models\CompteClientModel;
use App\Models\HistoriqueOperationModel;
use App\Models\OperationOperateurModel;
use App\Models\TypeOperationModel;

use App\Models\PrefixeOperateurModel;
use App\Models\CommissionOperateurModel;

use CodeIgniter\HTTP\RedirectResponse;

class ClientController extends BaseController
{
    protected CompteClientModel $comptes;
    protected HistoriqueOperationModel $historiques;
    protected OperationOperateurModel $baremes;
    protected TypeOperationModel $types;
    protected CommissionAutresOperateursModel $commissions;

    public function __construct()
    {
        $this->comptes     = new CompteClientModel();
        $this->historiques = new HistoriqueOperationModel();
        $this->baremes     = new OperationOperateurModel();
        $this->types       = new TypeOperationModel();
        $this->commissions = new CommissionAutresOperateursModel();
    }

    // =====================================================================
    // Connexion
    // =====================================================================

    public function showLogin(): string
    {
        return view('client/login');
    }

    /**
     * Etape 1 : le numero existe-t-il dans compte_client ?
     */
    public function verifierTelephone(): RedirectResponse
    {
        $telephone = trim((string) $this->request->getPost('telephone'));

        if (! preg_match('/^[0-9]{10}$/', $telephone)) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Le numéro de téléphone doit contenir exactement 10 chiffres.');
        }

        $compte = $this->comptes->parTelephone($telephone);

        if ($compte === null) {
            return redirect()->back()->withInput()
                ->with('erreur', "Ce numéro de téléphone n'existe pas, veuillez vous inscrire auprès d'un opérateur ou essayer un autre numéro.");
        }

        // Numero valide mais pas encore authentifie : on ne stocke que le
        // telephone en attente, surtout pas l'identifiant du compte.
        session()->set('login_telephone', $telephone);

        return redirect()->to('client/code-secret');
    }

    public function showCodeSecret()
    {
        if (! session()->has('login_telephone')) {
            return redirect()->to('client/login');
        }

        return view('client/code-secret', [
            'telephone' => session('login_telephone'),
        ]);
    }

    /**
     * Etape 2 : le code secret correspond-il au numero saisi a l'etape 1 ?
     */
    public function verifierCodeSecret(): RedirectResponse
    {
        $telephone = session('login_telephone');

        if (! $telephone) {
            return redirect()->to('client/login');
        }

        $code   = trim((string) $this->request->getPost('code'));
        $compte = $this->comptes->parTelephone($telephone);

        if ($compte === null || ! hash_equals((string) $compte['code_secret'], $code)) {
            return redirect()->back()->with('erreur', 'Code secret incorrect.');
        }

        // Authentification reussie : on regenere l'identifiant de session pour
        // eviter la fixation de session, puis on ne garde que l'id du compte.
        session()->regenerate();
        session()->remove('login_telephone');
        session()->set([
            'compte_id' => (int) $compte['id'],
            'telephone' => $compte['telephone'],
        ]);

        return redirect()->to('client/solde');
    }

    public function deconnexion(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to('client/login');
    }

    // =====================================================================
    // Pages de l'espace connecte
    // =====================================================================

    public function solde()
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        $compte = $this->comptes->avecClient($this->compteId());

        return view('client/solde', [
            'titre'     => 'Solde',
            'sousTitre' => 'Numéro de compte : ' . $compte['telephone'],
            'actif'     => 'solde',
            'compte'    => $compte,
        ]);
    }

    public function showDepot()
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        return view('client/depot', [
            'titre'     => 'Dépôt',
            'sousTitre' => "Ajoutez de l'argent sur votre compte Mobile Money.",
            'actif'     => 'depot',
        ]);
    }

    public function depot(): RedirectResponse
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        $montant = $this->montantSaisi('montant');

        if ($montant === null) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Le montant doit être un nombre strictement positif.');
        }

        $typeId = $this->types->idParNom('Depot');
        $frais  = $this->baremes->fraisPour($typeId, $montant);

        if ($frais === null) {
            return redirect()->back()->withInput()
                ->with('erreur', $this->messageHorsBareme($typeId, 'dépôt'));
        }

        $compteId = $this->compteId();
        $db       = db_connect();

        $db->transStart();

        // Depot : l'argent entre sur le compte, donc compte_destination.
        $this->comptes->ajusterSolde($compteId, $montant);
        $this->historiques->insert([
            'type_operation_id'  => $typeId,
            'compte_source'      => null,
            'compte_destination' => $compteId,
            'montant'            => $montant,
            'frais'              => $frais,
            'date_operation'     => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('erreur', "Le dépôt n'a pas pu être enregistré, veuillez réessayer.");
        }

        return redirect()->to('client/solde')
            ->with('succes', 'Dépôt de ' . $this->formater($montant) . ' Ar effectué.');
    }

    public function showRetrait()
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        return view('client/retrait', [
            'titre'     => 'Retrait',
            'sousTitre' => "Retirez de l'argent depuis votre compte Mobile Money.",
            'actif'     => 'retrait',
            'compte'    => $this->comptes->find($this->compteId()),
        ]);
    }

    public function retrait(): RedirectResponse
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        $montant = $this->montantSaisi('montant');

        if ($montant === null) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Le montant doit être un nombre strictement positif.');
        }

        $typeId = $this->types->idParNom('Retrait');
        $frais  = $this->baremes->fraisPour($typeId, $montant);

        if ($frais === null) {
            return redirect()->back()->withInput()
                ->with('erreur', $this->messageHorsBareme($typeId, 'retrait'));
        }

        $compteId = $this->compteId();
        $compte   = $this->comptes->find($compteId);
        $total    = $montant + $frais;

        if ((float) $compte['solde'] < $total) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Solde insuffisant : ce retrait coûte ' . $this->formater($total)
                    . ' Ar frais compris, votre solde est de ' . $this->formater((float) $compte['solde']) . ' Ar.');
        }

        $db = db_connect();
        $db->transStart();

        // Retrait : l'argent sort du compte, donc compte_source.
        $this->comptes->ajusterSolde($compteId, -$total);
        $this->historiques->insert([
            'type_operation_id'  => $typeId,
            'compte_source'      => $compteId,
            'compte_destination' => null,
            'montant'            => $montant,
            'frais'              => $frais,
            'date_operation'     => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('erreur', "Le retrait n'a pas pu être enregistré, veuillez réessayer.");
        }

        return redirect()->to('client/solde')
            ->with('succes', 'Retrait de ' . $this->formater($montant) . ' Ar effectué (frais : '
                . $this->formater($frais) . ' Ar).');
    }

    public function showTransfert()
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        return view('client/transfert', [
            'titre'     => 'Transfert',
            'sousTitre' => "Envoyez de l'argent vers un autre compte Mobile Money.",
            'actif'     => 'transfert',
            'compte'    => $this->comptes->find($this->compteId()),
        ]);
    }

    public function transfert(): RedirectResponse
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }


        $destinataire = trim((string) $this->request->getPost('destinataire'));
        $montant      = $this->montantSaisi('montant');
        $code         = trim((string) $this->request->getPost('code'));

        $compteId = $this->compteId();
        $compte   = $this->comptes->find($compteId);

        // withInput() sur chaque retour : le formulaire garde ce qui a ete saisi.
        if ($montant === null) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Le montant doit être un nombre strictement positif.');
        }


        // Deux cas : le destinataire est un de nos comptes (transfert interne),
        // ou son prefixe appartient a un autre operateur autorise (transfert
        // externe). Dans le second cas nous n'avons aucun compte a crediter :
        // nous enregistrons seulement ce que nous devrons lui reverser.
        $beneficiaire = $this->comptes->parTelephone($destinataire);
        $autreOp      = $beneficiaire === null
            ? $this->commissions->pourTelephone($destinataire)
            : null;

        if ($beneficiaire === null && $autreOp === null) {
            return redirect()->back()->withInput()
                ->with('erreur', "Ce numéro n'existe pas et son préfixe n'appartient à aucun opérateur partenaire.");
        }

        if ($beneficiaire !== null && (int) $beneficiaire['id'] === $compteId) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Vous ne pouvez pas transférer de l\'argent vers votre propre compte.');
        }

        $typeId = $this->types->idParNom('Transfert');

        $frais  = $this->baremes->fraisPour($typeId, $montant);

        if ($frais === null) {
            return redirect()->back()->withInput()
                ->with('erreur', $this->messageHorsBareme($typeId, 'transfert'));
        }

        // La commission est payee par le client EN PLUS des frais, puis reversee
        // integralement a l'autre operateur : elle n'entre pas dans nos revenus.
        $commission = $autreOp === null
            ? 0.0
            : $this->commissions->commissionPour((float) $autreOp['pct_commission'], $montant);

        $total = $montant + $frais + $commission;

        if ((float) $compte['solde'] < $total) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Solde insuffisant : ce transfert coûte ' . $this->formater($total)
                    . ' Ar frais compris, votre solde est de ' . $this->formater((float) $compte['solde']) . ' Ar.');
        }

        // Le code secret est verifie en dernier : les erreurs de saisie plus
        // simples sont signalees avant de demander de retaper le code.
        if (! hash_equals((string) $compte['code_secret'], $code)) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Code secret incorrect.');
        }

        // Vérifie si c'est un transfert externe
        if ($this->estTransfertExterne($destinataire)) {
            if (! $this->faireTransfertExterne($destinataire, $montant, $frais, $compteId, $typeId)) {
                return redirect()->back()->withInput()
                    ->with('erreur', 'Préfixe non pris en charge.');
            }

            return redirect()->to('client/solde')
                ->with('succes', 'Transfert externe de ' . $this->formater($montant) . ' Ar vers un autre opérateur '
                    . ' effectué (frais : ' . $this->formater($frais) . ' Ar).');
        }

        $beneficiaire = $this->comptes->parTelephone($destinataire);

        if ($beneficiaire === null) {
            return redirect()->back()->withInput()
                ->with('erreur', "Ce numéro de destinataire n'existe pas.");
        }

        if ((int) $beneficiaire['id'] === $compteId) {
            return redirect()->back()->withInput()
                ->with('erreur', 'Vous ne pouvez pas transférer de l\'argent vers votre propre compte.');
        }

        // Transfert interne

        $db = db_connect();
        $db->transStart();

        $this->comptes->ajusterSolde($compteId, -$total);

        if ($beneficiaire !== null) {
            $this->comptes->ajusterSolde((int) $beneficiaire['id'], $montant);
        }

        $this->historiques->insert([
            'type_operation_id'     => $typeId,
            'compte_source'         => $compteId,
            // Transfert externe : aucun compte a referencer, on garde le numero.
            'compte_destination'    => $beneficiaire !== null ? (int) $beneficiaire['id'] : null,
            'telephone_destination' => $beneficiaire !== null ? null : $destinataire,
            'montant'               => $montant,
            'frais'                 => $frais,
            'commission'            => $commission,
            'date_operation'        => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->withInput()
                ->with('erreur', "Le transfert n'a pas pu être enregistré, veuillez réessayer.");
        }

        return redirect()->to('client/solde')
            ->with('succes', 'Transfert de ' . $this->formater($montant) . ' Ar vers '
                . $destinataire . ' effectué (frais : ' . $this->formater($frais + $commission) . ' Ar).');
    }

    public function historique()
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        $compteId = $this->compteId();

        return view('client/historique', [
            'titre'      => 'Historique des transactions',
            'sousTitre'  => 'Vos dernières opérations, de la plus récente à la plus ancienne.',
            'actif'      => 'historique',
            'compteId'   => $compteId,
            'operations' => $this->historiques->pourCompte($compteId),
        ]);
    }

    // =====================================================================
    // AJAX
    // =====================================================================

    /**
     * Calcule les frais de transfert en fonction du montant et du destinataire.
     * Retourne 0 pour les transferts externes, frais de OperationOperateur pour internes.
     */
    public function calculerFraisTransfert()
    {
        if (! session()->has('compte_id')) {
            return $this->response->setStatusCode(401)->setJSON(['frais' => 0]);
        }

        $destinataire = trim((string) $this->request->getGet('destinataire'));
        $montant = (float) $this->request->getGet('montant');

        if (! preg_match('/^[0-9]{10}$/', $destinataire) || $montant <= 0) {
            return $this->response->setJSON(['frais' => 0]);
        }

        // Transfert externe => pas de frais
        if ($this->estTransfertExterne($destinataire)) {
            return $this->response->setJSON(['frais' => 0]);
        }

        // Transfert interne => calcule frais depuis le barème
        $typeId = $this->types->idParNom('Transfert');
        $frais = $this->baremes->fraisPour($typeId, $montant);

        return $this->response->setJSON(['frais' => $frais ?? 0]);
    }

    /**
     * Calcule les frais de retrait pour un montant donné.
     */
    public function calculerFraisRetrait()
    {
        if (! session()->has('compte_id')) {
            return $this->response->setStatusCode(401)->setJSON(['frais' => 0]);
        }

        $montant = (float) $this->request->getGet('montant');

        if ($montant <= 0) {
            return $this->response->setJSON(['frais' => 0]);
        }

        $typeId = $this->types->idParNom('Retrait');
        $frais = $this->baremes->fraisPour($typeId, $montant);

        return $this->response->setJSON(['frais' => $frais ?? 0]);
    }

    /**
     * Verifie l'existence d'un destinataire pendant la saisie du numero.
     */
    public function verifierDestinataire()
    {
        if (! session()->has('compte_id')) {
            return $this->response->setStatusCode(401)->setJSON(['existe' => false]);
        }

        $telephone = trim((string) $this->request->getGet('telephone'));


        $session = session();

        $numeroExpediteur = preg_replace('/\D/', '', $session->get('telephone'));
        $numeroDestinataire = preg_replace('/\D/', '', $telephone);

        $prefixeExpediteur = substr($numeroExpediteur, 0, 3);
        $prefixeDestinataire = substr($numeroDestinataire, 0, 3);

        $prefixModel = new PrefixeOperateurModel();

        // Recherche de l'opérateur de l'expéditeur
        $operateurExpediteur = $prefixModel
            ->where('prefixe', $prefixeExpediteur)
            ->first();

        if ($operateurExpediteur !== null) {

            // Vérifie si le préfixe du destinataire appartient au même opérateur
            $prefixe = $prefixModel
                ->where('operateur_id', $operateurExpediteur['operateur_id'])
                ->where('prefixe', $prefixeDestinataire)
                ->first();

            // Si le préfixe n'appartient pas à notre opérateur,
            // on considère qu'il s'agit d'un autre opérateur.
            if ($prefixe === null) {
                return $this->response->setJSON([
                    'existe'  => true,
                    'message' => 'Numéro appartenant à un autre opérateur.',
                ]);
            }
        }

        if (! preg_match('/^[0-9]{10}$/', $telephone)) {
            return $this->response->setJSON([
                'existe'  => false,
                'message' => 'Le numéro doit contenir 10 chiffres.',
            ]);
        }

        $beneficiaire = $this->comptes->parTelephone($telephone);

        if ($beneficiaire !== null) {
            if ((int) $beneficiaire['id'] === $this->compteId()) {
                return $this->response->setJSON([
                    'existe'  => false,
                    'message' => 'Il s\'agit de votre propre numéro.',
                ]);
            }

            $client = $this->comptes->avecClient((int) $beneficiaire['id']);

            // On ne renvoie que le nom : ni identifiant, ni solde, ni code secret.
            return $this->response->setJSON([
                'existe'  => true,
                'message' => 'Destinataire : ' . trim($client['prenom'] . ' ' . $client['nom']) . '.',
            ]);
        }

        // Numero inconnu chez nous : reste a savoir si son prefixe appartient a
        // un operateur partenaire. On annonce alors la commission, puisque le
        // client la paiera en plus des frais.
        $autreOp = $this->commissions->pourTelephone($telephone);

        if ($autreOp === null) {
            return $this->response->setJSON([
                'existe'  => false,
                'message' => "Ce numéro n'existe pas et son préfixe n'est pas autorisé.",
            ]);
        }

        return $this->response->setJSON([
            'existe'  => true,
            'externe' => true,
            'message' => 'Transfert vers un autre opérateur (préfixe '
                . $autreOp['prefixe_autre_operateur'] . ') : commission de '
                . rtrim(rtrim(number_format((float) $autreOp['pct_commission'], 2, ',', ' '), '0'), ',')
                . ' % en plus des frais.',
        ]);
    }

    // =====================================================================
    // Utilitaires
    // =====================================================================

    /**
     * Renvoie une redirection si l'utilisateur n'est pas connecte, null sinon.
     */
    private function exigerConnexion(): ?RedirectResponse
    {
        if (! session()->has('compte_id')) {
            return redirect()->to('client/login')
                ->with('erreur', 'Veuillez vous connecter pour accéder à cette page.');
        }

        return null;
    }

    private function compteId(): int
    {
        return (int) session('compte_id');
    }

    /**
     * Lit un montant poste et le valide. Retourne null s'il est absent, non
     * numerique ou negatif ou nul.
     */
    private function montantSaisi(string $champ): ?float
    {
        $brut = str_replace([' ', ','], ['', '.'], (string) $this->request->getPost($champ));

        if ($brut === '' || ! is_numeric($brut)) {
            return null;
        }

        $montant = (float) $brut;

        return $montant > 0 ? $montant : null;
    }

    private function messageHorsBareme(int $typeId, string $libelle): string
    {
        $bornes = $this->baremes->bornes($typeId);

        if ($bornes === null) {
            return "Aucun barème n'est défini pour ce type d'opération.";
        }

        return 'Montant hors barème : le ' . $libelle . ' doit être compris entre '
            . $this->formater($bornes['minimum']) . ' et ' . $this->formater($bornes['maximum']) . ' Ar.';
    }

    private function formater(float $montant): string
    {
        return number_format($montant, 0, ',', '.');
    }


    /**
     * Vérifie si le transfert est vers un numéro appartenant à un opérateur différent.
     */
    public function estTransfertExterne(string $numeroDestinataire): bool
    {
        $session = session();
        $numeroExpediteur = preg_replace('/\D/', '', $session->get('telephone'));
        $numeroDestinataire = preg_replace('/\D/', '', $numeroDestinataire);

        $prefixeExpediteur = substr($numeroExpediteur, 0, 3);
        $prefixeDestinataire = substr($numeroDestinataire, 0, 3);

        // Si même préfixe => transfert interne
        if ($prefixeExpediteur === $prefixeDestinataire) {
            return false;
        }

        $prefixModel = new PrefixeOperateurModel();

        // Récupère l'opérateur du préfixe expéditeur
        $operateurExpediteur = $prefixModel->where('prefixe', $prefixeExpediteur)->first();
        if ($operateurExpediteur === null) {
            return false;
        }

        // Récupère l'opérateur du préfixe destinataire
        $operateurDestinataire = $prefixModel->where('prefixe', $prefixeDestinataire)->first();
        if ($operateurDestinataire === null) {
            return true; // Préfixe inconnu => transfert externe
        }

        // Opérateurs différents => transfert externe
        return $operateurDestinataire['operateur_id'] !== $operateurExpediteur['operateur_id'];
    }
    /**
     * Effectue un transfert externe (vers un autre opérateur).
     * Retourne true si succès, false sinon.
     */
    public function faireTransfertExterne(string $destinataire, float $montant, float $frais, int $compteId, int $typeId): bool
    {
        $commissionModel = new CommissionOperateurModel();
        $prefixeDestinataire = substr(preg_replace('/\D/', '', $destinataire), 0, 3);

        $commission = $commissionModel->where('prefixe_autre_operateur', $prefixeDestinataire)->first();
        if ($commission === null) {
            return false;
        }

        $valeur_commission = $montant * ($commission['pct_commission'] / 100);
        $total = $montant + $frais;

        $db = db_connect();
        $db->transStart();

        // Déduit le montant + frais du compte source
        $this->comptes->ajusterSolde($compteId, -$total);

        // Enregistre la transaction
        $this->historiques->insert([
            'type_operation_id'  => $typeId,
            'compte_source'      => $compteId,
            'compte_destination' => null,
            'montant'            => $montant,
            'frais'              => $frais,
            'commission'         => $valeur_commission,
            'date_operation'     => date('Y-m-d H:i:s'),
        ]);

        $db->transComplete();

        return $db->transStatus() !== false;
    }

}
