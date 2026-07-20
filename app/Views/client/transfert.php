<?= $this->extend('client/layout') ?>

<?= $this->section('contenu') ?>
<div class="row g-4">
  <div class="col-12 col-lg-5">
    <section class="card">
      <div class="card-body">
        <form action="<?= base_url('client/transfert') ?>" method="post">
          <?= csrf_field() ?>
          <div class="mb-3">
            <label for="destinataire" class="form-label">Numéro de téléphone du destinataire</label>
            <input type="tel" class="form-control" id="destinataire" name="destinataire"
                   inputmode="numeric" pattern="[0-9]{10}" maxlength="10"
                   placeholder="0340100002" value="<?= esc(old('destinataire')) ?>"
                   autocomplete="off" required>
            <div class="form-text" id="retourDestinataire">10 chiffres, sans espace.</div>
          </div>

          <div class="mb-3">
            <label for="montant" class="form-label">Montant à transférer (Ar)</label>
            <input type="number" class="form-control" id="montant" name="montant"
                   min="1" step="1" placeholder="120000"
                   value="<?= esc(old('montant')) ?>" required>
            <div class="form-text">
              Solde disponible : <?= number_format((float) $compte['solde'], 0, ',', '.') ?> Ar.
              Les frais sont prélevés en plus du montant transféré.
            </div>
          </div>

          <div class="mb-3">
            <div class="form-check">
              <input type="checkbox" class="form-check-input" id="inclureFraisRetrait" name="inclure_frais_retrait"
                     value="1" <?= old('inclure_frais_retrait') ? 'checked' : '' ?>>
              <label class="form-check-label" for="inclureFraisRetrait">
                Inclure les frais de retrait du destinataire
              </label>
            </div>
            <div id="fraisRetraitAffichage" class="mt-2" style="display: none;">
              <small class="text-muted">Frais de retrait : <strong id="valeurFraisRetrait">0</strong> Ar</small>
              <br>
              <small class="text-muted">Montant total avec frais : <strong id="montantTotal">0</strong> Ar</small>
            </div>
          </div>

          <div class="mb-4">
            <label for="code" class="form-label">Code secret</label>
            <input type="password" class="form-control" id="code" name="code"
                   inputmode="numeric" pattern="[0-9]{4}" maxlength="4"
                   placeholder="••••" autocomplete="off" required>
            <div class="form-text">4 chiffres.</div>
          </div>

          <button type="submit" class="btn btn-principal w-100">Transférer</button>
        </form>
      </div>
    </section>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const champDestinataire = document.getElementById('destinataire');
  const champMontant = document.getElementById('montant');
  const checkboxFraisRetrait = document.getElementById('inclureFraisRetrait');
  const retour = document.getElementById('retourDestinataire');
  const fraisTransfert = document.getElementById('fraisTransfert');
  const valeurFrais = document.getElementById('valeurFrais');
  const fraisRetraitAffichage = document.getElementById('fraisRetraitAffichage');
  const valeurFraisRetrait = document.getElementById('valeurFraisRetrait');
  const montantTotal = document.getElementById('montantTotal');
  const messageParDefaut = '10 chiffres, sans espace.';

  let minuteur = null;
  let requeteEnCours = null;
  let minuteurFrais = null;
  let requeteEnCoursFrais = null;
  let minuteurFraisRetrait = null;
  let requeteEnCoursFraisRetrait = null;
  let fraisRetraitActuel = 0;
  let montantOriginal = 0;

  function reinitialiser() {
    retour.textContent = messageParDefaut;
    retour.classList.remove('text-danger', 'text-success');
    champDestinataire.classList.remove('is-invalid', 'is-valid');
  }

  function verifier(telephone) {
    // Une seule requete a la fois : la precedente est abandonnee si l'utilisateur
    // continue de taper.
    if (requeteEnCours) {
      requeteEnCours.abort();
    }
    requeteEnCours = new AbortController();

    const url = '<?= base_url('client/verifier-destinataire') ?>?telephone=' + encodeURIComponent(telephone);

    fetch(url, { signal: requeteEnCours.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then((reponse) => reponse.json())
      .then((donnees) => {
        retour.textContent = donnees.message;
        retour.classList.toggle('text-success', donnees.existe);
        retour.classList.toggle('text-danger', !donnees.existe);
        champDestinataire.classList.toggle('is-valid', donnees.existe);
        champDestinataire.classList.toggle('is-invalid', !donnees.existe);
        
        // Recalcule les frais quand le destinataire est verifie
        calculerFrais();
      })
      .catch((erreur) => {
        if (erreur.name !== 'AbortError') {
          reinitialiser();
        }
      });
  }

  function calculerFrais() {
    const telephone = champDestinataire.value.trim();
    const montant = champMontant.value.trim();

    // Cache l'affichage des frais si les donnees sont incompletes
    if (telephone.length !== 10 || montant === '' || montant <= 0) {
      fraisTransfert.style.display = 'none';
      return;
    }

    if (requeteEnCoursFrais) {
      requeteEnCoursFrais.abort();
    }
    requeteEnCoursFrais = new AbortController();

    const url = '<?= base_url('client/calculer-frais-transfert') ?>?destinataire=' 
      + encodeURIComponent(telephone) + '&montant=' + encodeURIComponent(montant);

    fetch(url, { signal: requeteEnCoursFrais.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then((reponse) => reponse.json())
      .then((donnees) => {
        valeurFrais.textContent = new Intl.NumberFormat('fr-FR').format(donnees.frais || 0);
        fraisTransfert.style.display = 'block';
      })
      .catch((erreur) => {
        if (erreur.name !== 'AbortError') {
          fraisTransfert.style.display = 'none';
        }
      });
  }

  function calculerFraisRetrait() {
    const montant = champMontant.value.trim();

    if (montant === '' || montant <= 0) {
      fraisRetraitAffichage.style.display = 'none';
      fraisRetraitActuel = 0;
      return;
    }

    if (requeteEnCoursFraisRetrait) {
      requeteEnCoursFraisRetrait.abort();
    }
    requeteEnCoursFraisRetrait = new AbortController();

    const url = '<?= base_url('client/calculer-frais-retrait') ?>?montant=' + encodeURIComponent(montant);

    fetch(url, { signal: requeteEnCoursFraisRetrait.signal, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then((reponse) => reponse.json())
      .then((donnees) => {
        fraisRetraitActuel = donnees.frais || 0;
        valeurFraisRetrait.textContent = new Intl.NumberFormat('fr-FR').format(fraisRetraitActuel);
        
        if (checkboxFraisRetrait.checked) {
          montantOriginal = parseFloat(champMontant.value) || 0;
          const total = montantOriginal + fraisRetraitActuel;
          montantTotal.textContent = new Intl.NumberFormat('fr-FR').format(total);
          fraisRetraitAffichage.style.display = 'block';
        }
      })
      .catch((erreur) => {
        if (erreur.name !== 'AbortError') {
          fraisRetraitAffichage.style.display = 'none';
        }
      });
  }

  checkboxFraisRetrait.addEventListener('change', () => {
    if (checkboxFraisRetrait.checked) {
      // Affiche et met à jour le total
      const montant = parseFloat(champMontant.value) || 0;
      montantOriginal = montant;
      const total = montant + fraisRetraitActuel;
      montantTotal.textContent = new Intl.NumberFormat('fr-FR').format(total);
      champMontant.value = total;
      fraisRetraitAffichage.style.display = 'block';
    } else {
      // Restaure le montant original
      champMontant.value = montantOriginal;
      fraisRetraitAffichage.style.display = 'none';
    }
  });

  champDestinataire.addEventListener('input', () => {
    const telephone = champDestinataire.value.trim();

    clearTimeout(minuteur);

    if (telephone.length < 10) {
      reinitialiser();
      fraisTransfert.style.display = 'none';
      return;
    }

    // Petit delai pour ne pas interroger le serveur a chaque frappe.
    minuteur = setTimeout(() => verifier(telephone), 250);
  });

  champMontant.addEventListener('input', () => {
    clearTimeout(minuteurFrais);
    clearTimeout(minuteurFraisRetrait);
    
    // Si le checkbox n'est pas coché, on restaure montantOriginal
    if (!checkboxFraisRetrait.checked) {
      montantOriginal = parseFloat(champMontant.value) || 0;
    }
    
    minuteurFrais = setTimeout(() => calculerFrais(), 250);
    minuteurFraisRetrait = setTimeout(() => calculerFraisRetrait(), 250);
  });

  // Le formulaire peut revenir pre-rempli apres une erreur : on verifie tout de suite.
  if (champDestinataire.value.trim().length === 10) {
    verifier(champDestinataire.value.trim());
  }
  
  // Calcule les frais de retrait si le montant est pre-rempli
  if (champMontant.value.trim() !== '') {
    calculerFraisRetrait();
  }
</script>
<?= $this->endSection() ?>
