<?= $this->extend('operateur/layout') ?>

<?= $this->section('tete') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<style>
  .bascule .btn { min-width: 200px; }
  .carte-total { border-top: 4px solid var(--orange); }
  .total-libelle {
    font-size: 14px; text-transform: uppercase; letter-spacing: 1px;
    color: #55607a; margin: 0;
  }
  .total-valeur {
    font-size: 44px; font-weight: 700; color: var(--marine); margin: 12px 0 4px;
  }
  .total-note { font-size: 13px; color: #6b7280; margin: 0; }
  .zone-graphique { max-width: 460px; margin: 0 auto; }
</style>
<?= $this->endSection() ?>

<?= $this->section('contenu') ?>

<div class="bascule btn-group mb-4" role="group" aria-label="Choix de la situation affichée">
  <button type="button" class="btn btn-principal" id="btnNotre" aria-pressed="true">Notre opérateur</button>
  <button type="button" class="btn btn-neutre" id="btnAutres" aria-pressed="false">Autres opérateurs</button>
</div>

<!-- ============ Vue : notre opérateur ============ -->
<div id="vueNotre">
  <div class="row g-4">
    <div class="col-12 col-lg-4">
      <section class="card carte-total h-100">
        <div class="card-body">
          <p class="total-libelle">Revenu total</p>
          <p class="total-valeur"><?= number_format((float) $revenuTotal, 0, ',', '.') ?> Ar</p>
          <p class="total-note">Somme des frais encaissés sur toutes les opérations.</p>
        </div>
      </section>
    </div>

    <div class="col-12 col-lg-8">
      <section class="card h-100">
        <div class="card-body">
          <h2 class="h5 mb-4">Revenus par type d'opération</h2>
          <?php if (empty($revenusParType)): ?>
            <p class="mb-0">Aucune opération enregistrée pour le moment.</p>
          <?php else: ?>
            <div class="zone-graphique">
              <canvas id="camembertTypes"></canvas>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>

<!-- ============ Vue : autres opérateurs ============ -->
<div id="vueAutres" hidden>
  <div class="row g-4">
    <div class="col-12 col-lg-4">
      <section class="card carte-total h-100">
        <div class="card-body">
          <p class="total-libelle">Gains des autres opérateurs</p>
          <p class="total-valeur"><?= number_format((float) $gainsAutresTotal, 0, ',', '.') ?> Ar</p>
          <p class="total-note">
            Commissions que nous leur devons sur les transferts sortants.
            Ce montant est une charge pour nous, pas un revenu.
          </p>
        </div>
      </section>
    </div>

    <div class="col-12 col-lg-8">
      <section class="card h-100">
        <div class="card-body">
          <h2 class="h5 mb-4">Répartition par opérateur (préfixe)</h2>
          <?php if (empty($gainsAutres)): ?>
            <p class="mb-0">Aucun transfert vers un autre opérateur pour le moment.</p>
          <?php else: ?>
            <div class="zone-graphique">
              <canvas id="camembertAutres"></canvas>
            </div>
          <?php endif; ?>
        </div>
      </section>
    </div>
  </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
  // La palette reprend les couleurs de l'application, completee par des teintes
  // derivees pour rester lisible quand il y a plus de trois parts.
  const couleurs =['#fca311', '#14213d', '#7a8aa8', '#c97f0d', '#4a5f8a', '#e5e5e5'];

  const optionsCamembert = (donnees) => ({
    type: 'doughnut',
    data: {
      labels: donnees.map((d) => d.libelle),
      datasets: [{
        data: donnees.map((d) => d.valeur),
        backgroundColor: donnees.map((d, i) => couleurs[i % couleurs.length]),
        borderColor: '#ffffff',
        borderWidth: 2
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { color: '#14213d', padding: 16 } },
        tooltip: {
          callbacks: {
            label: (item) => {
              const total = item.dataset.data.reduce((a, b) => a + b, 0);
              const part = total > 0 ? Math.round((item.parsed / total) * 100) : 0;
              return ' ' + item.label + ' : ' + item.parsed.toLocaleString('fr-FR') + ' Ar (' + part + ' %)';
            }
          }
        }
      }
    }
  });

  <?php if (! empty($revenusParType)): ?>
  new Chart(
    document.getElementById('camembertTypes'),
    optionsCamembert(<?= json_encode(array_map(static fn ($l) => [
        'libelle' => $l['libelle'],
        'valeur'  => $l['revenus'],
    ], $revenusParType), JSON_UNESCAPED_UNICODE) ?>)
  );
  <?php endif; ?>

  <?php if (! empty($gainsAutres)): ?>
  new Chart(
    document.getElementById('camembertAutres'),
    optionsCamembert(<?= json_encode(array_map(static fn ($l) => [
        'libelle' => 'Préfixe ' . $l['prefixe'],
        'valeur'  => $l['gains'],
    ], $gainsAutres), JSON_UNESCAPED_UNICODE) ?>)
  );
  <?php endif; ?>

  // Bascule entre les deux situations. Les deux vues sont deja rendues : le
  // basculement est instantane, sans rechargement ni requete.
  const btnNotre = document.getElementById('btnNotre');
  const btnAutres = document.getElementById('btnAutres');
  const vueNotre = document.getElementById('vueNotre');
  const vueAutres = document.getElementById('vueAutres');

  function afficher(vue) {
    const notre = vue === 'notre';

    vueNotre.hidden = !notre;
    vueAutres.hidden = notre;

    btnNotre.classList.toggle('btn-principal', notre);
    btnNotre.classList.toggle('btn-neutre', !notre);
    btnAutres.classList.toggle('btn-principal', !notre);
    btnAutres.classList.toggle('btn-neutre', notre);

    btnNotre.setAttribute('aria-pressed', notre ? 'true' : 'false');
    btnAutres.setAttribute('aria-pressed', notre ? 'false' : 'true');
  }

  btnNotre.addEventListener('click', () => afficher('notre'));
  btnAutres.addEventListener('click', () => afficher('autres'));
</script>
<?= $this->endSection() ?>
