<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($titre ?? 'Espace client') ?> : Mobile Money</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('client/styles.css') ?>">
</head>
<body>
  <?php $actif = $actif ?? ''; ?>
  <aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace client</p>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'solde' ? 'active' : '' ?>" href="<?= base_url('client/solde') ?>">Solde</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'depot' ? 'active' : '' ?>" href="<?= base_url('client/depot') ?>">Dépôt</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'retrait' ? 'active' : '' ?>" href="<?= base_url('client/retrait') ?>">Retrait</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'transfert' ? 'active' : '' ?>" href="<?= base_url('client/transfert') ?>">Transfert</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'envoi-multiple' ? 'active' : '' ?>" href="<?= base_url('client/envoi-multiple') ?>">Envoi multiple</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'historique' ? 'active' : '' ?>" href="<?= base_url('client/historique') ?>">Historique des transactions</a>
      </li>
    </ul>
    <div class="sidebar-pied">
      <a href="<?= base_url('client/deconnexion') ?>">Déconnexion</a>
    </div>
  </aside>

  <main class="contenu">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h3 mb-0"><?= esc($titre ?? '') ?></h1>
        <?php if (! empty($sousTitre)): ?>
          <p class="sous-titre"><?= esc($sousTitre) ?></p>
        <?php endif; ?>
      </div>
    </div>

    <?php if (session('erreur')): ?>
      <div class="alert alert-danger" role="alert"><?= esc(session('erreur')) ?></div>
    <?php endif; ?>

    <?php if (session('succes')): ?>
      <div class="alert alert-success" role="alert"><?= esc(session('succes')) ?></div>
    <?php endif; ?>

    <?= $this->renderSection('contenu') ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
