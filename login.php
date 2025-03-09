<?php
session_start();
require_once 'back-end/connect.php';
$errorMessage = '';
$errorTitle = '';
$successTitle = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!isset($pdo)) {
    $errorMessage = "Échec de la connexion à la base de données.";
  } else {
    $email = filter_var($_POST['Email'], FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['Password']);
    if (str_starts_with($email, "a-")) {
      $email = substr($email, 2);
      try {
        $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE Email = :email AND Statut = 'Actif' AND lower(Profil) ='adm' ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['Password'])) {
          $user_id = $user['ID_User'];
          $stmt = $pdo->prepare("SELECT * FROM administration WHERE ID_User = :user_id ");
          $stmt->bindParam(':user_id', $user_id);
          $stmt->execute();
          $admin = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!$admin) {
            $errorMessage = "Administrateur non trouvé.";
            exit;
          }
          echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Connexion réussie !',
                            showConfirmButton: false,
                            timer: 9000
                        });
                      </script>";
          try {
            date_default_timezone_set('Africa/Casablanca');
            $currentDateTime = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE utilisateur SET Derniere_Connexion = :created_at WHERE ID_User = :user_id AND lower(Profil) ='adm' ");
            $stmt->execute([
              ':created_at' => $currentDateTime,
              ':user_id' => $user['ID_User']
            ]);
          } catch (PDOException $e) {
            $errorMessage = "Erreur : " . $e->getMessage();
          }
          $_SESSION['user_id'] = $user['ID_User'];
          $_SESSION['admin_id'] = $admin['ID_Admin'];
          $_SESSION['role'] = "admin";
          header("Location:admin/Acceuil.php");
          exit();
        } else {
          $errorMessage = "Mot de passe ou email incorrect.";
        }
      } catch (PDOException $e) {
        $errorMessage = "Erreur : " . $e->getMessage();
      }
    } else {
      try {
        $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE Email = :email AND Statut = 'Actif' AND lower(Profil) ='etu' ");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['Password'])) {
          $user_id = $user['ID_User'];

          $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE ID_Utilisateur = :user_id");
          $stmt->bindParam(':user_id', $user_id);
          $stmt->execute();
          $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
          if (!$etudiant) {
            $errorMessage = "Étudiant non trouvé.";
            exit;
          }
          echo "<script>
                        Swal.fire({
                            position: 'center',
                            icon: 'success',
                            title: 'Connexion réussie !',
                            showConfirmButton: false,
                            timer: 9000
                        });
                      </script>";
          try {
            date_default_timezone_set('Africa/Casablanca');
            $currentDateTime = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE utilisateur SET Derniere_Connexion = :created_at WHERE ID_User = :user_id");
            $stmt->execute([
              ':created_at' => $currentDateTime,
              ':user_id' =>  $user_id
            ]);
          } catch (PDOException $e) {
            $errorMessage = "Erreur : " . $e->getMessage();
          }
          $_SESSION['user_id'] =  $user_id;
          $_SESSION['id_etudiant'] = $etudiant['ID_Etudiant'];
          $_SESSION['role'] = "etudiant";
          header("Location:/etudiant/Acceuil.php");
          exit();
        } else {
          $errorMessage = "Mot de passe ou email incorrect.";
        }
      } catch (PDOException $e) {
        $errorMessage = "Erreur : " . $e->getMessage();
      }
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>ENSEM Scolarité</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <div class="flex flex-col md:flex-row h-screen">
    <div class="w-full flex items-center justify-center p-6">
      <div class="w-full max-w-md">
        <div class="mb-8 text-center">
          <img src="/public/images/logo_ensem.jpg" alt="ENSEM Logo" class="mx-auto mb-4 w-60 h-24" />
        </div>

        <h2 class="text-2xl font-semibold text-gray-700 mb-6 text-center">
          Se connecter sur ENSEM SCOLARITÉ
        </h2>

        <!-- Show error message -->
        <?php if (!empty($errorMessage)) : ?>
          <script>
            Swal.fire({
              position: 'center',
              icon: 'error',
              title: '<?php echo addslashes($errorMessage); ?>',
              showConfirmButton: false,
              timer: 9000
            });
          </script>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
          <div class="mb-4">
            <label for="username" class="block text-gray-700 font-medium mb-2">Email</label>
            <input type="email" id="Email" name="Email" placeholder="Email" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" />
          </div>
          <div class="mb-4">
            <label for="password" class="block text-gray-700 font-medium mb-2">Mot de passe</label>
            <input type="password" id="password" name="Password" placeholder="Mot de passe" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" />
            <span class="ml-5 text-purple-700"> <a href="forgetPassword.php">Moot de passe oubliér ?</a></span>
          </div>

          <button type="submit" class="w-full bg-purple-700 text-white font-medium py-2 rounded-lg hover:bg-purple-800 transition duration-200">
            Se connecter
          </button>
        </form>
      </div>
    </div>
    <div class="w-full h-screen bg-purple-700 hidden md:block md:flex items-center justify-center">
      <div class="bg-purple-700 text-white">
        <h2 class="text-3xl font-semibold text-center">ENSEM SCOLARITÉ</h2>
      </div>
    </div>
  </div>
</body>

</html>