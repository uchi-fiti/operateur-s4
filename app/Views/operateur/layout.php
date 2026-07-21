<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= esc($titre ?? 'Espace opérateur') ?> : Mobile Money</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/operateur/styles.css">
  <?= $this->renderSection('tete') ?>
</head>
<body>
  <?php $actif = $actif ?? ''; ?>
  <aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace opérateur</p>
    <ul class="nav flex-column">
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'dashboard' ? 'active' : '' ?>" href="/operateur/dashboard">Dashboard</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'prefixes' ? 'active' : '' ?>" href="/operateur/prefixes">Vos préfixes</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'prefixes-autres' ? 'active' : '' ?>" href="/operateur/prefixes-autres">Préfixes des autres opérateurs</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'operations' ? 'active' : '' ?>" href="/operateur/operations">Opérations</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'montants-a-envoyer' ? 'active' : '' ?>" href="/operateur/montants-a-envoyer">Montants à envoyer</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'comptes-clients' ? 'active' : '' ?>" href="/operateur/comptes-clients">Comptes clients</a>
      </li>
      <li class="nav-item">
        <a class="nav-link <?= $actif === 'promotion' ? 'active' : '' ?>" href="/operateur/promotions">Promotions</a>
      </li>
    </ul>
    <div class="sidebar-pied">
      <a href="/operateur/login">Déconnexion</a>
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
      <?= $this->renderSection('actions') ?>
    </div>

    <?php
      // Les deux conventions de nommage coexistent dans le projet : on accepte
      // les cles francaises et anglaises pour qu'aucun message ne soit perdu.
      $messageErreur = session('error') ?? session('erreur');
      $messageSucces = session('success') ?? session('succes');
    ?>

    <?php if ($messageErreur): ?>
      <div class="alert alert-danger" role="alert"><?= esc($messageErreur) ?></div>
    <?php endif; ?>

    <?php if ($messageSucces): ?>
      <div class="alert alert-success" role="alert"><?= esc($messageSucces) ?></div>
    <?php endif; ?>

    <?php if (session('errors')): ?>
      <div class="alert alert-danger" role="alert">
        <ul class="mb-0">
          <?php foreach ((array) session('errors') as $erreurValidation): ?>
            <li><?= esc($erreurValidation) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <?= $this->renderSection('contenu') ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?= $this->renderSection('scripts') ?>
</body>
</html>
