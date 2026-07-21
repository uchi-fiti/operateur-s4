<?php

namespace App\Controllers;

use App\Models\CommissionAutresOperateursModel;
use App\Models\CompteClientModel;
use App\Models\HistoriqueOperationModel;
use App\Models\OperationOperateurModel;
use App\Models\TypeOperationModel;
use App\Models\PromotionTransfertModel;


use CodeIgniter\HTTP\RedirectResponse;

class ClientController extends BaseController
{
    protected CompteClientModel $comptes;
    protected HistoriqueOperationModel $historiques;
    protected OperationOperateurModel $baremes;
    protected TypeOperationModel $types;
    protected CommissionAutresOperateursModel $commissions;
    protected PromotionTransfertModel $promotions;

    public function __construct()
    {
        $this->comptes     = new CompteClientModel();
        $this->historiques = new HistoriqueOperationModel();
        $this->baremes     = new OperationOperateurModel();
        $this->types       = new TypeOperationModel();
        $this->commissions = new CommissionAutresOperateursModel();
        $this->promotions = new PromotionTransfertModel();
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

        // numero de telephone: chiffre 0 a 9 avec exactement 10 de length
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

        $promotion = $this->promotions->getPromotion();

        $promotionMessage = '';

        if($promotion !== null) {
            $frais = $frais - $frais * $promotion;
            $promotionMessage = 'Une promotion de '. $promotion * 100 . ' % a été appliqué sur le frais de transfert'; 
        }
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

        // Un seul chemin d'ecriture pour les deux cas : seules changent les
        // colonnes du destinataire et la commission (nulle en interne).
        $db = db_connect();
        $db->transStart();

        $this->comptes->ajusterSolde($compteId, -$total);

        if ($beneficiaire !== null) {
            $montant_solde = $montant  * (100 - $beneficiaire['pctg_epargne']) / 100 ;
            $montant_epargne = $montant  * $beneficiaire['pctg_epargne'] / 100;
            $this->comptes->ajusterSolde((int) $beneficiaire['id'], $montant_solde);
            $this->comptes->ajusterEpargne((int) $beneficiaire['id'], $montant_epargne);
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
                . $destinataire . ' effectué (frais : ' . $this->formater($frais + $commission) . ' Ar).' . $promotionMessage);
    }

    // =====================================================================
    // Envoi multiple
    // =====================================================================

    /** Nombre de destinataires acceptes pour un envoi multiple. */
    private const MIN_DESTINATAIRES = 2;
    private const MAX_DESTINATAIRES = 10;

