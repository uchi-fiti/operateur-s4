<?= $this->extend('client/layout') ?>

<?= $this->section('contenu') ?>
<div class="row g-4">
  <div class="col-12 col-lg-5">
    <section class="card carte-solde">
      <div class="card-body">
        <p class="solde-libelle">Votre solde</p>
        <p class="solde-montant"><?= number_format((float) $compte['solde'], 0, ',', '.') ?> Ar</p>
        <p class="solde-libelle">Votre Epargne</p>
        <p class="solde-montant"><?= number_format((float) $compte['valeur_epargne'], 0, ',', '.') ?> Ar</p>
        <p class="solde-note">
          Titulaire : <?= esc(trim($compte['prenom'] . ' ' . $compte['nom'])) ?>.
        </p>
      </div>
    </section>
  </div>
</div>
<?= $this->endSection() ?>
