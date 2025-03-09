<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aide</title>
    <link rel="stylesheet" href="../css/aide.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            margin: 0;
            padding: 40px;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 36px;
            color: #333;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 24px;
            color: #ce1527;
            margin-bottom: 10px;
        }

        .contact-info {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-info p {
            font-size: 18px;
            margin: 10px 0;
        }

        .contact-info p strong {
            color: #007BFF;
        }

        button {
            background-color: #007BFF;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-size: 18px;
            display: block;
            width: 100%;
            margin-top: 20px;
        }

        button:hover {
            background-color: #0056b3;
        }

        @media (max-width: 768px) {
            body {
                padding: 20px;
            }

            .contact-info {
                padding: 15px;
            }

            button {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <h1>Contact Direct</h1>
    <div class="contact-info">
        <h2>Coordonnées importantes</h2>
        <p><strong>Administration :</strong> admin@ensem.ac.ma | +212 5 22 22 44 35</p>
        <p><strong>Bureau des admissions :</strong> admissions@ensem.ac.ma | +212 5 22 33 44 55</p>
        <p><strong>Service informatique :</strong> support@ensem.ac.ma | +212 5 22 55 66 77</p>
    </div>
    <script src="aide.js"></script>
</body>

</html>