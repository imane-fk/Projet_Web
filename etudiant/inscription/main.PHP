<?php
$errorMessage = '';
$errorTitle = '';
$successTitle = '';
$successMessage = '';
$utilisateur = [];
$etudiant = [];
$paiement = [];
$assurance = [];
require_once "../../back-end/connect.php";
session_start();
if (
  !isset($_SESSION['user_id']) ||
  !isset($_SESSION['role']) ||
  $_SESSION['role'] !== 'etudiant' ||
  !isset($_SESSION['id_etudiant'])
) {
  header("Location:../../login.php");
  exit();
} else {
  $id_utilisateur = $_SESSION['user_id'];
  $id_etudiant = $_SESSION['id_etudiant'];
  $id_anee = 1;
  if (isset($pdo)) {
    try {
      $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE ID_User= :id_utilisateur");
      $stmt->execute([
        ':id_utilisateur' => $id_utilisateur,
      ]);
      $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt = $pdo->prepare("SELECT * FROM etudiant WHERE :id_etudiant=:id_etudiant");
      $stmt->execute([
        ':id_etudiant' => $id_etudiant,
      ]);
      $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);

      $stmt = $pdo->prepare("SELECT * FROM paiement WHERE :id_etudiant=:id_etudiant AND :id_anee=:id_anee");
      $stmt->execute([
        ':id_etudiant' => $id_etudiant,
        ':id_anee' => $id_anee,
      ]);
      $paiement = $stmt->fetch(PDO::FETCH_ASSOC);
      $stmt = $pdo->prepare("SELECT * FROM assurance WHERE :id_etudiant=:id_etudiant AND :id_anee=:id_anee");
      $stmt->execute([
        ':id_etudiant' => $id_etudiant,
        ':id_anee' => $id_anee,
      ]);
      $assurance = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      $errorMessage = "Erreur de base de données: " . $e->getMessage();
      $errorTitle = "Erreur";
    }
  } else {
    $errorMessage = "Échec de la connexion à la base de données.";
    $eroorTitle = "Erreur";
  }
}



