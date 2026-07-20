<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion client : Mobile Money</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('client/styles.css') ?>">
</head>
<body>
  <main class="page-centree">
    <div class="boite">
      <h1 class="h4">Connexion client</h1>
      <p class="sous-titre mb-4">Saisissez votre numéro de téléphone pour continuer.</p>

      <?php if (session('erreur')): ?>
        <div class="alert alert-danger" role="alert"><?= esc(session('erreur')) ?></div>
      <?php endif; ?>

      <form action="<?= base_url('client/login') ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-4">
          <label for="telephone" class="form-label">Numéro de téléphone</label>
          <input type="tel" class="form-control" id="telephone" name="telephone"
                 inputmode="numeric" pattern="[0-9]{10}" maxlength="10"
                 placeholder="0340100001" value="<?= esc(old('telephone')) ?>" required>
          <div class="form-text">10 chiffres, sans espace.</div>
        </div>

        <button type="submit" class="btn btn-principal w-100">Continuer</button>
      </form>

      <a class="lien-retour" href="<?= base_url('/') ?>">Retour au choix de l'espace</a>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
