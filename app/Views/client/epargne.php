<?= $this->extend('client/layout') ?>

<?= $this->section('contenu') ?>
<div class="row g-4">
  <div class="col-12 col-lg-5">
    <section class="card">
      <div class="card-body">
        <form action="<?= base_url('client/epargne/update') ?>" method="get">
          <?= csrf_field() ?>
          <div class="mb-4">
            <label for="montant" class="form-label">Pourcentage de transfert vers l'epargne</label>
            <input type="number" class="form-control" id="montant" name="pctg"
                   min="1" step="1" placeholder="50"
                   value="<?= esc(old('montant')) ?>" required>
          </div>

          <button type="submit" class="btn btn-principal w-100">Confirmer</button>
        </form>
      </div>
    </section>
  </div>
</div>
<?= $this->endSection() ?>
