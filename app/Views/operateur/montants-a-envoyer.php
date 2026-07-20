<?= $this->extend('operateur/layout') ?>

<?= $this->section('contenu') ?>
<section class="card">
  <div class="card-body">
    <?php if (empty($lignes)): ?>
      <p class="mb-0">Aucun opérateur partenaire enregistré.</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th scope="col">Préfixe opérateur</th>
              <th scope="col">Transferts</th>
              <th scope="col">Montants transférés</th>
              <th scope="col">Commissions dues</th>
              <th scope="col">Montant à envoyer</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($lignes as $ligne): ?>
              <tr>
                <td><?= esc($ligne['prefixe']) ?></td>
                <td><?= (int) $ligne['nombre'] ?></td>
                <td><?= number_format((float) $ligne['montants'], 0, ',', '.') ?> Ar</td>
                <td><?= number_format((float) $ligne['commissions'], 0, ',', '.') ?> Ar</td>
                <td><strong><?= number_format((float) $ligne['total'], 0, ',', '.') ?> Ar</strong></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"><strong>Total à envoyer</strong></td>
              <td><strong><?= number_format((float) $total, 0, ',', '.') ?> Ar</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
