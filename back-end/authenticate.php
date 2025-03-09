<?php
session_start();
require_once './connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['Email'];
    $password = $_POST['Password'];
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    $password = trim($password);
    echo $email;
    try {
        $stmt = $pdo->prepare("SELECT Password FROM Utilisateur WHERE Email = :email AND Statut = 'Actif'");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            if ($password == $user['Password']) {
                echo "Connexion réussie !";
                exit;
            } else {
                echo '<script>
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Mot de passe ou mail incorrect",
                    showConfirmButton: false,
                    timer: 1500
                });
              </script>';
            }
        } else {
            echo '<script>
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Mot de passe ou mail incorrect",
                showConfirmButton: false,
                timer: 1500
            });
          </script>';
            echo "Utilisateur non trouvé ou inactif.";
        }
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