    public function showEnvoiMultiple()
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        return view('client/envoi-multiple', [
            'titre'     => 'Envoi multiple',
            'sousTitre' => 'Répartissez un montant entre plusieurs numéros Telma.',
            'actif'     => 'envoi-multiple',
            'compte'    => $this->comptes->find($this->compteId()),
            'minimum'   => self::MIN_DESTINATAIRES,
            'maximum'   => self::MAX_DESTINATAIRES,
        ]);
    }

    public function envoiMultiple(): RedirectResponse
    {
        if ($redirection = $this->exigerConnexion()) {
            return $redirection;
        }

        $liste   = $this->nettoyerDestinataires($this->request->getPost('destinataires'));
        $montant = $this->montantSaisi('montant');
        $code    = trim((string) $this->request->getPost('code'));

        $compteId = $this->compteId();
        $compte   = $this->comptes->find($compteId);

        if ($montant === null) {
            return $this->echecEnvoiMultiple('Le montant doit être un nombre strictement positif.');
        }

        $nombre = count($liste);

        if ($nombre < self::MIN_DESTINATAIRES) {
            return $this->echecEnvoiMultiple('Indiquez au moins ' . self::MIN_DESTINATAIRES
                . ' destinataires. Pour un envoi vers un seul numéro, utilisez la page Transfert.');
        }

        if ($nombre > self::MAX_DESTINATAIRES) {
            return $this->echecEnvoiMultiple('Vous ne pouvez pas dépasser ' . self::MAX_DESTINATAIRES
                . ' destinataires par envoi.');
        }

        if (count(array_unique($liste)) !== $nombre) {
            return $this->echecEnvoiMultiple('La liste contient deux fois le même numéro.');
        }

        foreach ($liste as $numero) {
            if (! preg_match('/^[0-9]{10}$/', $numero)) {
                return $this->echecEnvoiMultiple('Le numéro « ' . $numero
                    . ' » est invalide : 10 chiffres attendus, sans espace.');
            }
        }

        // L'envoi multiple est reserve a notre operateur : chaque numero doit
        // exister dans compte_client. Un prefixe partenaire est refuse, mais
        // avec un message qui explique pourquoi.
        $beneficiaires = $this->comptes->parTelephones($liste);

        foreach ($liste as $numero) {
            if (isset($beneficiaires[$numero])) {
                continue;
            }

            $autreOp = $this->commissions->pourTelephone($numero);

            return $this->echecEnvoiMultiple($autreOp !== null
                ? 'Le numéro ' . $numero . ' appartient à un autre opérateur (préfixe '
                    . $autreOp['prefixe_autre_operateur'] . "). L'envoi multiple est réservé aux numéros Telma."
                : 'Le numéro ' . $numero . " n'existe pas.");
        }

        foreach ($beneficiaires as $numero => $beneficiaire) {
            if ((int) $beneficiaire['id'] === $compteId) {
                return $this->echecEnvoiMultiple('Vous ne pouvez pas vous inclure dans la liste des destinataires.');
            }
        }

        // Le montant doit se diviser exactement : on ne veut ni arrondi silencieux
        // ni reste attribue arbitrairement a l'un des destinataires.
        if (fmod($montant, $nombre) != 0.0) {
            $inferieur = floor($montant / $nombre) * $nombre;
            $superieur = ceil($montant / $nombre) * $nombre;

            return $this->echecEnvoiMultiple('Le montant doit être divisible par ' . $nombre
                . ' pour être réparti en parts égales. Essayez ' . $this->formater($inferieur)
                . ' ou ' . $this->formater($superieur) . ' Ar.');
        }

        $part   = $montant / $nombre;
        $typeId = $this->types->idParNom('Transfert');

        // Chaque part est un transfert a part entiere : les frais sont ceux de
        // la tranche du bareme ou tombe la PART, et non le montant total.
        $fraisPart = $this->baremes->fraisPour($typeId, $part);

        if ($fraisPart === null) {
            return $this->echecEnvoiMultiple('Part hors barème : ' . $this->formater($part)
                . ' Ar par destinataire. ' . $this->messageHorsBareme($typeId, 'transfert'));
        }

        $fraisTotal = $fraisPart * $nombre;
        $total      = $montant + $fraisTotal;

        if ((float) $compte['solde'] < $total) {
            return $this->echecEnvoiMultiple('Solde insuffisant : cet envoi coûte ' . $this->formater($total)
                . ' Ar frais compris, votre solde est de ' . $this->formater((float) $compte['solde']) . ' Ar.');
        }

        // Code secret verifie en dernier, comme sur la page Transfert.
        if (! hash_equals((string) $compte['code_secret'], $code)) {
            return $this->echecEnvoiMultiple('Code secret incorrect.');
        }

        $db = db_connect();
        $db->transStart();

        $this->comptes->ajusterSolde($compteId, -$total);

        // Une ligne par destinataire, chacune portant sa part et ses propres
        // frais : l'historique est identique a N transferts simples.
        foreach ($liste as $numero) {
            $beneficiaire = $beneficiaires[$numero];

            $this->comptes->ajusterSolde((int) $beneficiaire['id'], $part);

            $this->historiques->insert([
                'type_operation_id'     => $typeId,
                'compte_source'         => $compteId,
                'compte_destination'    => (int) $beneficiaire['id'],
                'telephone_destination' => null,
                'montant'               => $part,
                'frais'                 => $fraisPart,
                'commission'            => 0,
                'date_operation'        => date('Y-m-d H:i:s'),
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->echecEnvoiMultiple("L'envoi n'a pas pu être enregistré, veuillez réessayer.");
        }

        return redirect()->to('client/solde')
            ->with('succes', 'Envoi de ' . $this->formater($montant) . ' Ar réparti entre '
                . $nombre . ' destinataires (' . $this->formater($part) . ' Ar chacun, frais : '
                . $this->formater($fraisPart) . ' Ar par destinataire, soit '
                . $this->formater($fraisTotal) . ' Ar).');
    }

    /**
     * Retour d'erreur de l'envoi multiple : redirection explicite plutot que
     * back(), qui dependrait de l'en-tete Referer, en conservant la saisie.
     */
    private function echecEnvoiMultiple(string $message): RedirectResponse
    {
        return redirect()->to('client/envoi-multiple')->withInput()->with('erreur', $message);
    }

    /**
     * Normalise la liste de numeros postee : trim, retrait des champs vides,
     * reindexation. getPost() peut renvoyer null ou une chaine, d'ou le cast.
     */
    private function nettoyerDestinataires($brut): array
    {
        $liste = [];

        foreach ((array) $brut as $numero) {
            $numero = trim((string) $numero);

            if ($numero !== '') {
                $liste[] = $numero;
            }
        }

        return $liste;
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

        $typeId = $this->types->idParNom('Transfert');
        $frais  = $this->baremes->fraisPour($typeId, $montant);

        if ($frais === null) {
            return $this->response->setJSON(['frais' => 0]);
        }

        // Vers un autre operateur, le client paie aussi la commission : on
        // annonce le total preleve, comme le fera l'historique.
        $commission = 0.0;

        if ($this->comptes->parTelephone($destinataire) === null) {
            $autreOp = $this->commissions->pourTelephone($destinataire);

            if ($autreOp !== null) {
                $commission = $this->commissions->commissionPour((float) $autreOp['pct_commission'], $montant);
            }
        }

        return $this->response->setJSON([
            'frais'      => $frais + $commission,
            'commission' => $commission,
        ]);
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


    public function changerEpargne(){
        $pctg = $this->request->getGet("pctg");
        $loggedId = session()->get("compte_id");
        $this->comptes->update($loggedId, ["pctg_epargne" => $pctg]);
        return view("client/epargne");
    }

    public function pageEpargne(){
        return view("client/epargne");
    }
}
