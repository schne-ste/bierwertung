<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Bierwertung – Startseite</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Automatischer Redirect für Smartphones -->
    <!--<script>
        if (window.matchMedia("(max-width: 768px)").matches) {
            window.location.href = "user.php";
        }
    </script>-->

    <style>
        body {
            font-family: sans-serif;
            background: #222;
            color: #fff;
            text-align: center;
            padding: 40px;
        }

        h1 {
            margin-bottom: 40px;
            font-size: 2.5em;
        }

        .button {
            display: block;
            margin: 20px auto;
            padding: 20px 40px;
            font-size: 1.5em;
            background: #444;
            color: white;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            width: 80%;
            max-width: 400px;
            transition: background 0.3s;
        }

        .button:hover {
            background: #666;
        }

        .footer {
            margin-top: 60px;
            font-size: 0.9em;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>Bierwertung </h1>

    <a href="user.php" class="button">Punkte ändern</a>
    <a href="viewer.php?theme=light" class="button"> Punkte-Anzeige - Lightmode (TV)</a>
    <a href="viewer.php?theme=dark" class="button"> Punkte-Anzeige - Darkmode (TV)</a>
    <a href="print.php" class="button"> Drucken</a>
    <a href="login.php" class="button"> Admin Login</a>

    <div class="footer">
        © <?= date('Y') ?> Bierwertung – Prost! 
    </div>
</body>
</html>
