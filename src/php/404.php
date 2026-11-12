<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>404 - Stránka sa nenašla</title>
    <link rel="icon" href="/content/images/favicon.png"> <!-- https://www.flaticon.com/free-icon/pc-tower_4275113 -->

    <link rel="stylesheet" href="/src/css/styles.css">
    <link rel="stylesheet" href="/src/css/forms.css">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap"> <!-- Inter & Plus Jakarta Sans Font Styles -->

    <script src="/src/ts/dist/components/preloader.js" type="module"></script>
    <script src="https://kit.fontawesome.com/069c0938a1.js" crossorigin="anonymous"></script> <!-- Icons -->
</head>

<body class="<?php echo ($_COOKIE["theme"] ?? "light") === "dark" ? "dark" : "light"; ?>">
    <div class="preloader">
        <div class="loader"></div>
    </div>

    <main>
        <section class="error_404">
            <h1 class="error_code">404</h1>

            <h2>Je nám to ľúto, ale stránka sa nenašla.</h2>

            <p>Stránka, ktorú hľadáte, neexistuje alebo bola presunutá.</p>

            <a href="/domov"><i class="fa-solid fa-arrow-left"></i> Späť na domovskú stránku</a> <!-- https://fontawesome.com/icons/arrow-left -->
        </section>
    </main>
</body>
</html>