<?= $this->extend('client/layout') ?>

<?= $this->section('contenu') ?>
<div class="row g-4">
  <div class="col-12 col-lg-5">
    <section class="card">
      <div class="card-body">
        <form action="<?= base_url('client/retrait') ?>" method="post">
          <?= csrf_field() ?>
          <div class="mb-4">
            <label for="montant" class="form-label">Montant à retirer (Ar)</label>
            <input type="number" class="form-control" id="montant" name="montant"
                   min="1" step="1" placeholder="12000"
                   value="<?= esc(old('montant')) ?>" required>
            <div class="form-text">
              Solde disponible : <?= number_format((float) $compte['solde'], 0, ',', '.') ?> Ar.
              Les frais sont prélevés en plus du montant retiré.
            </div>
          </div>

          <button type="submit" class="btn btn-principal w-100">Retirer</button>
        </form>
      </div>
    </section>
  </div>
</div>
<?= $this->endSection() ?>
