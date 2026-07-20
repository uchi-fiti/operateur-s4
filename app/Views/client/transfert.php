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
  const retour = document.getElementById('retourDestinataire');
  const messageParDefaut = '10 chiffres, sans espace.';

  let minuteur = null;
  let requeteEnCours = null;

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
      })
      .catch((erreur) => {
        if (erreur.name !== 'AbortError') {
          reinitialiser();
        }
      });
  }

  champDestinataire.addEventListener('input', () => {
    const telephone = champDestinataire.value.trim();

    clearTimeout(minuteur);

    if (telephone.length < 10) {
      reinitialiser();
      return;
    }

    // Petit delai pour ne pas interroger le serveur a chaque frappe.
    minuteur = setTimeout(() => verifier(telephone), 250);
  });

  // Le formulaire peut revenir pre-rempli apres une erreur : on verifie tout de suite.
  if (champDestinataire.value.trim().length === 10) {
    verifier(champDestinataire.value.trim());
  }
</script>
<?= $this->endSection() ?>
