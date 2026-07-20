<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Code secret : Mobile Money</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('client/styles.css') ?>">
</head>
<body>
  <main class="page-centree">
    <div class="boite">
      <h1 class="h4">Code secret</h1>
      <p class="sous-titre mb-4">
        Compte <?= esc($telephone) ?>. Saisissez votre code secret à 4 chiffres.
      </p>

      <?php if (session('erreur')): ?>
        <div class="alert alert-danger" role="alert"><?= esc(session('erreur')) ?></div>
      <?php endif; ?>

      <form action="<?= base_url('client/code-secret') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-4">
          <label for="code" class="form-label">Code secret</label>
          <input type="password" class="form-control" id="code" name="code"
                 inputmode="numeric" pattern="[0-9]{4}" maxlength="4"
                 placeholder="••••" autocomplete="off" required autofocus>
          <div class="form-text">4 chiffres.</div>
        </div>

        <button type="submit" class="btn btn-principal w-100">Valider</button>
      </form>

      <a class="lien-retour" href="<?= base_url('client/login') ?>">Modifier le numéro de téléphone</a>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
