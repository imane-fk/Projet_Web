<?php
require_once('../back-end/connect.php');
session_start();
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'etudiant' ||
    !isset($_SESSION['id_etudiant'])
) {
    header('Location: ../login.php');
    exit();
}
if ($pdo) {
    $id_etudiant = $_SESSION['id_etudiant'];
    $id_utilisateur = $_SESSION['user_id'];
    $etudiant = [];
    try {
        $stmt = $pdo->prepare("
        SELECT u.ID_User, u.Nom,u.Prenom,u.Derniere_Connexion,u.Date_Creation,u.Telephone,u.Photo_Profil,u.Statut,u.Adresse, u.Email, e.ID_Etudiant, e.Nom_Arabe,e.Prenom_Arabe,e.CNE,e.Date_Naissance,e.Sexe,e.Telephone_Parents,e.Adresse_Parents,e.Nationalite,e.Email_Universitaire,e.Email_Universitaire,e.Boursier,e.Boursier,e.Acte_Cautionnement,e.Reglement_Vie_Collective,e.Reglement_Etudes,e.Cliche_Pulmonaire,e.Certificat_Medical,e.Copie_Baccalaureat
        FROM Utilisateur u
        LEFT JOIN etudiant e ON u.ID_User = e.ID_Utilisateur
        WHERE LOWER(u.Profil) = 'etu'
        AND u.ID_User = :userId
    ");
        $stmt->execute(['userId' => $id_utilisateur]);
        $etudiant = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur : " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenue Étudiant</title>
    <script>
        // Array of computer engineering quotes
        const quotes = [
            "“L'ordinateur est né pour résoudre des problèmes qui n'existaient pas avant lui.” – Bill Gates",
            "“Le logiciel est une excellente combinaison entre l'art et l'ingénierie.” – Bill Gates",
            "“La simplicité est la sophistication ultime.” – Leonardo da Vinci (et repris en ingénierie logicielle)",
            "“Le code est comme l'humour. Quand vous devez l'expliquer, c'est qu'il n'est pas bon.” – Cory House",
            "“Il y a deux façons d'écrire des programmes : les rendre si simples qu'il n'y a évidemment pas d'erreurs, ou si complexes qu'il n'y a pas d'erreurs évidentes.” – C.A.R. Hoare"
        ];

        // Function to get a random quote
        function getRandomQuote() {
            return quotes[Math.floor(Math.random() * quotes.length)];
        }

        // Display the random quote on the page
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('quote').textContent = getRandomQuote();
        });
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/Réinscription.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="../css/sidebar.css">
</head>

<body class="bg-gray-100 text-gray-900">
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
    <div class="flex flex-col items-center justify-center min-h-screen p-6">
        <div class="bg-white rounded-lg shadow-lg p-8 max-w-lg text-center">
            <h1 class="text-4xl font-bold text-blue-600">Bienvenue, cher étudiant ! 🎉</h1>

            <h2 class="font-bold text-2xl text-green-600 mb-4"><?php echo htmlspecialchars($etudiant['Nom'] . ' ' . $etudiant['Prenom']); ?></h2>
            <p class="mt-4 text-lg">
                Nous sommes ravis de vous voir ici. Préparez-vous à explorer le monde fascinant de l'ingénierie informatique et à transformer vos idées en réalité.
            <p class="mt-4 text-lg">
                Nous sommes ravis de vous voir ici. Préparez-vous à explorer le monde fascinant de l'ingénierie informatique et à transformer vos idées en réalité.
            </p>
            <div class="mt-6 p-4 bg-blue-50 rounded-lg border-l-4 border-blue-500">
                <p id="quote" class="italic text-blue-700"></p>
            </div>

        </div>
    </div>
</body>

</html>