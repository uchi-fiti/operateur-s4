<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Préfixes : espace opérateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/operateur/styles.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace opérateur</p>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="/operateur/dashboard">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link active" href="/operateur/prefixes">Préfixes</a></li>
      <li class="nav-item"><a class="nav-link" href="/operateur/operations">Opérations</a></li>
      <li class="nav-item"><a class="nav-link" href="/operateur/comptes-clients">Comptes clients</a></li>
    </ul>
    <div class="sidebar-pied">
      <a href="/operateur/login">Déconnexion</a>
    </div>
  </aside>

  <main class="contenu">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h3 mb-0">Préfixes</h1>
        <p class="sous-titre">Liste des préfixes téléphoniques acceptés par la plateforme.</p>
      </div>
    </div>

    <section class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th scope="col">Préfixe</th>
                <th scope="col">Date d'ajout</th>
                <th scope="col">Nombre de comptes</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>034</td>
                <td>12/01/2026</td>
                <td>1 248</td>
              </tr>
              <tr>
                <td>032</td>
                <td>12/01/2026</td>
                <td>876</td>
              </tr>
              <tr>
                <td>033</td>
                <td>04/02/2026</td>
                <td>512</td>
              </tr>
              <tr>
                <td>038</td>
                <td>17/03/2026</td>
                <td>94</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <div class="modal fade" id="modalPrefixe" tabindex="-1" aria-labelledby="titreModalPrefixe" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="/operateur/prefixes" method="get">
          <div class="modal-header">
            <h2 class="modal-title h5" id="titreModalPrefixe">Ajouter un préfixe</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>

          <div class="modal-body">
            <label for="prefixe" class="form-label">Préfixe</label>
            <input type="text" class="form-control" id="prefixe" name="prefixe"
                   inputmode="numeric" pattern="[0-9]{3}" maxlength="3" placeholder="034" required>
            <div class="form-text">3 chiffres exactement.</div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-neutre" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-principal">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html><!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Préfixes : espace opérateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace opérateur</p>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="dashboard.html">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link active" href="prefixes.html">Préfixes</a></li>
      <li class="nav-item"><a class="nav-link" href="operations.html">Opérations</a></li>
      <li class="nav-item"><a class="nav-link" href="comptes-clients.html">Comptes clients</a></li>
    </ul>
    <div class="sidebar-pied">
      <a href="login.html">Déconnexion</a>
    </div>
  </aside>

  <main class="contenu">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h3 mb-0">Préfixes</h1>
        <p class="sous-titre">Liste des préfixes téléphoniques acceptés par la plateforme.</p>
      </div>
      <button type="button" class="btn btn-principal" data-bs-toggle="modal" data-bs-target="#modalPrefixe">
        Ajouter un préfixe
      </button>
    </div>

    <section class="card">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th scope="col">Préfixe</th>
                <th scope="col">Date d'ajout</th>
                <th scope="col">Nombre de comptes</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>034</td>
                <td>12/01/2026</td>
                <td>1 248</td>
              </tr>
              <tr>
                <td>032</td>
                <td>12/01/2026</td>
                <td>876</td>
              </tr>
              <tr>
                <td>033</td>
                <td>04/02/2026</td>
                <td>512</td>
              </tr>
              <tr>
                <td>038</td>
                <td>17/03/2026</td>
                <td>94</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <div class="modal fade" id="modalPrefixe" tabindex="-1" aria-labelledby="titreModalPrefixe" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="prefixes.html" method="get">
          <div class="modal-header">
            <h2 class="modal-title h5" id="titreModalPrefixe">Ajouter un préfixe</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>

          <div class="modal-body">
            <label for="prefixe" class="form-label">Préfixe</label>
            <input type="text" class="form-control" id="prefixe" name="prefixe"
                   inputmode="numeric" pattern="[0-9]{3}" maxlength="3" placeholder="034" required>
            <div class="form-text">3 chiffres exactement.</div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn btn-neutre" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-principal">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
