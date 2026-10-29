<?php
// Wspólne fragmenty HTML: tabela ligowa i wiersz meczu.

function widok_tabela(array $tabela, bool $kompakt = false): void { ?>
  <div class="table-wrap">
  <table class="ltable<?= $kompakt ? ' ltable--compact' : '' ?>">
    <thead><tr>
      <th class="c">#</th><th class="l">Drużyna</th><th>M</th>
      <?php if (!$kompakt): ?><th>W</th><th>R</th><th>P</th><?php endif; ?>
      <th>Bramki</th><th>Pkt</th>
    </tr></thead>
    <tbody>
    <?php foreach ($tabela as $t):
      $nasz = $t['nazwa'] === NASZ_KLUB;
      $strefa = $t['poz'] === 1 ? ' z-awans' : ($t['poz'] >= count($tabela) - 2 ? ' z-spadek' : ''); ?>
      <tr class="<?= $nasz ? 'us' : '' ?><?= $strefa ?>">
        <td class="c"><span class="poz"><?= $t['poz'] ?></span></td>
        <td class="l team"><div class="team__in"><?= herb($t['slug'], $t['nazwa']) ?><span><?= e($t['nazwa']) ?></span></div></td>
        <td><?= $t['m'] ?></td>
        <?php if (!$kompakt): ?><td><?= $t['w'] ?></td><td><?= $t['r'] ?></td><td><?= $t['p'] ?></td><?php endif; ?>
        <td class="gl"><?= $t['bz'] ?>:<?= $t['bs'] ?></td>
        <td class="pkt"><?= $t['pkt'] ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
<?php }

// Lista meczów: pierwsze $widoczne od razu, reszta po kliknięciu "Pokaż wszystkie".
function widok_lista_meczow(array $mecze, int $widoczne, string $pokazTekst): void { ?>
  <ul class="matches" data-expandable>
    <?php foreach ($mecze as $i => $m) {
        ob_start(); widok_mecz($m); $li = ob_get_clean();
        echo $i >= $widoczne ? str_replace('<li class="', '<li hidden class="extra ', $li) : $li;
    } ?>
  </ul>
  <?php if (count($mecze) > $widoczne): ?>
    <button class="btn btn--ghost-dark btn--block expand" aria-expanded="false"
            data-more="<?= e($pokazTekst) ?> (<?= count($mecze) ?>)" data-less="Zwiń listę"><?= e($pokazTekst) ?> (<?= count($mecze) ?>)</button>
  <?php endif;
}

function widok_mecz(array $m): void {
    $wynik = $m['wynik'];
    $rez = $wynik ? liga_rezultat($m) : null; ?>
  <li class="match<?= $rez ? ' match--' . strtolower($rez) : '' ?>">
    <div class="match__meta">
      <span>Kolejka <?= $m['kolejka'] ?></span>
      <span><?= !empty($m['przelozony']) ? 'mecz przełożony – nowy termin wkrótce' : e(data_pl($m['data'])) ?></span>
    </div>
    <div class="match__row">
      <span class="match__team<?= $m['gosp'] === NASZ_KLUB ? ' us' : '' ?>"><?= e($m['gosp']) ?><?= herb($m['gosp_slug'], $m['gosp'], 'herb herb--sm') ?></span>
      <span class="match__score"><?= $wynik ? $wynik[0] . ':' . $wynik[1] : 'vs' ?></span>
      <span class="match__team match__team--away<?= $m['gosc'] === NASZ_KLUB ? ' us' : '' ?>"><?= herb($m['gosc_slug'], $m['gosc'], 'herb herb--sm') ?><?= e($m['gosc']) ?></span>
    </div>
    <?php if ($rez): ?><span class="badge badge--<?= strtolower($rez) ?>" title="<?= ['W'=>'Wygrana','R'=>'Remis','P'=>'Porażka'][$rez] ?>"><?= $rez ?></span><?php endif; ?>
  </li>
<?php }
