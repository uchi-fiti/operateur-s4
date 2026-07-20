<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Opérations : espace opérateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/operateur/styles.css">
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace opérateur</p>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link" href="/operateur/dashboard">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="/operateur/prefixes">Préfixes</a></li>
      <li class="nav-item"><a class="nav-link active" href="/operateur/operations">Opérations</a></li>
      <li class="nav-item"><a class="nav-link" href="/operateur/comptes-clients">Comptes clients</a></li>
    </ul>
    <div class="sidebar-pied">
      <a href="/operateur/login">Déconnexion</a>
    </div>
  </aside>

  <main class="contenu">
    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h3 mb-0">Opérations</h1>
        <p class="sous-titre">Grille des frais appliqués selon le type d'opération.</p>
      </div>
      <button type="button" class="btn btn-principal" data-bs-toggle="modal" data-bs-target="#modalOperation">
        Ajouter une opération
      </button>
    </div>

    <section class="card mb-4">
      <div class="card-body">
        <form class="row g-3 align-items-end" id="formulaireType">
          <div class="col-12 col-md-5">
            <label for="typeOperation" class="form-label">Type d'opération</label>
            <select class="form-select" id="typeOperation" name="typeOperation">
              <?php foreach (($typeOperations ?? []) as $typeOperation): ?>
                <option value="<?= $typeOperation['id'] ?>"><?= esc($typeOperation['nom']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-principal">Afficher</button>
          </div>
        </form>
      </div>
    </section>

    <section class="card">
      <div class="card-body">
        <h2 class="h5 mb-4" id="titreTableau">Frais : dépôt</h2>
        <div class="table-responsive">
          <table class="table align-middle mb-0">
            <thead>
              <tr>
                <th scope="col">Type</th>
                <th scope="col">Montant min (Ar)</th>
                <th scope="col">Montant max (Ar)</th>
                <th scope="col">Frais (Ar)</th>
                <th scope="col">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($operations  as $operation): ?>
                <tr>
                  <td><?= esc($operation['type_nom']) ?></td>
                  <td><?= esc(number_format((float) $operation['montant_min'], 0, ',', ' ')) ?></td>
                  <td><?= esc(number_format((float) $operation['montant_max'], 0, ',', ' ')) ?></td>
                  <td><?= esc(number_format((float) $operation['frais'], 0, ',', ' ')) ?></td>
                  <td><a href="/operateur/operations/<?= $operation['id'] ?>/edit" class="btn btn-sm btn-outline-primary">Modifier</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </main>

  <div class="modal fade" id="modalOperation" tabindex="-1" aria-labelledby="titreModalOperation" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="/operateur/operations" method="post">
          <div class="modal-header">
            <h2 class="modal-title h5" id="titreModalOperation">Ajouter une opération</h2>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
          </div>

          <div class="modal-body">
            <div class="mb-3">
              <label for="type_operation_id" class="form-label">Type d'opération</label>
              <select class="form-select" id="type_operation_id" name="type_operation_id" required>
                <?php foreach (($typeOperations ?? []) as $typeOperation): ?>
                  <option value="<?= $typeOperation['id'] ?>"><?= esc($typeOperation['nom']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label for="montant_min" class="form-label">Montant minimum (Ar)</label>
              <input type="number" class="form-control" id="montant_min" name="montant_min"
                     min="0" step="100" placeholder="0" required>
            </div>

            <div class="mb-3">
              <label for="montant_max" class="form-label">Montant maximum (Ar)</label>
              <input type="number" class="form-control" id="montant_max" name="montant_max"
                     min="0" step="100" placeholder="2000" required>
            </div>

            <div>
              <label for="frais" class="form-label">Frais (Ar)</label>
              <input type="number" class="form-control" id="frais" name="frais"
                     min="0" step="10" placeholder="50" required>
              <div class="form-text">Frais prélevés sur cette tranche de montant.</div>
            </div>
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
