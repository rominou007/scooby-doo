
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Gestionstock</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="home.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="users.php">Users</a>
        </li>
      </ul>
    </div>
    <li class="d-flex">
        <?php if (isset($_SESSION['username'])): ?>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        <?php else: ?>
            <a href="index.php" class="btn btn-primary mx-2">Login</a>
            <a href="register.php" class="btn btn-primary">Register</a>
        <?php endif; ?>
    </li>
  </div>
</nav>