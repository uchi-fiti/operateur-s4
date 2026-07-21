<?= $this->extend('client/layout') ?>

<?= $this->section('contenu') ?>
<div class="row g-4">
  <div class="col-12 col-lg-6">
    <section class="card">
      <div class="card-body">
        <form action="<?= base_url('client/envoi-multiple') ?>" method="post" id="formEnvoiMultiple">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label class="form-label" for="destinataire0">Numéros des destinataires</label>
            <div id="listeDestinataires">
              <?php
                // Apres une erreur, on reaffiche exactement ce qui avait ete saisi.
                $saisis = (array) (old('destinataires') ?? []);
                $saisis = array_values(array_filter($saisis, static fn ($n) => trim((string) $n) !== ''));

                while (count($saisis) < $minimum) {
                    $saisis[] = '';
                }
              ?>
              <?php foreach ($saisis as $index => $numero): ?>
                <div class="input-group mb-2 ligne-destinataire">
                  <input type="tel" class="form-control champ-destinataire"
                         id="destinataire<?= $index ?>" name="destinataires[]"
                         inputmode="numeric" pattern="[0-9]{10}" maxlength="10"
                         placeholder="0340100002" value="<?= esc($numero) ?>"
                         autocomplete="off" required>
                  <button type="button" class="btn btn-neutre btn-supprimer">Retirer</button>
                </div>
              <?php endforeach; ?>
            </div>
            <div class="form-text" id="aideDestinataires">
              Entre <?= (int) $minimum ?> et <?= (int) $maximum ?> numéros Telma, 10 chiffres chacun.
            </div>
            <button type="button" class="btn btn-neutre mt-2" id="btnAjouter">Ajouter un destinataire</button>
          </div>

          <div class="mb-3">
            <label for="montant" class="form-label">Montant total à répartir (Ar)</label>
            <input type="number" class="form-control" id="montant" name="montant"
                   min="1" step="1" placeholder="30000"
                   value="<?= esc(old('montant')) ?>" required>
            <div class="form-text" id="aideRepartition">
              Solde disponible : <?= number_format((float) $compte['solde'], 0, ',', '.') ?> Ar.
              Le montant est divisé en parts égales. Les frais sont ceux d'un transfert
              simple, appliqués à chaque part.
            </div>
          </div>

          <div class="mb-4">
            <label for="code" class="form-label">Code secret</label>
            <input type="password" class="form-control" id="code" name="code"
                   inputmode="numeric" pattern="[0-9]{4}" maxlength="4"
                   placeholder="••••" autocomplete="off" required>
            <div class="form-text">4 chiffres.</div>
          </div>

          <button type="submit" class="btn btn-principal w-100">Envoyer</button>
        </form>
      </div>
    </section>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  const MIN = <?= (int) $minimum ?>;
  const MAX = <?= (int) $maximum ?>;
  const URL_VERIF = '<?= base_url('client/verifier-destinataire') ?>';

  const liste = document.getElementById('listeDestinataires');
  const btnAjouter = document.getElementById('btnAjouter');
  const champMontant = document.getElementById('montant');
  const aideRepartition = document.getElementById('aideRepartition');
  const aideDestinataires = document.getElementById('aideDestinataires');
  const texteRepartitionParDefaut = aideRepartition.textContent;

  const requetes = new Map();

  function lignes() {
    return Array.from(liste.querySelectorAll('.ligne-destinataire'));
  }

  // Le bouton Retirer disparait quand on est au minimum, et Ajouter quand on
  // est au maximum : les regles du serveur restent visibles dans l'interface.
  function majBoutons() {
    const total = lignes().length;

    lignes().forEach((ligne) => {
      ligne.querySelector('.btn-supprimer').disabled = total <= MIN;
    });

    btnAjouter.disabled = total >= MAX;
    aideDestinataires.textContent = total >= MAX
      ? 'Maximum de ' + MAX + ' destinataires atteint.'
      : 'Entre ' + MIN + ' et ' + MAX + ' numéros Telma, 10 chiffres chacun.';
  }

  function majRepartition() {
    const montant = parseFloat(champMontant.value);
    const nombre = lignes().length;

    if (!montant || montant <= 0) {
      aideRepartition.textContent = texteRepartitionParDefaut;
      aideRepartition.classList.remove('text-danger', 'text-success');
      return;
    }

    if (montant % nombre !== 0) {
      const bas = Math.floor(montant / nombre) * nombre;
      const haut = Math.ceil(montant / nombre) * nombre;
      aideRepartition.textContent = 'Ce montant ne se divise pas en ' + nombre
        + ' parts égales. Essayez ' + bas.toLocaleString('fr-FR')
        + ' ou ' + haut.toLocaleString('fr-FR') + ' Ar.';
      aideRepartition.classList.add('text-danger');
      aideRepartition.classList.remove('text-success');
      return;
    }

    aideRepartition.textContent = 'Chaque destinataire recevra '
      + (montant / nombre).toLocaleString('fr-FR') + ' Ar.';
    aideRepartition.classList.add('text-success');
    aideRepartition.classList.remove('text-danger');
  }

  function verifier(champ) {
    const telephone = champ.value.trim();

    champ.classList.remove('is-valid', 'is-invalid');

    if (telephone.length !== 10) {
      return;
    }

    const precedente = requetes.get(champ);
    if (precedente) {
      precedente.abort();
    }

    const controleur = new AbortController();
    requetes.set(champ, controleur);

    fetch(URL_VERIF + '?telephone=' + encodeURIComponent(telephone), {
      signal: controleur.signal,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then((reponse) => reponse.json())
      .then((donnees) => {
        // L'endpoint accepte aussi les numeros d'autres operateurs : ici c'est
        // une erreur, l'envoi multiple etant reserve a notre operateur.
        const valide = donnees.existe === true && donnees.externe !== true;
        champ.classList.toggle('is-valid', valide);
        champ.classList.toggle('is-invalid', !valide);
      })
      .catch((erreur) => {
        if (erreur.name !== 'AbortError') {
          champ.classList.remove('is-valid', 'is-invalid');
        }
      });
  }

  function ajouterLigne() {
    if (lignes().length >= MAX) {
      return;
    }

    const ligne = document.createElement('div');
    ligne.className = 'input-group mb-2 ligne-destinataire';
    ligne.innerHTML = '<input type="tel" class="form-control champ-destinataire"'
      + ' name="destinataires[]" inputmode="numeric" pattern="[0-9]{10}" maxlength="10"'
      + ' placeholder="0340100002" autocomplete="off" required>'
      + '<button type="button" class="btn btn-neutre btn-supprimer">Retirer</button>';

    liste.appendChild(ligne);
    ligne.querySelector('input').focus();

    majBoutons();
    majRepartition();
  }

  btnAjouter.addEventListener('click', ajouterLigne);

  // Delegation : les lignes ajoutees dynamiquement sont couvertes sans avoir a
  // leur rebrancher un ecouteur.
  liste.addEventListener('click', (evenement) => {
    if (! evenement.target.classList.contains('btn-supprimer')) {
      return;
    }

    if (lignes().length <= MIN) {
      return;
    }

    evenement.target.closest('.ligne-destinataire').remove();
    majBoutons();
    majRepartition();
  });

  let minuteur = null;

  liste.addEventListener('input', (evenement) => {
    if (! evenement.target.classList.contains('champ-destinataire')) {
      return;
    }

    const champ = evenement.target;
    clearTimeout(minuteur);
    minuteur = setTimeout(() => verifier(champ), 250);
  });

  champMontant.addEventListener('input', majRepartition);

  // Le formulaire peut revenir pre-rempli apres une erreur.
  document.querySelectorAll('.champ-destinataire').forEach((champ) => {
    if (champ.value.trim().length === 10) {
      verifier(champ);
    }
  });

  majBoutons();
  majRepartition();
</script>
<?= $this->endSection() ?>