?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="/css/page1-inscription.css">
  <title>Formulaire d'inscription - Étudiant Ingénieur</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="../../css/navbar.css">
  <link rel="stylesheet" href="../../css/sidebar.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">

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
  <?php if (!empty($sucessMessage)) : ?>
    <script>
      Swal.fire({
        position: 'center',
        icon: 'success',
        text: '<?php echo addslashes($sucessMessage); ?>',
        title: '<?php echo addslashes($successTitle); ?>',
        showConfirmButton: false,
        timer: 9000
      });
    </script>
  <?php endif; ?>
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
          <img src="<?php echo !empty($utilisateur['Photo_Profil'])
                      ? 'data:image/jpeg;base64,' . base64_encode($utilisateur['Photo_Profil'])
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
      <li><a href="../Acceuil.php"><span class="material-icons"></span>Accueil</a></li>
      <li><a href="./main.PHP"><span class="material-icons"></span>Inscription</a></li>
      <li><a href="../Réinscription.php"><span class="material-icons"></span>Réinscription</a></li>
      <li><a href="aide.html"><span class="material-icons"></span>Aide</a></li>
      <li><a href="../../back-end/logout.php"><span class="material-icons"></span>Déconnexion</a></li>

    </ul>
  </aside>

  <div class="Etape1"></div>
  <form class="form" action="../../back-end/traitement_inscription.php" method="POST" enctype="multipart/form-data">

    <div class="div1">
      <h1>Formulaire d'inscription</h1>
      <h5>Note de la Direction de l'ENSEM</h5>
      <p class="p1">Cher-e élève ingénieur-e,<br>
        Nous avons mis en place cette application pour vous faciliter l'inscription et l'accès
        à votre école. L'opération en ligne se déroule en 5 étapes suivantes:
      </p>
      <ul class="p1">
        <li>Etape 1 : Compléter vos informations personnelles et attacher.</li>
        <li>Etape 2 : Attacher votre attestation d'assurance.</li>
        <li>Etape 4 : Déclaration des frais de paiement.</li>
        <li>Etape 5 : Choix des co-chambres.</li>
      </ul>
    </div>
    <div class="container">


      <div class="div2">
        <!-- Nom -->
        <div class="form-group">
          <label for="nom">Nom :</label>
          <input value="<?php echo $utilisateur['Nom']; ?>" type="text" id="nom" name="nom" placeholder="Votre nom" required>
        </div>
        <!-- Nom en arabe -->
        <div class="form-group">
          <label for="nom_ar">اللقب :</label>
          <input value="<?php echo $etudiant['Nom_Arabe']; ?>" type="text" id="nom_ar" name="nom_ar" placeholder="اللقب باللغة العربية" required>
        </div>



        <!-- Prénom -->
        <div class="form-group">
          <label for="prenom">Prénom :</label>
          <input value="<?php echo $utilisateur['Prenom']; ?>" type="text" id="prenom" name="prenom" placeholder="Votre prénom" required>
        </div>
        <!-- Prenom en arabe -->
        <div class="form-group">
          <label for="prenom_ar">الاسم :</label>
          <input value="<?php echo $etudiant['Prenom_Arabe']; ?>" type="text" id="prenom_ar" name="prenom_ar" placeholder="الاسم باللغة العربية" required>
        </div>


        <!-- CNE/CODE MASSAR -->
        <div class="form-group">
          <label for="CNE">CNE/Code Massar :</label>
          <input value="<?php echo $etudiant['CNE']; ?>" type="text" id="CNE" name="CNE" placeholder="Votre CNE" required>
        </div>
        <!-- Date de naissance -->
        <div class="form-group">
          <label for="date_naissance">Date de naissance :</label>
          <input value="<?php echo $etudiant['Date_Naissance']; ?>" type="date" id="date_naissance" name="date_naissance" required>
        </div>

        <!-- Sexe -->
        <div class="form-group">
          <label for="sexe">Sexe :</label>
          <select id="sexe" name="sexe" required>
            <option value="">---------</option>
            <option value="homme" <?php if ($etudiant['Sexe'] == 'Homme') echo 'selected'; ?>>Homme</option>
            <option value="femme" <?php if ($etudiant['Sexe'] == 'Femme') echo 'selected'; ?>>Femme</option>
          </select>
        </div>
      </div>
      <div class="div2">


        <!-- Téléphone personnelle -->

        <div class="form-group">
          <div><label for="telephone_p">Téléphone Personnel :</label></div>
          <div><input value="<?php echo $utilisateur['Telephone']; ?>" type="tel" id="telephone_p" name="telephone_p" placeholder="Votre numéro de téléphone" required></div>
        </div>

        <!-- telephone parents -->
        <div class="form-group">
          <label for="telephone_pare">Téléphone des Parents :</label>
          <input value="<?php echo $etudiant['Telephone_Parents']; ?>" type="tel" id="telephone_pare" name="telephone_pare" placeholder=" Téléphone de votre parents" required>
        </div>

        <!-- Nationalité -->
        <div class="form-group">
          <label for="nationalite">Nationalité :</label>
          <input value="<?php echo $etudiant['Nationalite']; ?>" type="text" id="nationalite" name="nationalite" placeholder="Votre nationalité" required>
        </div>
        <!-- Lieu de naissance -->
        <div class="form-group">
          <label for="lieu_naissance">Lieu de naissance :</label>
          <input value="<?php echo $etudiant['Lieu_Naissance']; ?>" type="text" id="lieu_naissance" name="lieu_naissance" placeholder="Votre lieu de naissance" required>
        </div>
        <!-- Adresse des parents -->
        <div class="form-group">
          <label for="adresse_parents">Adresse des parents :</label>
          <input value="<?php echo $etudiant['Adresse_Parents']; ?>" type="text" id="adresse_parents" name="adresse_parents" placeholder="Adresse des parents" required>
        </div>
        <!-- Adresse Personnelle -->
        <div class="form-group">
          <label for="adresse_personnelle">Adresse :</label>
          <input value="<?php echo $utilisateur['Adresse']; ?>" type="text" id="adresse_personnelle" name="adresse_personnelle" placeholder="Adresse personnelle" required>
        </div>
        <!-- Email Universitaire -->
        <div class="form-group">
          <label for="email">Email Universitaire :</label>
          <input value="<?php echo $etudiant['Email_Universitaire']; ?>" type="email" id="email" name="email" placeholder="Votre e-mail" required>
        </div>
        <!-- Email Personnelle -->
        <div class="form-group">
          <label for="email">Email personnelle :</label>
          <input value="<?php echo $utilisateur['Email']; ?>" type="email" id="email" name="email" placeholder="Votre e-mail" required>
        </div>


      </div>
    </div>
    <div class="container2">
      <!-- Boursier -->
      <div class="form-group">
        <label for="boursier">Boursier :</label>
        <input type="checkbox" id="boursier" name="boursier"
          <?php if ($etudiant['Boursier'] == 1) echo 'checked'; ?>>
      </div>

      <!-- Personne à besoins spécifiques -->
      <div class="form-group">
        <label for="besoins_specifiques">Personne à besoins spécifiques (handicap) :</label>
        <input type="checkbox" id="besoins_specifiques" name="besoins_specifiques"
          <?php if ($etudiant['Handicap'] == 1) echo 'checked'; ?>>
      </div>
    </div>
    </div>

    <div>
    </div>
    </div>
    <hr>
    <div>

      <!-- champ 3 -->

      <div class="gris">Veuillez attacher une photo d'identité de bonne qualité (où
        le visage représente au moins 40%) qui sera imprimée sur votre
        carte d'étudiant.De préférence une image carrée avec un fond clair et uni. =>
        <a href="/public/images/image.png" class="lien1">Voir un exemple</a>
        <br>
        <span class="red">Les photos de type selfie prises dans la nature, en famille, en voiture, etc... ne sont pas acceptées!</span>

      </div>
      <div>
        <div>
          <p>photo</p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="Photo_Profil">Modification :</label>

          <input type="file" accept="image/*" id="Photo_Profil" name="Photo_Profil">
          <img style="width: 300px;height:300px "
            src="<?php echo !empty($utilisateur['Photo_Profil'])
                    ? 'data:image/jpeg;base64,' . base64_encode($utilisateur['Photo_Profil'])
                    : 'https://static.wikia.nocookie.net/herofanon/images/d/d0/Eren_Yeager.png/revision/latest?cb=20200310003401'; ?>"
            alt="Extra large avatar">


        </div>
      </div>
    </div>
    <hr>
    <div>

      <!-- champ 4 -->

      <div class="gris">Veuillez attacher les documents que vous avez légalisé.</div>
      <div>
        <div>
          <p>Acte de cautionnement légalisé</p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="modification3">Modification :</label>
          <input type="file" id="modification3" name="Acte_Cautionnement">
          <?php if (!empty($etudiant['Acte_Cautionnement'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($etudiant['Acte_Cautionnement']); ?>" download="Acte_Cautionnement.pdf">Télécharger le fichier</a>
          <?php endif ?>
        </div>
      </div>

      <div>
        <div>
          <p>Règlement intérieur de la vie collective légalisé</p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="modification4">Modification :</label>
          <input type="file" id="modification4" name="Reglement_Vie_Collective">
          <?php if (!empty($etudiant['Reglement_Vie_Collective'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($etudiant['Reglement_Vie_Collective']); ?>" download="Reglement_Vie_Collective.pdf">Télécharger le fichier</a>
          <?php endif ?>
        </div>
      </div>

      <div>
        <div>
          <p>Règlement intérieur des études légalisé</p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="modification5">Modification :</label>
          <input type="file" id="modification5" name="Reglement_Etudes">
          <?php if (!empty($etudiant['Reglement_Etudes'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($etudiant['Reglement_Etudes']); ?>" download="Reglement_Etudes.pdf">Télécharger le fichier</a>
          <?php endif ?>
        </div>
      </div>

      <div>
        <div>
          <p>Cliché pulmonaire récent - Petit format obligatoire</p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="modification6">Modification :</label>
          <input type="file" id="modification6" name="Cliche_Pulmonaire">
          <?php if (!empty($etudiant['Cliche_Pulmonaire'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($etudiant['Cliche_Pulmonaire']); ?>" download="Cliche_Pulmonaire.pdf">Télécharger le fichier</a>
          <?php endif ?>
        </div>
      </div>

      <div>
        <div>
          <p>Certificat médical récent d’aptitude à la vie en collectivité</p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="modification7">Modification :</label>
          <input type="file" id="modification7" name="Certificat_Medical">
          <?php if (!empty($etudiant['Certificat_Medical'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($etudiant['Certificat_Medical']); ?>" download="Certificat_Medical.pdf">Télécharger le fichier</a>
          <?php endif ?>
        </div>
      </div>

      <div>
        <div>
          <p>Copie du baccalauréat et des autres diplômes</p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="modification8">Modification :</label>
          <input type="file" id="modification8" name="Copie_Baccalaureat">
          <?php if (!empty($etudiant['Copie_Baccalaureat'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($etudiant['Copie_Baccalaureat']); ?>" download="Copie_Baccalaureat.pdf">Télécharger le fichier</a>
          <?php endif ?>
        </div>
      </div>



      <div>
        <div>
          <p>Reçu de paiement des frais </p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="Reçu">Modification :</label>
          <input type="file" id="Reçu" name="Reçu" accept="application/pdf">
          <?php if (!empty($paiement['Justificatif'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($paiement['Justificatif']); ?>" download="justificatif.pdf">Télécharger le reçu de paiement</a>
          <?php endif ?>
        </div>
      </div>
      <div>
        <div>
          <p>Reçu d'assurance </p>
        </div>
        <!-- Modification -->
        <div class="form-group">
          <label for="Reçu">Modification :</label>
          <input type="file" id="Reçu" name="Reçu" accept="application/pdf">
          <?php if (!empty($assurance['Justificatif'])): ?>
            <a href="data:application/pdf;base64,<?php echo base64_encode($assurance['Justificatif']); ?>" download="justificatif.pdf">Télécharger d'assurance</a>
          <?php endif ?>
        </div>
      </div>




    </div>
    <hr>
    <div>
      <!-- champ 5 -->

      <!-- Frais d'internat -->
      <div class="form-group">
        <label for="frais_internat">Frais d'internat :</label>
        <input value="<?php echo $paiement['Prix_Internat']; ?>" type="number" id="frais_internat" name="frais_internat" placeholder="Montant en DH">
      </div>

      <!-- Frais de restauration -->
      <div class="form-group">
        <label for="frais_restauration">Frais de restauration :</label>
        <input value="<?php echo $paiement['Prix_Restau']; ?>" type="number" id="frais_restauration" name="frais_restauration" placeholder="Montant en DH">
      </div>

      <!-- Caution annuelle -->
      <div class="form-group">
        <label for="caution_annuelle">Caution annuelle :</label>
        <input value="<?php echo $paiement['Caution_Annuel']; ?>" type="number" id="caution_annuelle" name="caution_annuelle" placeholder="Montant en DH">
      </div>
    </div>


    </div>
    <!-- Bouton de soumission -->
    <div class="form-group">
      <button type="submit">Soumettre</button>
    </div>





  </form>

</body>

</html>