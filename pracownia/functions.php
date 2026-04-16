<?php
session_start();
function printnavbar() {
echo
'<nav class="navbar navbar-expand-lg bg-primary" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand text-white" href="https://makison.uk/zawodowe/pracownia/">Pracownicy</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="etaty.php">Etaty</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="zespoly.php">Zespoły</a>
        </li>
        <li class="nav-item"><a class="nav-link" aria-current="page" href="https://makison.uk">Powrót do strony głównej MAK\'a</a></li>
      </ul>
    </div>
  </div>
</nav>';
}
?>

