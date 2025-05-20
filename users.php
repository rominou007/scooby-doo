<?php
    session_start();
    require("db.php");
    if(!isset($_SESSION['user_id'])){
        header("location: home2.php");
        exit;
    } elseif($_SESSION['role'] != 2){
        header("location: home.php");
        exit;
    }
    $listUsers = $pdo->query("SELECT * FROM user")->fetchAll();


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homme</title>
    <?php include("link.php"); ?>
</head>
<?php include("navbar.php"); ?>
<body>

  <h1 class="text-center mt-5">Tableau des utilisateurs</h1>

  <table class="container mt-5 table table-striped  table-hover">
        <thead>
          <tr>
            <th scope="col">#</th>
            <th scope="col">Mail</th>
            <th scope="col">Prenom</th>
            <th scope="col">Nom</th>
            <th scope="col">Access</th>
            <th scope="col">Action</th>
          </tr>
        </thead>
        <tbody>

        <?php foreach($listUsers as $user): ?>
          <tr>
            <th scope="row"><?php echo $user["id_user"] ?></th>
            <td><?php echo $user["email"] ?></td>
            <td><?php echo $user["prenom"] ?></td>
            <td><?php echo $user["nom"] ?></td>
            <td>
            <?php if ($user["role"] == 2): ?>
                <span class="badge text-bg-danger">admin</span>
            <?php else: ?>
                <span class="badge text-bg-primary">étudiant</span>
            <?php endif; ?>
            </td>
            <td>
              <!-- Button trigger modal -->
              <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#staticBackdrop<?php echo $user["id_user"] ?>">
                <i class="fa-solid fa-pen-to-square"></i>
              </button>

              <a href="delete_user.php?id=<?php echo $user["id_user"] ?>" class="btn btn-danger">
                <i class="fa-solid fa-trash"></i>
              </a>
            </td>
          </tr>

          <!-- Modal -->
          <div class="modal fade" id="staticBackdrop<?php echo $user["id_user"] ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h1 class="modal-title fs-5" id="staticBackdropLabel">Modifier l'utilisateur</h1>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form action="edit_user.php" method="post">

                  <input type="hidden" name="id_user" value="<?php echo $user["id_user"] ?>">

                  <label for="prenom" class="form-label">Prénom</label>
                  <input required type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $user["prenom"] ?>">
                  <label for="nom" class="form-label">Nom</label>
                  <input required type="text" class="form-control" id="nom" name="nom" value="<?php echo $user["nom"] ?>">
                  <label for="email" class="form-label">Mail</label>
                  <input required type="email" class="form-control" id="email" name="email" value="<?php echo $user["email"] ?>">

                  <label for="acces" class="form-label">Access</label>
                  <select name="acces" id="acces" class="form-control">
                      <option value="0" <?php if($user["role"] == 0) echo "selected"?>>étudiant</option>
                      <option value="1" <?php if($user["role"] == 1) echo "selected"?>>Prof</option>
                      <option value="2" <?php if($user["role"] == 2) echo "selected"?>>Admin</option>
                  </select>
                    
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                  <input type="submit" value="Modifier" class="btn btn-primary">
                  
                  </form>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>

        </tbody>
  </table>

</body>
</html>