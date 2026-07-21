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
            <th scope="col">Valeur</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($promotions)): ?>
            <tr>
              <td colspan="3" class="text-center text-muted py-4">Aucune promotion trouvée pour cet opérateur.</td>
            </tr>
          <?php endif; ?>

          <?php foreach (($promotions ?? []) as $promotion): ?>
            <tr>
              <td><?= esc($promotion['valeur'] * 100 . '%') ?></td>
              <td>
                <a href="<?= site_url('operateur/promotion/modify') ?>">
                    <button type="button" class="btn btn-neutre btn-modifier">
                        Modifier la commission
                    </button>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<?= $this->endSection() ?>
