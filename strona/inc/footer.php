</main>
<footer class="footer" id="kontakt">
  <div class="wrap footer__grid">
    <div>
      <div class="brand brand--footer">
        <img src="assets/img/unia-herb.svg" alt="" width="56" height="56">
        <span><b>Unia</b> Ząbkowice<small>Biało-niebiescy od <?= $KLUB['rok'] ?></small></span>
      </div>
      <p class="muted"><?= e($KLUB['pelna_nazwa']) ?><br>KRS <?= e($KLUB['krs']) ?></p>
    </div>
    <div>
      <h4>Kontakt</h4>
      <p><?= e($KLUB['adres']) ?><br>
      tel. <a href="tel:+48<?= str_replace(' ', '', $KLUB['telefon']) ?>"><?= e($KLUB['telefon']) ?></a><br>
      <a href="mailto:<?= e($KLUB['email']) ?>"><?= e($KLUB['email']) ?></a></p>
    </div>
    <div>
      <h4>Na skróty</h4>
      <p><a href="rozgrywki.php">Tabela i terminarz</a><br>
      <a href="klub.php">Historia klubu</a><br>
      <a href="index.php#akademia">Akademia – zapisy</a><br>
      <a href="index.php#partnerzy">Zostań partnerem</a></p>
    </div>
    <div>
      <h4>Śledź nas</h4>
      <p><a href="<?= e($KLUB['social']['facebook']) ?>" target="_blank" rel="noopener">Facebook – KS Unia Ząbkowice</a><br>
      <a href="<?= e($KLUB['social']['instagram']) ?>" target="_blank" rel="noopener">Instagram – @uniazabkowice1922</a><br>
      <a href="<?= e($KLUB['social']['akademia']) ?>" target="_blank" rel="noopener">Facebook – Akademia</a></p>
    </div>
  </div>
  <div class="wrap footer__bottom">
    <span>© <?= date('Y') ?> KS Unia Ząbkowice</span>
    <span>Prototyp strony · tabela i terminarz: <a href="https://www.90minut.pl" target="_blank" rel="noopener">90minut.pl</a></span>
  </div>
</footer>
<script src="assets/js/main.js?v=<?= filemtime(__DIR__ . '/../assets/js/main.js') ?>"></script>
</body>
</html>
