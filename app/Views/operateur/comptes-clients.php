<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Comptes clients : espace opérateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/operateur/styles.css">
  <style>
    .carte-stat {
      border-top: 4px solid var(--orange);
    }

    .stat-libelle {
      font-size: 14px;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #55607a;
      margin: 0;
    }

    .stat-valeur {
      font-size: 44px;
      font-weight: 700;
      color: var(--marine);
      margin: 12px 0 4px;
    }

    .stat-note {
      font-size: 13px;
      color: #6b7280;
      margin: 0;
    }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace opérateur</p>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="/operateur/dashboard">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="/operateur/prefixes">Préfixes</a></li>
      <li class="nav-item"><a class="nav-link" href="/operateur/operations">Opérations</a></li>
      <li class="nav-item"><a class="nav-link active" href="/operateur/comptes-clients">Comptes clients</a></li>
    </ul>
    <div class="sidebar-pied">
      <a href="/operateur/login">Déconnexion</a>
    </div>
  </aside>

  <main class="contenu">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h3 mb-0">Comptes clients</h1>
        <p class="sous-titre">Vue d'ensemble des comptes ouverts sur la plateforme.</p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-12 col-md-6 col-lg-4">
        <section class="card carte-stat h-100">
          <div class="card-body">
            <p class="stat-libelle">Clients inscrits</p>
            <p class="stat-valeur">2 730</p>
            <p class="stat-note">Total des comptes créés depuis l'ouverture du service.</p>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>