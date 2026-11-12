<?php
    /**
     * Site Navigation (Shared By All Pages)
     *
     * Optional Variables Before Including:
     *
     * @var array $navigation_links - Custom Links ([label, href]) - Defaults To The Homepage Sections
     * @var bool $is_homepage - Whether The Links Should Point To The Sections On The Current Page
     * @var User $user_object - User Methods (Needed For The Account Button)
     * @var PDO $connection - Connection To The Database (Needed For The Account Button)
     * @var string $theme - Current Theme (light | dark)
    **/

    $section_prefix = !empty($is_homepage) ? "" : "/domov"; // Links Point To The Homepage Sections When Not On The Homepage

    $navigation_links = $navigation_links ?? [
        ["label" => "Služby", "href" => $section_prefix . "#all_services"],
        ["label" => "O nás", "href" => $section_prefix . "#about_us"],
        ["label" => "Recenzie", "href" => $section_prefix . "#reviews"],
        ["label" => "Kontakt", "href" => $section_prefix . "#contact"]
    ];

    // Logged In User (Name, Avatar, Role)

    $navigation_user = null;

    if(function_exists("isLoggedIn") and isLoggedIn() and isset($user_object, $connection) and $connection) {
        $navigation_user_id = $_SESSION["logged_in_user_id"];
        $profile_picture_name = $user_object->getUserInformation($connection, $navigation_user_id, "profile_picture_name");
        $google_profile_picture_url = $user_object->getUserInformation($connection, $navigation_user_id, "google_profile_picture_url");

        if(!empty($profile_picture_name)) $avatar = "/content/uploads/" . $navigation_user_id . "/" . $profile_picture_name;
        elseif(!empty($google_profile_picture_url)) $avatar = $google_profile_picture_url;
        else $avatar = "/content/images/avatar.png"; // https://www.flaticon.com/free-icon/user_847969

        $navigation_user = [
            "first_name" => (string)($user_object->getUserInformation($connection, $navigation_user_id, "first_name") ?? ""),
            "last_name" => (string)($user_object->getUserInformation($connection, $navigation_user_id, "last_name") ?? ""),
            "avatar" => $avatar,
            "is_admin" => $user_object->getUserInformation($connection, $navigation_user_id, "role") === "admin"
        ];
    }

    $current_theme = ($theme ?? "light") === "dark" ? "dark" : "light";
?>

<nav class="navigation_bar" aria-label="Hlavná navigácia" data-navigation>
    <div class="navigation_container">
        <a class="brand" href="/domov" aria-label="PC Expert - Domov">
            <span class="brand_mark" aria-hidden="true"><i class="fa-solid fa-microchip"></i></span> <!-- https://fontawesome.com/icons/microchip -->
            <span class="brand_name">PC&nbsp;Expert</span>
        </a>

        <div class="navigation_menu" id="navigation_menu" data-navigation-menu>
            <ul class="navigation_links">
                <?php foreach($navigation_links as $one_link): ?>
                    <li><a href="<?php echo $one_link["href"]; ?>"><?php echo $one_link["label"]; ?></a></li>

                <?php endforeach; ?>

                <?php if($navigation_user and $navigation_user["is_admin"]): ?>
                    <li><a class="admin" href="/administracia"><i class="fa-solid fa-shield-halved"></i> Administrácia</a></li> <!-- https://fontawesome.com/icons/shield-halved -->

                <?php endif; ?>
            </ul>

            <div class="navigation_account">
                <?php if($navigation_user): ?>
                    <a class="account_link" href="/moj-ucet" title="Môj účet">
                        <img class="profile_picture" src="<?php echo htmlspecialchars($navigation_user["avatar"], ENT_QUOTES, "UTF-8"); ?>" alt="" referrerpolicy="no-referrer">
                        <span><?php echo htmlspecialchars($navigation_user["first_name"] . " " . $navigation_user["last_name"], ENT_QUOTES, "UTF-8"); ?></span>
                    </a>

                    <a class="btn btn_ghost btn_sm logout_link" href="/logout.php" title="Odhlásiť sa"><i class="fa-solid fa-arrow-right-from-bracket"></i> <span>Odhlásiť</span></a> <!-- https://fontawesome.com/icons/arrow-right-from-bracket -->

                <?php else: ?>
                    <a class="btn btn_ghost btn_sm" href="/prihlasenie">Prihlásiť sa</a>
                    <a class="btn btn_sm" href="/registracia">Registrácia</a>

                <?php endif; ?>
            </div>
        </div>

        <div class="navigation_actions">
            <button class="theme_toggle" type="button" data-theme-toggle data-theme="<?php echo $current_theme; ?>" title="Prepnúť vzhľad" aria-label="Prepnúť na <?php echo $current_theme === "dark" ? "svetlý" : "tmavý"; ?> vzhľad">
                <i class="fa-solid fa-sun" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/sun -->
                <i class="fa-solid fa-moon" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/moon -->
            </button>

            <button class="hamburger" type="button" aria-label="Otvoriť menu" aria-expanded="false" aria-controls="navigation_menu" data-navigation-toggle>
                <i class="fa-solid fa-bars" aria-hidden="true"></i> <!-- https://fontawesome.com/icons/bars -->
            </button>
        </div>
    </div>
</nav>
