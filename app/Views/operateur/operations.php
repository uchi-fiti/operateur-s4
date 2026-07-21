<?= $this->extend('operateur/layout') ?>

<?= $this->section('actions') ?>
<button type="button" class="btn btn-principal" data-bs-toggle="modal" data-bs-target="#modalOperation">
  Ajouter une opération
</button>
<?= $this->endSection() ?>

<?= $this->section('contenu') ?>
<section class="card mb-4">
  <div class="card-body">
    <form class="row g-3 align-items-end" id="formulaireType" method="get" action="/operateur/operations">
      <div class="col-12 col-md-5">
        <label for="typeOperation" class="form-label">Type d'opération</label>
        <select class="form-select" id="typeOperation" name="typeOperation">
          <option value="">Tous les types</option>
          <?php foreach (($typeOperations ?? []) as $typeOperation): ?>
            <option value="<?= esc($typeOperation['id']) ?>"
              <?= (string) $typeChoisi === (string) $typeOperation['id'] ? 'selected' : '' ?>>
              <?= esc($typeOperation['nom']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-auto">
        <button type="submit" class="btn btn-principal">Afficher</button>
      </div>
    </form>
  </div>
</section>

<section class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th scope="col">Type</th>
            <th scope="col">Montant min (Ar)</th>
            <th scope="col">Montant max (Ar)</th>
            <th scope="col">Frais (Ar)</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($operations)): ?>
            <tr>
              <td colspan="5" class="text-center text-muted py-4">Aucun barème pour ce type d'opération.</td>
            </tr>
          <?php endif; ?>

          <?php foreach (($operations ?? []) as $operation): ?>
            <tr>
              <td><?= esc($operation['type_nom']) ?></td>
              <td><?= esc(number_format((float) $operation['montant_min'], 0, ',', ' ')) ?></td>
              <td><?= esc(number_format((float) $operation['montant_max'], 0, ',', ' ')) ?></td>
              <td><?= esc(number_format((float) $operation['frais'], 0, ',', ' ')) ?></td>
              <td>
                <a href="/operateur/operations/<?= esc($operation['id']) ?>/edit" class="btn btn-neutre">Modifier</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<div class="modal fade" id="modalOperation" tabindex="-1" aria-labelledby="titreModalOperation" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form action="/operateur/operations" method="post">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h2 class="modal-title h5" id="titreModalOperation">Ajouter une opération</h2>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
        </div>

        <div class="modal-body">
          <div class="mb-3">
            <label for="type_operation_id" class="form-label">Type d'opération</label>
            <select class="form-select" id="type_operation_id" name="type_operation_id" required>
              <?php foreach (($typeOperations ?? []) as $typeOperation): ?>
                <option value="<?= esc($typeOperation['id']) ?>"
                  <?= (string) old('type_operation_id') === (string) $typeOperation['id'] ? 'selected' : '' ?>>
                  <?= esc($typeOperation['nom']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="montant_min" class="form-label">Montant minimum (Ar)</label>
            <input type="number" class="form-control" id="montant_min" name="montant_min"
                   min="0" step="100" placeholder="0" value="<?= esc(old('montant_min')) ?>" required>
          </div>

          <div class="mb-3">
            <label for="montant_max" class="form-label">Montant maximum (Ar)</label>
            <input type="number" class="form-control" id="montant_max" name="montant_max"
                   min="0" step="100" placeholder="2000" value="<?= esc(old('montant_max')) ?>" required>
          </div>

          <div>
            <label for="frais" class="form-label">Frais (Ar)</label>
            <input type="number" class="form-control" id="frais" name="frais"
                   min="0" step="10" placeholder="50" value="<?= esc(old('frais')) ?>" required>
            <div class="form-text">
              Frais prélevés sur cette tranche. La tranche ne doit pas en chevaucher une autre du même type.
            </div>
          </div>
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
  // Le filtre s'applique des le changement, sans passer par le bouton.
  document.getElementById('typeOperation').addEventListener('change', (evenement) => {
    evenement.target.form.submit();
  });

  <?php if (session('errors')): ?>
  // Une saisie a ete refusee : on rouvre le modal, deja repopule par old().
  new bootstrap.Modal(document.getElementById('modalOperation')).show();
  <?php endif; ?>
</script>
<?= $this->endSection() ?>
