<?php
session_start();
require_once 'credentials.php';

$message = '';
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $inputUser = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($inputUser === '' || $password === '') {
        $message = 'Preencha usuário e senha.';
    } else {
        //aceitar  a chave do array 'admin' ou o nome exibido 'Tatu'
        $credentials = getUserCredentials();
        $userKey = null;

        // procura por chave exata
        if (isset($credentials[$inputUser])) {
            $userKey = $inputUser;
        } else {
            //procura por nome/username exibido (case-insensitive)
            foreach ($credentials as $key => $c) {
                if (isset($c['username']) && strcasecmp($c['username'], $inputUser) === 0) {
                    $userKey = $key;
                    break;
                }
            }
        }

        if ($userKey !== null) {
            $userData = authenticate($userKey, $password);
        } else {
            $userData = false;
        }

        if ($userData) {
            $_SESSION['user'] = $userData;
            header('Location: index.php');
            exit;
        }

        $message = 'Usuário ou senha inválidos.';
    }
}

$user = $_SESSION['user'] ?? null;
$loggedIn = !empty($user);
$page = $loggedIn ? ($_GET['page'] ?? 'dashboard') : 'home';

function getTopMenuItems($user)
{
    if (!$user) {
        return [];
    }

    return [
        ['label' => 'Configurações', 'href' => 'index.php?page=settings'],
        ['label' => 'Sair', 'href' => 'index.php?action=logout'],
    ];
}

function getSideMenuItems($user)
{
    if (!$user) {
        return [];
    }

    if ($user['type'] === 'admin') {
        return [
            ['label' => 'Painel', 'href' => 'index.php?page=dashboard'],
            ['label' => 'Usuários', 'href' => 'index.php?page=users'],
            ['label' => 'Relatórios', 'href' => 'index.php?page=reports'],
            ['label' => 'Configurações', 'href' => 'index.php?page=settings'],
        ];
    }
    
    if ($user['type'] === 'moderator') {
        return [
            ['label' => 'Painel', 'href' => 'index.php?page=dashboard'],
            ['label' => 'Usuários', 'href' => 'index.php?page=users'],
            ['label' => 'Configurações', 'href' => 'index.php?page=settings'],
        ];
    }

    return [
        ['label' => 'Painel', 'href' => 'index.php?page=dashboard'],
        ['label' => 'Meu Perfil', 'href' => 'index.php?page=profile'],
        ['label' => 'Configurações', 'href' => 'index.php?page=settings'],
    ];
}

function renderContent($page, $user)
{
    if (!$user) {
        return '<div class="content-card"><h2>Bem-vindo ao Tatu</h2><p>Este é um espaço livre para apresentar informações, notícias ou conteúdos especiais para visitantes. Faça login para acessar seu dashboard personalizado.</p></div>';
    }

    switch ($page) {
        case 'users':
            return '<div class="content-card"><h2>Gestão de usuários</h2><p>Como administrador, você pode revisar contas, definir permissões e manter o time organizado.</p></div>';
        case 'reports':
            return '<div class="content-card"><h2>Relatórios</h2><p>Veja indicadores chave, desempenho e metas da equipe. Seus dados são atualizados em tempo real para decisões rápidas.</p></div>';
        case 'settings':
            return '<div class="content-card"><h2>Configurações</h2><p>Ajuste as preferências do seu perfil, notificações e detalhes da sua conta.</p></div>';
        case 'profile':
            return '<div class="content-card"><h2>Meu Perfil</h2><p>Revise suas informações pessoais, atualize seus dados e acompanhe seu progresso dentro da plataforma.</p></div>';
        case 'tasks':
            return '<div class="content-card"><h2>Minhas Tarefas</h2><p>Confira suas tarefas pendentes, prazos e prioridades para manter seu trabalho organizado.</p></div>';
        case 'support':
            return '<div class="content-card"><h2>Ajuda</h2><p>Encontre guias, contato com suporte e respostas rápidas para seguir com seu trabalho sem interrupções.</p></div>';
        default:
            if ($user['type'] === 'admin') {
                return '<div class="content-card"><h2>Dashboard do Administrador</h2><p>Olá ' . htmlspecialchars($user['name']) . ', você está acessando seu painel com recursos avançados.</p><div class="feature-grid"><div><strong>Relatórios rápidos</strong><p>Analise dados, volume de usuários e resultados.</p></div><div><strong>Controle de equipe</strong><p>Monitore atividades, permissões e tarefas.</p></div></div></div>';
            }
            if ($user['type'] === 'moderator') {
                return '<div class="content-card"><h2>Dashboard do Moderador</h2><p>Olá ' . htmlspecialchars($user['name']) . ', você está acessando seu painel com recursos de moderação.</p><div class="feature-grid"><div><strong>Controle de conteúdo</strong><p>Monitore e gerencie os usuarios da plataforma.</p></div></div></div>';
            }

            return '<div class="content-card"><h2>Dashboard do Usuário</h2><p>Olá ' . htmlspecialchars($user['name']) . ', aqui está seu espaço personalizado.</p><div class="feature-grid"><div><strong>Atualizações</strong><p>Personalize agora as cores do site com o seu estilo.</p></div></div></div>';
    }
}

