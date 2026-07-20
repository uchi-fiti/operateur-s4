<?= $this->extend('operateur/layout') ?>

<?= $this->section('contenu') ?>
<div class="row g-4">
  <div class="col-12 col-lg-5">
    <section class="card">
      <div class="card-body">
        <form action="/operateur/prefixes-autres/ajouter" method="post">
          <?= csrf_field() ?>

          <div class="mb-3">
            <label for="prefixe" class="form-label">Préfixe valable</label>
            <input type="text" class="form-control" id="prefixe" name="prefixe"
                   inputmode="numeric" pattern="[0-9]{3}" maxlength="3" placeholder="032"
                   value="<?= esc(old('prefixe')) ?>" required>
            <div class="form-text">3 chiffres. Ne doit pas être un de vos propres préfixes.</div>
          </div>

          <div class="mb-4">
            <label for="pct_commission" class="form-label">Commission transfert (%)</label>
            <input type="number" class="form-control" id="pct_commission" name="pct_commission"
                   min="0" max="100" step="0.01" placeholder="2"
                   value="<?= esc(old('pct_commission')) ?>" required>
            <div class="form-text">
              Pourcentage du montant transféré que vous reverserez à cet opérateur.
              Le client le paie en plus des frais.
            </div>
          </div>

          <div class="d-flex gap-3">
            <a class="btn btn-neutre" href="/operateur/prefixes-autres">Annuler</a>
            <button type="submit" class="btn btn-principal flex-grow-1">Valider</button>
          </div>
        </form>
      </div>
    </section>
  </div>
</div>
<?= $this->endSection() ?>
