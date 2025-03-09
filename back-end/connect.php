<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Connexion</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php

    $dsn = "mysql:host=localhost;dbname=ensem_web_project";
    $utilisateur = "root";
    $mot_de_passe = "";

    try {
        $pdo = new PDO($dsn, $utilisateur, $mot_de_passe);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo '<script>
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Échec de la connexion à la base de données",
            showConfirmButton: false,
            timer: 1500
        });
      </script>';
    }
    ?>
</body>

</html>