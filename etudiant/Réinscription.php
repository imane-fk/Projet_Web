<?php
session_start();
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'etudiant' ||
    !isset($_SESSION['id_etudiant'])
) {
    header('Location: ../login.php');
    exit();
} else {
    $host = 'localhost';
    $dbname = 'ensem_web_project';
    $username = 'root';
    $password = '';
    $errorMessage = '';
    $successMessage = '';
    $successTitle = '';
    $errorTitle = '';
    $id_etudiant = $_SESSION['id_etudiant'];
    $id_annee = 2;
    $nom = '';
    $prenom = '';
    $cne = '';
    $adresse_parents = '';
    $adresse = '';
    $email_universitaire = '';
    $telephone_parents = '';
    $caution = '';
    $restauration = '';
    $internat = '';
    $justificatif = '';
    $id_paiement = '';
    $id_assurance = '';

    try {

        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $stmt = $pdo->prepare("
    SELECT 
        u.ID_User, u.Nom, u.Prenom, u.Derniere_Connexion, u.Date_Creation, 
        u.Telephone, u.Photo_Profil, u.Statut, u.Adresse, u.Email, 
        e.ID_Etudiant, e.Nom_Arabe, e.Prenom_Arabe, e.CNE, e.Date_Naissance, 
        e.Sexe, e.Telephone_Parents, e.Adresse_Parents, e.Nationalite, 
        e.Email_Universitaire, e.Boursier, e.Acte_Cautionnement, 
        e.Reglement_Vie_Collective, e.Reglement_Etudes, e.Cliche_Pulmonaire, 
        e.Certificat_Medical, e.Copie_Baccalaureat
    FROM 
        Utilisateur u
    LEFT JOIN 
        etudiant e ON u.ID_User = e.ID_Utilisateur
    WHERE 
        e.ID_Etudiant = :id_etudiant
");
        $stmt->execute([':id_etudiant' => $id_etudiant]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($etudiant)) {
            $nom = $etudiant['Nom'];
            $prenom = $etudiant['Prenom'];
            $cne = $etudiant['CNE'];
            $adresse_parents = $etudiant['Adresse_Parents'];
            $adresse = $etudiant['Adresse'];
            $email_universitaire = $etudiant['Email_Universitaire'];
            $telephone_personnel = $etudiant['Telephone'];
            $telephone_parents = $etudiant['Telephone_Parents'];
        } else {
            $errorMessage = "Étudiant introuvable !";
            $errorTitle = "Étudiant introuvable";
        }

        $stmt = $pdo->prepare("SELECT * FROM paiement WHERE ID_Etudiant = :id_etudiant AND ID_Annee = :id_annee");
        $stmt->execute([
            ':id_etudiant' => $id_etudiant,
            ':id_annee' => $id_annee,
        ]);
        $paiement = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($paiement) {
            $id_paiement = $paiement['ID_Paiement'];
            $caution = $paiement['Caution_Annuel'];
            $restauration = $paiement['Prix_Restau'];
            $internat = $paiement['Prix_Internat'];
            $date_paiement = $paiement['Date_Paiement'];
        }
        $stmt = $pdo->prepare("SELECT * FROM assurance WHERE ID_Etudiant = :id_etudiant AND ID_Annee = :id_annee");
        $stmt->execute([
            ':id_etudiant' => $id_etudiant,
            ':id_annee' => $id_annee,
        ]);
        $assurance = $stmt->fetch();
        if ($assurance) {
            $id_assurance = $assurance['ID_Assurance'];
            $justificatif = $assurance['Justificatif'];
        }
    } catch (PDOException $e) {
        $errorMessage = "Échec de la requête SQL : " . $e->getMessage();
        $errorTitle = "Erreur de requête";
    }
}



?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire de Réinscription</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../css/Réinscription.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php if (!empty($errorMessage)) : ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'error',
                text: '<?php echo addslashes($errorMessage); ?>',
                title: '<?php echo addslashes($errorTitle); ?>',
                showConfirmButton: false,
                timer: 9000
            });
        </script>
    <?php endif; ?>

    <?php if (!empty($successMessage)) : ?>
        <script>
            Swal.fire({
                position: 'center',
                icon: 'success',
                text: '<?php echo addslashes($successMessage); ?>',
                title: '<?php echo addslashes($successTitle); ?>',
                showConfirmButton: false,
                timer: 9000
            });
        </script>
    <?php endif; ?>
    <!-- Navbar -->
    <header>
        <nav class="navbar">
            <div class="navbar-left">
                <span class="year">Année Universitaire : 2024-2025</span>
            </div>
            <div class="navbar-right">
                <div class="icons">
                    <span class="material-icons">message</span>
                    <span class="icon-notification">1</span>
                </div>
                <div class="icons">
                    <span class="material-icons">notifications</span>
                    <span class="icon-notification">0</span>
                </div>
                <div class="user-profile">
                    <img src="<?php echo !empty($etudiant['Photo_Profil'])
                                    ? 'data:image/jpeg;base64,' . base64_encode($etudiant['Photo_Profil'])
                                    : '/public/images/photo site.jpg'; ?>" alt="Utilisateur" class="profile-photo">
                </div>
            </div>
        </nav>
    </header>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="/public/images/school-logo.png" alt="Logo de l'école" />
            <h2>École Nationale Supérieure d'Électricité et de Mécanique</h2>
        </div>
        <ul class="sidebar-links">
            <h4>
                <span>Menu Principal</span>
                <div class="menu-separator"></div>
            </h4>
            <li><a href="./Acceuil.php"><span class="material-icons"></span>Accueil</a></li>
            <li><a href="./inscription/main.PHP"><span class="material-icons"></span>Inscription</a></li>
            <li><a href="./Réinscription.php"><span class="material-icons"></span>Réinscription</a></li>
            <li><a href="../Aide.php"><span class="material-icons"></span>Aide</a></li>
            <li><a href="../back-end/logout.php"><span class="material-icons"></span>Déconnexion</a></li>

        </ul>
    </aside>
    <div class="content">
        <div class="container">
            <h1>Formulaire de Réinscription</h1>
            <br>
            <h3>Informations personnelles</h3>
            <form action="../../back-end/project.php" enctype="multipart/form-data" method="post">
                <!-- Champ caché pour l'identifiant de l'étudiant -->
                <input type="hidden" name="id_etudiant" value="<?php echo $id_etudiant; ?>">

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" value="<?php echo htmlspecialchars($nom); ?>" required>
                <br>
                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" value="<?php echo htmlspecialchars($prenom); ?>" required>
                <br>
                <label for="CNE">CNE / Code Massar :</label>
                <input type="text" id="CNE" name="CNE" value="<?php echo htmlspecialchars($cne); ?>" required>
                <br>
                <label for="adresse_parents">Adresse des parents :</label>
                <input type="text" id="adresse_parents" name="adresse_parents" value="<?php echo htmlspecialchars($adresse_parents); ?>" required>
                <br>
                <label for="adresse">Adresse :</label>
                <input type="text" id="adresse" name="adresse" value="<?php echo htmlspecialchars($adresse); ?>" required>
                <br>
                <label for="email_universitaire">Email Universitaire :</label>
                <input type="email" id="email_universitaire" name="email_universitaire" value="<?php echo htmlspecialchars($email_universitaire); ?>" required>
                <br>
                <label for="telephone">Téléphone personnel :</label>
                <input type="tel" id="telephone" name="telephone" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($telephone_personnel); ?>" required>
                <br>
                <label for="telephone-parents">Téléphone des parents :</label>
                <input type="tel" id="telephone_parents" name="telephone_parents" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($telephone_parents); ?>" required>
                <br>
                <h3>Assurance et frais</h3>
                <label for="assurance">Attestation d'assurance :</label>
                <input type="file" id="assurance" name="assurance" accept="image/*,application/pdf">
                <input type="number" name="id_assurance" value="<?php echo $id_assurance; ?>" hidden>
                <?php if (!empty($assurance['Justificatif'])): ?>
                    <a href="data:application/pdf;base64,<?php echo base64_encode($assurance['Justificatif']); ?>" download="justificatif.pdf">Télécharger l'attestation d'assurance</a>
                <?php endif; ?>
                <br>
                <label for="caution">Prix de la caution annuelle :</label>
                <input type="number" id="caution" name="caution" value="<?php echo htmlspecialchars($caution); ?>">
                <br>
                <label for="prix_Internat">Prix de l'internat :</label>
                <input type="number" id="internat" name="prix_Internat" value="<?php echo htmlspecialchars($internat); ?>">
                <br>
                <label for="prix_Restauration">Prix de la Restauration :</label>
                <input type="number" id="restauration" name="Prix_Restau" value="<?php echo htmlspecialchars($restauration); ?>">
                <br>
                <label for="date">Date de paiement des frais :</label>
                <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date_paiement); ?>" required>
                <br>
                <input type="number" name="id_paiement" value="<?php echo $id_paiement; ?>" hidden>
                <label for="frais">Reçu de paiement des frais</label>
                <input type="file" id="frais" name="frais" accept="image/*,application/pdf">
                <?php if (!empty($paiement['Justificatif'])): ?>
                    <a href="data:application/pdf;base64,<?php echo base64_encode($paiement['Justificatif']); ?>" download="justificatif.pdf">Télécharger le reçu de paiement</a>
                <?php endif; ?>
                <br><br><br>
                <button type="submit">Soumettre</button>
            </form>
        </div>
    </div>
    </div>

</body>

</html>