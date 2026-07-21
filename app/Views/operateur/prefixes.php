<?= $this->extend('operateur/layout') ?>

<?= $this->section('actions') ?>
<button type="button" class="btn btn-principal" data-bs-toggle="modal" data-bs-target="#modalPrefixe">
  Ajouter un préfixe
</button>
<?= $this->endSection() ?>

<?= $this->section('contenu') ?>
<section class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th scope="col">Préfixe</th>
            <th scope="col">Date d'ajout</th>
            <th scope="col">Nombre de comptes</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($prefixes)): ?>
            <tr>
              <td colspan="3" class="text-center text-muted py-4">Aucun préfixe trouvé pour cet opérateur.</td>
            </tr>
          <?php endif; ?>

          <?php foreach (($prefixes ?? []) as $prefixe): ?>
            <tr>
              <td><?= esc($prefixe['prefixe']) ?></td>
              <td><?= esc(date('d/m/Y', strtotime($prefixe['date_creation']))) ?></td>
              <td><?= esc(number_format((int) $prefixe['nombre_comptes'], 0, ',', ' ')) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<div class="modal fade" id="modalPrefixe" tabindex="-1" aria-labelledby="titreModalPrefixe" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="/operateur/prefixes" method="post">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h2 class="modal-title h5" id="titreModalPrefixe">Ajouter un préfixe</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>

        <div class="modal-body">
          <label for="prefixe" class="form-label">Préfixe</label>
          <input type="text" class="form-control" id="prefixe" name="prefixe"
                 inputmode="numeric" pattern="[0-9]{3}" maxlength="3" placeholder="034" required>
          <div class="form-text">3 chiffres exactement.</div>
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
