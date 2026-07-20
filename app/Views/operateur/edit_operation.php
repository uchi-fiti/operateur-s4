<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une opération</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/operateur/styles.css">
</head>

<body>

<aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace opérateur</p>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="/operateur/dashboard">Dashboard</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="/operateur/prefixes">Préfixes</a>
        </li>

        <li class="nav-item">
            <a class="nav-link active" href="/operateur/operations">Opérations</a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="/operateur/comptes-clients">Comptes clients</a>
        </li>
    </ul>

    <div class="sidebar-pied">
        <a href="/operateur/login">Déconnexion</a>
    </div>
</aside>

<main class="contenu">

    <div class="mb-4">
        <h1 class="h3 mb-0">Modifier un barème</h1>
        <p class="sous-titre">
            Modifier les informations d'un barème de frais.
        </p>
    </div>

    <section class="card">

        <div class="card-body">

            <form action="<?= site_url('/operateur/operations/update/'.$operation['id']) ?>" method="post">

                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label">
                        Type d'opération
                    </label>

                    <select
                            class="form-select"
                            name="type_operation_id"
                            required>

                        <?php foreach($typeOperations as $type): ?>

                            <option
                                    value="<?= $type['id'] ?>"
                                <?= $type['id']==$operation['type_operation_id'] ? 'selected' : '' ?>>

                                <?= esc($type['nom']) ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Montant minimum (Ar)
                    </label>

                    <input
                            class="form-control"
                            type="number"
                            step="0.01"
                            name="montant_min"
                            value="<?= $operation['montant_min'] ?>"
                            required>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Montant maximum (Ar)
                    </label>

                    <input
                            class="form-control"
                            type="number"
                            step="0.01"
                            name="montant_max"
                            value="<?= $operation['montant_max'] ?>"
                            required>

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Frais (Ar)
                    </label>

                    <input
                            class="form-control"
                            type="number"
                            step="0.01"
                            name="frais"
                            value="<?= $operation['frais'] ?>"
                            required>

                </div>

                <div class="d-flex justify-content-between">

                    <a href="/operateur/operations"
                       class="btn btn-neutre">
                        Annuler
                    </a>

                    <button
                            type="submit"
                            class="btn btn-principal">

                        Enregistrer les modifications

                    </button>

                </div>

            </form>

        </div>

    </section>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>