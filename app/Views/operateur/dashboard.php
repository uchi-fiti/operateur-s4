<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord : espace opérateur</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/operateur/styles.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">Mobile<span>Money</span></div>
    <p class="sidebar-role">Espace opérateur</p>
    <ul class="nav flex-column">
      <li class="nav-item"><a class="nav-link active" href="/operateur/dashboard">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="/operateur/prefixes">Préfixes</a></li>
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
        <h1 class="h3 mb-0">Tableau de bord</h1>
        <p class="sous-titre">Suivi des revenus générés par les frais d'opération.</p>
      </div>
    </div>

    <section class="card">
      <div class="card-body">
        <h2 class="h5 mb-4">Revenus par jour (7 derniers jours)</h2>
        <canvas id="graphiqueRevenus" height="110"></canvas>
      </div>
    </section>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    const contexte = document.getElementById('graphiqueRevenus');

    new Chart(contexte, {
      type: 'bar',
      data: {
        labels: ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'],
        datasets: [{
          label: 'Revenus (Ar)',
          data: [124000, 98000, 156000, 143000, 187000, 205000, 92000],
          backgroundColor: '#fca311',
          borderColor: '#14213d',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { labels: { color: '#14213d' } },
          tooltip: {
            callbacks: {
              label: (item) => item.parsed.y.toLocaleString('fr-FR') + ' Ar'
            }
          }
        },
        scales: {
          x: { ticks: { color: '#14213d' }, grid: { display: false } },
          y: {
            beginAtZero: true,
            ticks: {
              color: '#14213d',
              callback: (valeur) => valeur.toLocaleString('fr-FR') + ' Ar'
            },
            grid: { color: '#e5e5e5' }
          }
        }
      }
    });
  </script>
</body>
</html>