<?php
session_start();
require_once 'back-end/connect.php';
require_once 'back-end/mail_config.php';
require_once 'back-end/functions.php';
$errorMessage = '';
$step = 1;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($pdo)) {
        $errorMessage = "Échec de la connexion à la base de données.";
    } else {
        $step = filter_var($_POST['step'], FILTER_SANITIZE_NUMBER_INT);
        if ($step == 1) {
            $email = filter_var($_POST['Email'], FILTER_SANITIZE_EMAIL);
            try {
                $stmt = $pdo->prepare("SELECT * FROM Utilisateur WHERE Email = :email ");
                $stmt->bindParam(':email', $email);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($user) {
                    $mail = getMailer();
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = "Réinitialisation de mot de passe";
                    $code = generateRandomString();
                    $mail->Body = "
                    <p>Bonjour,</p>
                    <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
                    <p>Veuillez utiliser le code de vérification ci-dessous pour compléter cette opération :</p>
                    <p style='font-size: 18px; font-weight: bold; color: #333;'>$code</p>
                    <p>Si vous n'êtes pas à l'origine de cette demande, veuillez ignorer cet email.</p>
                    <p>Merci,</p>
                    <p><b>L'équipe Support</b></p>
                ";
                    if (!$mail->send()) {
                        $errorMessage = "Erreur lors de l'envoi de l'email. Veuillez réessayer.";
                        $errorTitle = "Erreur d'envoi";
                        exit();
                    }
                    $_SESSION['code'] = $code;
                    $_SESSION['email'] = $email;
                    $_SESSION['used'] = false;
                    $_SESSION['date'] = date('Y-m-d H:i:s');
                    $sucessMessage = "Un code a été envoyé à votre email. Vérifiez votre boîte de réception pour continuer.";
                    $successTitle = "Code envoyé";
                    $step = 2;
                } else {
                    $errorMessage = "Vérifiez votre adresse email et essayez à nouveau.";
                    $errorTitle = "Email introuvable";
                }
            } catch (PDOException $e) {
                $errorMessage = "Erreur : " . $e->getMessage();
            }
        } else if ($step == 2) {
            $verification_code = $_POST['code1'] . $_POST['code2'] . $_POST['code3'] . $_POST['code4'] . $_POST['code5'] . $_POST['code6'] . $_POST['code7'] . $_POST['code8'];
            if ($_SESSION['used']) {
                $errorMessage = "Le code a déjà été utilisé.";
                $errorTitle = "Code déjà utilisé";
            } else {
                $currentDate = new DateTime();
                $sessionDate = new DateTime($_SESSION['date']);
                if ($currentDate->diff($sessionDate)->m > 5) {
                    $errorMessage = "Le code a expiré.";
                    $errorTitle = "Code expiré";
                } else if ($verification_code != $_SESSION['code']) {
                    $errorMessage = "Le code est incorrect.";
                    $errorTitle = "Code incorrect";
                } else {
                    $sucessMessage = "Code vérifié avec succès.";
                    $successTitle = "Code vérifié";
                    $_SESSION['used'] = true;
                    $step = 3;
                }
            }
        } else {
            $password = trim($_POST['passwd1']);
            $password = password_hash($password, PASSWORD_DEFAULT);
            try {
                $stmt = $pdo->prepare("UPDATE utilisateur SET Password = :password WHERE Email = :email");
                $stmt->execute([
                    ':password' => $password,
                    ':email' => $_SESSION['email']
                ]);
                echo "<script>
                    Swal.fire({
                        position: 'center',
                        icon: 'success',
                        title: 'Mot de passe réinitialisé avec succès !',
                        showConfirmButton: false,
                        timer: 3000
                    }).then(() => {
                        window.location.href = 'login.php';
                    });
                </script>";
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
    <script src="../../js/forgetPassword.js" defer></script>
</head>

<body>

    <div class="flex flex-col md:flex-row h-screen">
        <div class="w-full flex items-center justify-center p-6">
            <div class="w-full max-w-md">
                <div class="my-8 text-center">
                    <img src="/public/images/logo_ensem.jpg" alt="ENSEM Logo" class="mx-auto mb-4 w-60 h-24" />
                </div>

                <h2 class="text-2xl font-semibold text-gray-700 mb-6 text-center">
                    Se connecter sur ENSEM SCOLARITÉ
                </h2>

                <ol class="items-center w-full space-y-4 sm:flex sm:space-x-8 sm:space-y-0 rtl:space-x-reverse">
                    <li class="flex items-center <?php if ($step == 1) : ?>  text-blue-600 dark:text-blue-500 <?php else : ?>  text-gray-500 dark:text-gray-400 <?php endif; ?> space-x-2 rtl:space-x-reverse">
                        <span class="flex items-center justify-center w-8 h-8 border <?php if ($step == 1) : ?> border-blue-600 <?php else : ?> border-gray-500  <?php endif; ?> rounded-full shrink-0 dark:border-blue-500">
                            1
                        </span>
                        <span>
                            <h3 class="font-medium leading-tight">Envoi code vérification</h3>
                            <p class="text-sm"></p>
                        </span>
                    </li>
                    <li class="flex items-center  <?php if ($step == 2) : ?>  text-blue-600 dark:text-blue-500 <?php else : ?>  text-gray-500 dark:text-gray-400 <?php endif; ?> space-x-2.5 rtl:space-x-reverse">
                        <span class="flex items-center justify-center w-8 h-8 border <?php if ($step == 2) : ?> border-blue-600 <?php else : ?> border-gray-500  <?php endif; ?>  rounded-full shrink-0 dark:border-gray-400">
                            2
                        </span>
                        <span>
                            <h3 class="font-medium leading-tight">Vérification du code</h3>

                        </span>
                    </li>
                    <li class="flex items-center <?php if ($step == 3) : ?>  text-blue-600 dark:text-blue-500 <?php else : ?>  text-gray-500 dark:text-gray-400 <?php endif; ?> space-x-2.5 rtl:space-x-reverse">
                        <span class="flex items-center justify-center w-8 h-8 border <?php if ($step == 3) : ?> border-blue-600 <?php else : ?> border-gray-500  <?php endif; ?>  rounded-full shrink-0 dark:border-gray-400">
                            3
                        </span>
                        <span>
                            <h3 class="font-medium leading-tight">Réinitialisation du mot de passe</h3>
                        </span>
                    </li>
                </ol>





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
                <div class=" mt-5 bg-[#ccf5f5] text-[#006a74]  border-[#b8f0f1]  border  rounded-lg p-6 mb-6 shadow-md">
                    <h3 class="text-xl font-semibold mb-4">Vous avez oublié votre mot de passe ?</h3>
                    <p>Ne vous inquiétez pas, suivez simplement les étapes suivantes :</p>
                    <ol class="list-decimal list-inside mt-2 space-y-2">
                        <ol class="space-y-4">
                            <li>1. Assurez-vous d'entrer correctement votre adresse email et cliquez sur <span class="font-bold">Envoyer</span>.</li>
                            <li>2. Vous recevrez un code dans votre boîte de réception, que vous devrez entrer à l'étape suivante pour réinitialiser votre mot de passe.</li>
                            <li>3. Entrez le code reçu et définissez un nouveau mot de passe.</li>
                        </ol>
                    </ol>
                </div>
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                    <?php if ($step == 1) : ?>
                        <div class="mb-4">
                            <label for="username" class="block text-gray-700 font-medium mb-2">Email</label>
                            <input type="email" id="Email" name="Email" placeholder="Email" required class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500" />
                        </div>
                        <div>
                            <input type="number" name="step" hidden value="1" id="">
                        </div>
                    <?php endif; ?>

                    <?php if ($step == 2) : ?>
                        <div class="flex justify-center mb-2 space-x-2 rtl:space-x-reverse">
                            <div>
                                <label for="code-1" class="sr-only">First code</label>
                                <input type="text" name="code1" maxlength="1" data-focus-input-init data-focus-input-next="code-2" id="code-1" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                            <div>
                                <label for="code-2" class="sr-only">Second code</label>
                                <input type="text" name="code2" maxlength="1" data-focus-input-init data-focus-input-prev="code-1" data-focus-input-next="code-3" id="code-2" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                            <div>
                                <label for="code-3" class="sr-only">Third code</label>
                                <input type="text" name="code3" maxlength="1" data-focus-input-init data-focus-input-prev="code-2" data-focus-input-next="code-4" id="code-3" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                            <div>
                                <label for="code-4" class="sr-only">Fourth code</label>
                                <input type="text" name="code4" maxlength="1" data-focus-input-init data-focus-input-prev="code-3" data-focus-input-next="code-5" id="code-4" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                            <div>
                                <label for="code-5" class="sr-only">Fifth code</label>
                                <input type="text" name="code5" maxlength="1" data-focus-input-init data-focus-input-prev="code-4" data-focus-input-next="code-6" id="code-5" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                            <div>
                                <label for="code-6" class="sr-only">Sixth code</label>
                                <input type="text" name="code6" maxlength="1" data-focus-input-init data-focus-input-prev="code-5" data-focus-input-next="code-7" id="code-6" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                            <div>
                                <label for="code-7" class="sr-only">Seventh code</label>
                                <input type="text" name="code7" maxlength="1" data-focus-input-init data-focus-input-prev="code-6" data-focus-input-next="code-8" id="code-7" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                            <div>
                                <label for="code-8" class="sr-only">Eighth code</label>
                                <input type="text" name="code8" maxlength="1" data-focus-input-init data-focus-input-prev="code-7" id="code-8" class="block w-9 h-9 py-3 text-sm font-extrabold text-center text-gray-900 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500" required />
                            </div>
                        </div>
                        <p id="helper-text-explanation" class="my-2 text-sm text-center text-gray-500 dark:text-gray-400">Veuillez saisir le code à 8 chiffres que nous avons envoyé par email.</p>
                        <div>
                            <input type="number" name="step" hidden value="2" id="">
                        </div>
                    <?php endif; ?>
                    <?php if ($step == 3) : ?>
                        <div class="mb-4">
                            <label for="passwd1" class="block text-gray-700 font-medium mb-2">Mot de passe</label>
                            <input
                                type="password"
                                minlength="8"
                                id="passwd1"
                                name="passwd1"
                                placeholder="Entrez votre mot de passe"
                                required
                                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                oninput="verifyPassword()" />
                        </div>
                        <div class="mb-4">
                            <label for="passwd2" class="block text-gray-700 font-medium mb-2">Confirmer votre mot de passe</label>
                            <input
                                type="password"
                                minlength="8"
                                id="passwd2"
                                name="passwd2"
                                placeholder="Confirmez votre mot de passe"
                                required
                                class="w-full border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-purple-500"
                                oninput="verifyPassword()" />
                        </div>
                        <div id="passwordError" class="text-red-500 font-medium mt-2"></div>
                        <div>
                            <input type="number" name="step" hidden value="3" id="">
                        </div>
                        <p class="text-sm text-gray-700 dark:text-gray-300 my-5 bg-yellow-100 dark:bg-yellow-700 p-4 rounded-lg shadow"> Votre mot de passe doit contenir : au moins <b>8 caractères</b>,
                            <b>1 chiffre</b>, <b>1 lettre majuscule</b>, <b>1 lettre minuscule</b>,
                            et <b>1 symbole</b> (ex: !@#$%^&*).
                        </p>
                    <?php endif; ?>


                    <button
                        type="submit" id="submitButton"
                        class="w-full bg-purple-700 text-white font-medium py-2 rounded-lg hover:bg-purple-800 transition duration-200  ?>"
                        <?php echo ($step == 3) ? 'disabled' : ''; ?>>
                        Envoyer
                    </button>

                    </button>
                </form>
            </div>
        </div>
        <div class="w-full h-screen hidden md:flex items-center justify-center">
            <img src="/public/images/resetPassword.jpg" alt="Réinitialisation de mot de passe" class="w-full h-full object-cover" />
        </div>
    </div>

</body>

</html>