$topLinks = getTopMenuItems($user);
$sideMenu = getSideMenuItems($user);
$contentHtml = renderContent($page, $user);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Tatu</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topbar">
        <div class="topbar-left">
            <span class="brand">Sistema do Tatu</span>
            <span class="brand-subtitle"><?= $loggedIn ? 'Painel de ' . htmlspecialchars($user['name']) : 'Página de conteúdo livre' ?></span>
        </div>

        <?php if (!$loggedIn): ?>
        <form class="login-form" method="post" action="index.php">
            <input type="text" name="username" placeholder="Usuário" autocomplete="username">
            <input type="password" name="password" placeholder="Senha" autocomplete="current-password">
            <button type="submit" name="login">Entrar</button>
        </form>
        <?php else: ?>
        <div class="topbar-right">
            <?php foreach ($topLinks as $link): ?>
                <a class="top-link" href="<?= htmlspecialchars($link['href']) ?>"><?= htmlspecialchars($link['label']) ?></a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </header>

    <?php if ($message): ?>
        <div class="message-box"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!$loggedIn): ?>
    <main class="main-full">
        <section class="hero">
            <h1>Conteúdo livre para visitantes</h1>
            <p>Explore uma página com informações abertas, antes de acessar seu dashboard personalizado. Faça login para ver conteúdo exclusivo e menus adaptados ao seu perfil.</p>
            <div class="hero-cards">
                <article>
                    <h3>Apresentação</h3>
                    <p>Uma interface simples e clara, com cabeçalho e conteúdo inferior adaptados ao visitante.</p>
                </article>
                <article>
                    <h3>Personalização</h3>
                    <p>Após o login, o cabeçalho, menu lateral e conteúdo passam a ser personalizados por tipo de usuário.</p>
                </article>
            </div>
        </section>
    </main>
    <?php else: ?>
    <div class="dashboard-layout">
        <aside class="sidebar">
            <div class="user-card">
                <strong><?= htmlspecialchars($user['name']) ?></strong>
              <span>Tipo: <?= htmlspecialchars($user['type'] === 'admin' ? 'Administrador' :($user['type'] === 'moderator' ? 'Moderador' : 'Usuário')) ?></span>
            </div>
            <nav class="side-nav">
                <?php foreach ($sideMenu as $menu): ?>
                    <a href="<?= htmlspecialchars($menu['href']) ?>" class="side-link"><?= htmlspecialchars($menu['label']) ?></a>
                <?php endforeach; ?>
            </nav>
        </aside>
        <main class="dashboard-content">
            <?= $contentHtml ?>
        </main>
    </div>
    <?php endif; ?>
</body>
</html>
