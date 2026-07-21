<?= $this->extend('operateur/layout') ?>

<?= $this->section('tete') ?>
<style>
  .carte-stat { border-top: 4px solid var(--orange); }
  .stat-libelle {
    font-size: 14px; text-transform: uppercase; letter-spacing: 1px;
    color: #55607a; margin: 0;
  }
  .stat-valeur {
    font-size: 44px; font-weight: 700; color: var(--marine); margin: 12px 0 4px;
  }
  .stat-note { font-size: 13px; color: #6b7280; margin: 0; }
</style>
<?= $this->endSection() ?>

<?= $this->section('contenu') ?>
<?php
  $total = array_sum(array_map(static fn ($l) => (int) $l['nombre_comptes'], $results ?? []));
?>

<div class="row g-4">
  <div class="col-12 col-md-6 col-lg-4">
    <section class="card carte-stat h-100">
      <div class="card-body">
        <p class="stat-libelle">Clients inscrits</p>
        <p class="stat-valeur"><?= number_format($total, 0, ',', ' ') ?></p>
        <p class="stat-note">Tous préfixes confondus, depuis l'ouverture du service.</p>
      </div>
    </section>
  </div>

  <?php foreach (($results ?? []) as $result): ?>
    <div class="col-12 col-md-6 col-lg-4">
      <section class="card carte-stat h-100">
        <div class="card-body">
          <p class="stat-libelle">Préfixe <?= esc($result['prefixe']) ?></p>
          <p class="stat-valeur"><?= number_format((int) $result['nombre_comptes'], 0, ',', ' ') ?></p>
          <p class="stat-note">Comptes ouverts sur ce préfixe.</p>
        </div>
      </section>
    </div>
  <?php endforeach; ?>
</div>
<?= $this->endSection() ?>
