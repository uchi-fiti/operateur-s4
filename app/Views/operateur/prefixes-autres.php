<?= $this->extend('operateur/layout') ?>

<?= $this->section('actions') ?>
<a class="btn btn-principal" href="/operateur/prefixes-autres/ajouter">Ajouter un préfixe</a>
<?= $this->endSection() ?>

<?= $this->section('contenu') ?>
<section class="card">
  <div class="card-body">
    <?php if (empty($prefixes)): ?>
      <p class="mb-0">Aucun préfixe partenaire enregistré. Les transferts vers les autres
        opérateurs sont donc tous refusés pour le moment.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th scope="col">Préfixe</th>
              <th scope="col">Commission transfert</th>
              <th scope="col">Date d'ajout</th>
              <th scope="col">Action</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($prefixes as $ligne): ?>
              <tr>
                <td><?= esc($ligne['prefixe_autre_operateur']) ?></td>
                <td><?= esc(rtrim(rtrim(number_format((float) $ligne['pct_commission'], 2, ',', ' '), '0'), ',')) ?> %</td>
                <td><?= esc(date('d/m/Y', strtotime($ligne['date_creation']))) ?></td>
                <td>
                  <button type="button" class="btn btn-neutre btn-modifier"
                          data-bs-toggle="modal" data-bs-target="#modalCommission"
                          data-id="<?= esc($ligne['id']) ?>"
                          data-prefixe="<?= esc($ligne['prefixe_autre_operateur']) ?>"
                          data-pct="<?= esc($ligne['pct_commission']) ?>">
                    Modifier la commission
                  </button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>

<div class="modal fade" id="modalCommission" tabindex="-1" aria-labelledby="titreModalCommission" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" id="formCommission">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h2 class="modal-title h5" id="titreModalCommission">Modifier la commission</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>

        <div class="modal-body">
          <p class="sous-titre mb-4">
            Préfixe <strong id="apercuPrefixe"></strong>. Seule la commission est modifiable.
          </p>

          <label for="pct_commission" class="form-label">Commission transfert (%)</label>
          <input type="number" class="form-control" id="pct_commission" name="pct_commission"
                 min="0" max="100" step="0.01" required>
          <div class="form-text">Pourcentage du montant transféré, reversé à cet opérateur.</div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-neutre" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn btn-principal">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // Le modal est unique : on le repointe vers la bonne ligne a l'ouverture.
  const formCommission = document.getElementById('formCommission');

  document.querySelectorAll('.btn-modifier').forEach((bouton) => {
    bouton.addEventListener('click', () => {
      document.getElementById('apercuPrefixe').textContent = bouton.dataset.prefixe;
      document.getElementById('pct_commission').value = bouton.dataset.pct;
      formCommission.action = '/operateur/prefixes-autres/modifier/' + bouton.dataset.id;
    });
  });
</script>
<?= $this->endSection() ?>
