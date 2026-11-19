<header>
    <img src="<?= html_escape($logo) ?>" alt="<?= html_escape($logoAlt) ?>" class="logo-header">
    <h1><?= html_escape($heading) ?></h1>
    <nav aria-label="Navegação principal">
        <ul>
            <li><a href="jogo.php"<?= $currentPage === 'game' ? ' aria-current="page"' : '' ?>>Jogo</a></li>
            <li><a href="perfil.php"<?= $currentPage === 'profile' ? ' aria-current="page"' : '' ?>>Perfil</a></li>
            <li><a href="ranking.php"<?= $currentPage === 'ranking' ? ' aria-current="page"' : '' ?>>Ranking</a></li>
            <li>
                <form action="api/auth/logout.php" method="post">
                    <input type="hidden" name="csrf" value="<?= html_escape(csrf_token()) ?>">
                    <button type="submit">SAIR</button>
                </form>
            </li>
        </ul>
    </nav>
</header>
