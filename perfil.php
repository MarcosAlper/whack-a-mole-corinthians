<?php
declare(strict_types=1);
require __DIR__ . '/api/bootstrap.php';
require_authenticated_page();
$currentPage = 'profile';
$heading = 'MEU PERFIL';
$logo = 'assets/img/toupeira2.png';
$logoAlt = 'Toupeira pixel art';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= html_escape(csrf_token()) ?>">
    <title>Perfil - Acerte a Toupeira</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php require __DIR__ . '/includes/nav.php'; ?>
    <main>
        <section class="perfil-card">
            <h2>INFORMAÇÕES DO JOGADOR</h2>
            <div class="perfil-info">
                <p><strong>Nome:</strong> <span id="profile-display-name">Carregando...</span></p>
                <p><strong>Username:</strong> <span id="profile-display-username">Carregando...</span></p>
                <p><strong>E-mail:</strong> <span id="profile-display-email">Carregando...</span></p>
                <p><strong>Data de nascimento:</strong> <span id="profile-display-birth">Carregando...</span></p>
                <p><strong>Telefone:</strong> <span id="profile-display-phone">Carregando...</span></p>
                <p><strong>CPF:</strong> <span id="profile-display-cpf">Carregando...</span></p>
            </div>
        </section>
        <section>
            <h2>EDITAR INFORMAÇÕES</h2>
            <form id="profile-form">
                <label for="profile-name">Nome completo</label>
                <input type="text" id="profile-name" name="nome" maxlength="150" required>

                <label for="profile-email">E-mail</label>
                <input type="email" id="profile-email" name="email" maxlength="254" required>

                <label for="profile-phone">Telefone</label>
                <input type="tel" id="profile-phone" name="telefone" inputmode="numeric" pattern="[0-9]{10,11}" minlength="10" maxlength="11" title="Digite 10 ou 11 números, incluindo o DDD." required>

                <label for="profile-birth">Data de nascimento (não editável)</label>
                <input type="text" id="profile-birth" readonly>

                <label for="profile-cpf">CPF (não editável)</label>
                <input type="text" id="profile-cpf" readonly>

                <label for="profile-username">Username (não editável)</label>
                <input type="text" id="profile-username" readonly>

                <button type="submit">SALVAR ALTERAÇÕES</button>
            </form>
            <p id="profile-message" role="status" aria-live="polite"></p>
        </section>
    </main>
    <script src="js/perfil.js" defer></script>
</body>
</html>
