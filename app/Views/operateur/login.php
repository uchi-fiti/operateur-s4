<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion opérateur : Mobile Money</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <main class="page-centree">
    <div class="boite">
      <h1 class="h4">Connexion opérateur</h1>
      <p class="sous-titre mb-4">Accédez à votre espace d'administration.</p>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger" role="alert">
          <?= session()->getFlashdata('error') ?>
        </div>
      <?php endif; ?>

      <form action="/operateur/login" method="post">
        <div class="mb-3">
          <label for="utilisateur" class="form-label">Nom d'utilisateur</label>
          <input type="text" class="form-control" id="utilisateur" name="utilisateur"
                 placeholder="ex : rakoto.jean" value="admin_telma" required>
        </div>

        <div class="mb-4">
          <label for="motdepasse" class="form-label">Mot de passe</label>
          <input type="password" class="form-control" id="motdepasse" name="motdepasse"
                 placeholder="••••••••" value="telma123" required>
        </div>

        <button type="submit" class="btn btn-principal w-100">Se connecter</button>
      </form>

      <a class="lien-retour" href="/">Retour au choix de l'espace</a>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>