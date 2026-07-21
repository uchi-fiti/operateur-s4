<?= $this->extend('operateur/layout') ?>

<?= $this->section('contenu') ?>
<div class="row g-4">
  <div class="col-12 col-lg-5">
    <section class="card">
      <div class="card-body">
        <form action="<?= site_url('/operateur/operations/update/' . $operation['id']) ?>" method="post">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label for="type_operation_id" class="form-label">Type d'opération</label>
            <select class="form-select" id="type_operation_id" name="type_operation_id" required>
              <?php foreach (($typeOperations ?? []) as $type): ?>
                <option value="<?= esc($type['id']) ?>"
                  <?= (string) $type['id'] === (string) $operation['type_operation_id'] ? 'selected' : '' ?>>
                  <?= esc($type['nom']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="montant_min" class="form-label">Montant minimum (Ar)</label>
            <input type="number" class="form-control" id="montant_min" name="montant_min"
                   step="0.01" value="<?= esc(old('montant_min', $operation['montant_min'])) ?>" required>
          </div>

          <div class="mb-3">
            <label for="montant_max" class="form-label">Montant maximum (Ar)</label>
            <input type="number" class="form-control" id="montant_max" name="montant_max"
                   step="0.01" value="<?= esc(old('montant_max', $operation['montant_max'])) ?>" required>
          </div>

          <div class="mb-4">
            <label for="frais" class="form-label">Frais (Ar)</label>
            <input type="number" class="form-control" id="frais" name="frais"
                   step="0.01" value="<?= esc(old('frais', $operation['frais'])) ?>" required>
          </div>

          <div class="d-flex gap-3">
            <a href="/operateur/operations" class="btn btn-neutre">Annuler</a>
            <button type="submit" class="btn btn-principal flex-grow-1">Enregistrer les modifications</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>
<?= $this->endSection() ?>
