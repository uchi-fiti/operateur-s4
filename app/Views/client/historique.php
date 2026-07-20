<?= $this->extend('client/layout') ?>

<?= $this->section('contenu') ?>
<section class="card">
  <div class="card-body">
    <?php if (empty($operations)): ?>
      <p class="mb-0">Aucune opération pour le moment.</p>
    <?php else: ?>
      <ul class="list-group list-group-flush liste-historique">
        <?php foreach ($operations as $operation): ?>
          <?php
            $montant = number_format((float) $operation['montant'], 0, ',', '.');
            // Le client a paye les frais ET la commission : on lui affiche le
            // total preleve, la repartition entre operateurs ne le concerne pas.
            $frais = number_format((float) $operation['frais'] + (float) $operation['commission'], 0, ',', '.');
            $recu  = (int) $operation['compte_destination'] === $compteId;
          ?>
          <li class="list-group-item">
            <p class="historique-phrase">
              <?php if ($operation['type_nom'] === 'Depot'): ?>
                <strong>Dépôt</strong> de <?= $montant ?> Ar. Frais : <?= $frais ?> Ar

              <?php elseif ($operation['type_nom'] === 'Retrait'): ?>
                <strong>Retrait</strong> de <?= $montant ?> Ar. Frais : <?= $frais ?> Ar

              <?php elseif ($recu): ?>
                <?php // Transfert entrant : pas de frais a la charge du destinataire. ?>
                Vous avez reçu <?= $montant ?> Ar de la part de
                <?= esc($operation['telephone_source'] ?? 'un compte inconnu') ?>.

              <?php else: ?>
                <strong>Transfert</strong> de <?= $montant ?> Ar vers
                <?= esc($operation['telephone_destination'] ?? 'un compte inconnu') ?>.
                Frais : <?= $frais ?> Ar
              <?php endif; ?>
            </p>
            <?php $horodatage = strtotime($operation['date_operation']); ?>
            <span class="historique-date">
              <?= esc(date('d/m/Y', $horodatage) . ' à ' . date('H\hi', $horodatage)) ?>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>
</section>
<?= $this->endSection() ?>
