<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}
$csrf = $_SESSION['csrf'];
$flash = admin_flash();
$erro = '';

$installado = file_exists(ADMIN_PASS_FILE);

/* ---------- Primeiro acesso: criar senha ---------- */
if (!$installado) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_post('criar_senha') === '1') {
        if (!admin_has_csrf()) {
            $erro = 'Sessão expirada. Recarregue a página e tente de novo.';
        } elseif (admin_post('nova_senha') === '') {
            $erro = 'Escreva uma senha para continuar.';
        } elseif (strlen(admin_post('nova_senha')) < 6) {
            $erro = 'A senha precisa ter pelo menos 6 caracteres.';
        } elseif (admin_post('nova_senha') !== admin_post('nova_senha2')) {
            $erro = 'As duas senhas não são iguais.';
        } else {
            @file_put_contents(ADMIN_PASS_FILE, password_hash(admin_post('nova_senha'), PASSWORD_DEFAULT));
            $_SESSION['flash'] = 'Senha criada! Agora entre com ela.';
            admin_redirect('index.php');
        }
    }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Painel — Aromas de Jordana</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f4f3e7;color:#0e150e;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .card{background:#fff;max-width:420px;width:100%;border-radius:16px;padding:32px;box-shadow:0 10px 30px rgba(14,21,14,.12)}
  h1{font-size:22px;margin-bottom:4px}
  p.sub{color:#555;margin-bottom:20px;font-size:14px;line-height:1.5}
  label{display:block;font-weight:600;font-size:14px;margin:14px 0 6px}
  input{width:100%;padding:12px;border:2px solid #d8d5c8;border-radius:8px;font-size:16px}
  input:focus{outline:none;border-color:#00473c}
  button{width:100%;margin-top:20px;padding:14px;background:#00473c;color:#fff;border:0;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer}
  button:hover{background:#00382e}
  .erro{background:#fdecec;color:#a33;border:1px solid #f0c4c4;padding:10px 12px;border-radius:8px;margin-bottom:8px;font-size:14px}
  .aviso{background:#fff7e0;border:1px solid #f0d98a;padding:10px 12px;border-radius:8px;font-size:14px;margin-bottom:8px;color:#7a5c00;line-height:1.5}
</style>
</head>
<body>
  <div class="card">
    <h1>Bem-vinda, sua loja!</h1>
    <p class="sub">Vamos criar uma <strong>senha</strong> para que só você possa adicionar produtos no site.</p>
    <form method="post" action="index.php">
      <input type="hidden" name="csrf" value="<?php echo admin_html($csrf); ?>" />
      <input type="hidden" name="criar_senha" value="1" />
      <?php if ($erro !== '') { echo '<div class="erro">' . admin_html($erro) . '</div>'; } ?>
      <label for="nova_senha">Escolha sua senha</label>
      <input autofocus autocomplete="new-password" id="nova_senha" name="nova_senha" type="password" />
      <label for="nova_senha2">Digite a senha novamente</label>
      <input autocomplete="new-password" id="nova_senha2" name="nova_senha2" type="password" />
      <button type="submit">Criar senha e entrar</button>
    </form>
  </div>
</body>
</html>
<?php
    exit;
}

/* ---------- Sair ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && admin_post('acao') === 'sair' && admin_has_csrf()) {
    unset($_SESSION[ADMIN_SESSION_KEY]);
    $_SESSION['flash'] = 'Você saiu do painel.';
    admin_redirect('index.php');
}

/* ---------- Login ---------- */
if (!admin_is_authed()) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['senha'])) {
        $bloqueado = admin_login_lock_time();
        if ($bloqueado > time()) {
            $erro = 'Muitas tentativas erradas. Aguarde alguns minutos e tente de novo.';
        } elseif (!admin_has_csrf()) {
            $erro = 'Sessão expirada. Recarregue a página e tente de novo.';
        } else {
            $hash = @file_get_contents(ADMIN_PASS_FILE);
            if ($hash !== false && password_verify($_POST['senha'], trim($hash))) {
                session_regenerate_id(true);
                $_SESSION[ADMIN_SESSION_KEY] = true;
                admin_reset_fails();
            } else {
                admin_register_fail();
                $erro = 'Senha incorreta. Tente de novo.';
            }
        }
    }
    if (!admin_is_authed()) {
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Entrar — Aromas de Jordana</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f4f3e7;color:#0e150e;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px}
  .card{background:#fff;max-width:400px;width:100%;border-radius:16px;padding:32px;box-shadow:0 10px 30px rgba(14,21,14,.12)}
  h1{font-size:22px;margin-bottom:4px}
  p.sub{color:#555;margin-bottom:18px;font-size:14px}
  label{display:block;font-weight:600;font-size:14px;margin:12px 0 6px}
  input{width:100%;padding:12px;border:2px solid #d8d5c8;border-radius:8px;font-size:16px}
  input:focus{outline:none;border-color:#00473c}
  button{width:100%;margin-top:16px;padding:14px;background:#00473c;color:#fff;border:0;border-radius:8px;font-size:16px;font-weight:700;cursor:pointer}
  button:hover{background:#00382e}
  .erro{background:#fdecec;color:#a33;border:1px solid #f0c4c4;padding:10px 12px;border-radius:8px;margin-bottom:8px;font-size:14px}
  .aviso{background:#fff7e0;border:1px solid #f0d98a;padding:10px 12px;border-radius:8px;font-size:14px;margin-bottom:8px;color:#7a5c00;line-height:1.5}
</style>
</head>
<body>
  <div class="card">
    <h1>Painel da loja</h1>
    <p class="sub">Digite sua senha para adicionar produtos.</p>
    <?php if ($flash !== '') { echo '<div class="aviso">' . admin_html($flash) . '</div>'; } ?>
    <form method="post" action="index.php">
      <input type="hidden" name="csrf" value="<?php echo admin_html($csrf); ?>" />
      <?php if ($erro !== '') { echo '<div class="erro">' . admin_html($erro) . '</div>'; } ?>
      <label for="senha">Senha</label>
      <input autofocus autocomplete="current-password" id="senha" name="senha" type="password" />
      <button type="submit">Entrar</button>
    </form>
  </div>
</body>
</html>
<?php
        exit;
    }
}

/* ---------- Ações autenticadas ---------- */
$produtos = admin_read_products();
$acao = isset($_GET['acao']) ? $_GET['acao'] : 'lista';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!admin_has_csrf()) {
        $erro = 'Sessão expirada. Volte ao painel e tente de novo.';
    } elseif (admin_post('acao') === 'salvar') {
        $id = admin_post('id');
        $existente = $id !== '' ? admin_find_product($produtos, $id) : null;

        $nome = admin_post('nome');
        $preco = floatval(str_replace(',', '.', admin_post('preco')));

        if ($nome === '') {
            $erro = 'Escreva o nome do produto.';
        } elseif (admin_post('preco') !== '' && $preco <= 0) {
            $erro = 'O preço está incorreto (use vírgula, ex.: 69,90) ou deixe em branco para "sob consulta".';
        } else {
            $imagens = array();
            if ($existente) {
                $remover = isset($_POST['remover_foto']) ? $_POST['remover_foto'] : array();
                foreach ($existente['imagens'] as $idx => $img) {
                    if (is_array($img) && !in_array((string)$idx, array_map('strval', $remover), true)) {
                        $imagens[] = array('src' => $img['src'], 'alt' => '');
                    }
                }
            }
            foreach (admin_process_uploads() as $nova) {
                $imagens[] = $nova;
            }
            if (count($imagens) === 0) {
                $erro = 'Adicione pelo menos uma foto do produto.';
            } else {
                if ($existente) {
                    $id_real = $existente['id'];
                } else {
                    $id_real = admin_slug($nome) . '-' . substr(bin2hex(random_bytes(3)), 0, 4);
                }
                $produto = array(
                    'id' => $id_real,
                    'nome' => $nome,
                    'descricao' => admin_post('descricao'),
                    'preco' => $preco,
                    'categoria' => admin_post('categoria', 'sagradas'),
                    'imagens' => $imagens,
                    'ctaTexto' => 'Tenho interesse',
                    'ctaHref' => '#contato'
                );
                if ($existente) {
                    foreach ($produtos as $k => $p) {
                        if (isset($p['id']) && $p['id'] === $id_real) {
                            $produtos[$k] = $produto;
                        }
                    }
                } else {
                    array_unshift($produtos, $produto);
                }
                admin_write_products($produtos);
                $_SESSION['flash'] = $existente
                    ? 'Produto atualizado com sucesso!'
                    : 'Produto adicionado! Ele já aparece no site.';
                admin_redirect('index.php');
            }
        }
    } elseif (admin_post('acao') === 'excluir') {
        $deletar = admin_post('id');
        $novos = array();
        foreach ($produtos as $p) {
            if (isset($p['id']) && $p['id'] !== $deletar) {
                $novos[] = $p;
            }
        }
        admin_write_products($novos);
        $_SESSION['flash'] = 'Produto removido.';
        admin_redirect('index.php');
    }
}

$editar = null;
if ($acao === 'editar' && isset($_GET['id'])) {
    $editar = admin_find_product($produtos, $_GET['id']);
}

if ($acao === 'editar' && !$editar) {
    $acao = 'lista';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Painel — Aromas de Jordana</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:system-ui,-apple-system,Segoe UI,Roboto,sans-serif;background:#f4f3e7;color:#0e150e;min-height:100vh}
  .topo{background:#00473c;color:#fff;padding:16px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px}
  .topo strong{font-size:18px}
  .topo .links{display:flex;gap:12px;align-items:center}
  .topo a{color:#fff;text-decoration:none;font-size:14px;font-weight:600}
  .topo button{background:none;border:1px solid rgba(255,255,255,.4);color:#fff;padding:6px 12px;border-radius:8px;cursor:pointer;font-size:14px}
  .container{max-width:760px;margin:0 auto;padding:24px}
  .aviso{background:#fff7e0;border:1px solid #f0d98a;padding:12px 16px;border-radius:8px;margin:16px 24px;color:#7a5c00;font-size:15px;max-width:712px}
  .erro{background:#fdecec;color:#a33;border:1px solid #f0c4c4;padding:12px 16px;border-radius:8px;margin:16px 24px;font-size:15px;max-width:712px}
  .acima{display:flex;align-items:center;justify-content:space-between;margin:8px 0 20px;flex-wrap:wrap;gap:12px}
  h1{font-size:24px}
  .btn-add{background:#00473c;color:#fff;border:0;padding:12px 18px;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;text-decoration:none}
  .btn-add:hover{background:#00382e}
  .produto{background:#fff;border:1px solid #e2dfd1;border-radius:14px;padding:16px;display:flex;gap:16px;align-items:center;margin-bottom:12px;flex-wrap:wrap}
  .produto img{width:72px;height:72px;object-fit:cover;border-radius:10px;background:#eee}
  .produto .info{flex:1;min-width:200px}
  .produto .info h2{font-size:17px;margin-bottom:2px}
  .produto .info p{color:#555;font-size:13px}
  .produto .acoes{display:flex;gap:8px}
  .produto .acoes a,.produto .acoes button{border:1px solid #cfcbb9;background:#fff;color:#0e150e;padding:8px 14px;border-radius:8px;font-size:14px;cursor:pointer;text-decoration:none}
  .produto .acoes .excluir{color:#b33}
  .vazio{background:#fff;border:1px dashed #cfcbb9;border-radius:14px;padding:40px;text-align:center;color:#555}
  form.ficha{background:#fff;border:1px solid #e2dfd1;border-radius:14px;padding:24px}
  form.ficha label{display:block;font-weight:600;font-size:14px;margin:18px 0 6px}
  form.ficha input[type=text],form.ficha input[type=number],form.ficha textarea,form.ficha input[type=password]{width:100%;padding:12px;border:2px solid #d8d5c8;border-radius:8px;font-size:16px;font-family:inherit}
  form.ficha input:focus,form.ficha textarea:focus{outline:none;border-color:#00473c}
  form.ficha textarea{min-height:90px;resize:vertical}
  .categorias{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
  .categorias input{display:none}
  .categorias label{border:2px solid #d8d5c8;border-radius:10px;padding:12px;text-align:center;font-size:14px;cursor:pointer;margin:0;font-weight:600}
  .categorias input:checked + label{border-color:#00473c;background:#e7f0e6;color:#00473c}
  .fichas{display:flex;flex-wrap:wrap;gap:12px;margin-top:12px}
  .fichas .ficha-foto{position:relative;width:96px;height:96px}
  .fichas .ficha-foto img{width:96px;height:96px;object-fit:cover;border-radius:10px}
  .fichas .ficha-foto .rem{position:absolute;top:-6px;right:-6px;background:#b33;color:#fff;border:0;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:14px;font-weight:700}
  .ajuda{background:#f0f4ef;border-radius:10px;padding:12px;font-size:13px;color:#2c5d4f;line-height:1.5;margin-top:16px}
  .botoes{display:flex;gap:10px;margin-top:24px;flex-wrap:wrap}
  .botoes .salvar{background:#00473c;color:#fff;border:0;padding:12px 22px;border-radius:10px;font-size:16px;font-weight:700;cursor:pointer}
  .botoes .cancelar{border:1px solid #cfcbb9;background:#fff;padding:12px 22px;border-radius:10px;font-size:16px;text-decoration:none;color:#0e150e}
  @media (max-width:520px){.container{padding:16px}.categorias{grid-template-columns:1fr}.topo .links{width:100%}}
</style>
</head>
<body>
  <div class="topo">
    <strong>Aromas de Jordana</strong>
    <div class="links">
      <a href="../index.html" target="_blank">Ver a loja</a>
      <form method="post" action="index.php"><input type="hidden" name="csrf" value="<?php echo admin_html($csrf); ?>" /><input type="hidden" name="acao" value="sair" /><button type="submit">Sair</button></form>
    </div>
  </div>

  <?php
  if ($erro !== '') { echo '<div class="erro">' . admin_html($erro) . '</div>'; }
  if ($flash !== '') { echo '<div class="aviso">' . admin_html($flash) . '</div>'; }
  ?>

  <div class="container">
  <?php if ($acao === 'adicionar' || ($acao === 'editar' && $editar)) { ?>
    <?php
    $val = $editar ? $editar : array('nome' => '', 'descricao' => '', 'preco' => '', 'categoria' => 'sagradas', 'imagens' => array());
    ?>
    <form class="ficha" method="post" action="index.php" enctype="multipart/form-data">
      <input type="hidden" name="csrf" value="<?php echo admin_html($csrf); ?>" />
      <input type="hidden" name="acao" value="salvar" />
      <input type="hidden" name="id" value="<?php echo admin_html($editar ? $editar['id'] : ''); ?>" />

      <h1><?php echo $editar ? 'Editar produto' : 'Adicionar novo produto'; ?></h1>

      <label for="nome">Nome do produto</label>
      <input id="nome" name="nome" type="text" value="<?php echo admin_html($val['nome']); ?>" placeholder="Ex.: Vela de Lavanda"/>

      <label for="descricao">Descrição</label>
      <textarea id="descricao" name="descricao" placeholder="Explique o produto de um jeito carinhoso — olhe os outros produtos para se inspirar."><?php echo admin_html($val['descricao']); ?></textarea>

      <label for="preco">Preço (deixe em branco para "sob consulta")</label>
      <input id="preco" name="preco" type="text" inputmode="decimal" value="<?php echo admin_html((float)$val['preco'] > 0 ? number_format((float)$val['preco'], 2, ',', '') : ''); ?>" placeholder="Ex.: 69,90" />

      <label>Categoria</label>
      <div class="categorias">
        <input type="radio" id="cat-sagradas" name="categoria" value="sagradas" <?php echo $val['categoria'] === 'sagradas' ? 'checked' : ''; ?> /><label for="cat-sagradas">Sagradas</label>
        <input type="radio" id="cat-decorativas" name="categoria" value="decorativas" <?php echo $val['categoria'] === 'decorativas' ? 'checked' : ''; ?> /><label for="cat-decorativas">Decorativas</label>
        <input type="radio" id="cat-lembrancinhas" name="categoria" value="lembrancinhas" <?php echo $val['categoria'] === 'lembrancinhas' ? 'checked' : ''; ?> /><label for="cat-lembrancinhas">Lembrancinhas</label>
      </div>

      <label>Fotos do produto</label>
      <?php if (count($val['imagens']) > 0) { ?>
        <div class="fichas">
        <?php foreach ($val['imagens'] as $idx => $img) { ?>
          <div class="ficha-foto">
            <img src="../<?php echo admin_html($img['src']); ?>" alt="" />
            <button type="button" class="rem" onclick="this.closest('.ficha-foto').remove();">x</button>
            <input type="hidden" name="remover_foto[]" value="<?php echo admin_html((string)$idx); ?>" />
          </div>
        <?php } ?>
        </div>
      <?php } ?>
      <input id="fotos" name="fotos[]" type="file" multiple accept="image/*" />
      <div class="ajuda">Dica: a <strong>primeira foto</strong> é a capa do produto. Se mandar mais de uma, elas aparecem como miniaturas para trocar. Fotos grandes de celular são reduzidas automaticamente.</div>

      <div class="botoes">
        <button class="salvar" type="submit"><?php echo $editar ? 'Salvar alterações' : 'Adicionar produto'; ?></button>
        <a class="cancelar" href="index.php">Cancelar</a>
      </div>
    </form>
  <?php } else { ?>
    <div class="acima">
      <h1>Meus produtos</h1>
      <a class="btn-add" href="index.php?acao=adicionar">+ Adicionar produto</a>
    </div>
    <?php if (count($produtos) === 0) { ?>
      <div class="vazio">Nenhum produto ainda. Clique em "Adicionar produto" para publicar o primeiro!</div>
    <?php } ?>
    <?php foreach ($produtos as $p) {
        $capa = null;
        if (isset($p['imagens'][0]['src'])) { $capa = $p['imagens'][0]['src']; }
    ?>
      <div class="produto">
        <?php if ($capa) { ?><img src="../<?php echo admin_html($capa); ?>" alt="" /><?php } ?>
        <div class="info">
          <h2><?php echo admin_html($p['nome']); ?></h2>
          <p><?php echo admin_html(admin_categoria_label(isset($p['categoria']) ? $p['categoria'] : 'sagradas')); ?> · <?php echo (float)$p['preco'] > 0 ? 'R$ ' . admin_html(number_format((float)$p['preco'], 2, ',', '')) : 'Preço sob consulta'; ?></p>
        </div>
        <div class="acoes">
          <a href="index.php?acao=editar&amp;id=<?php echo admin_html($p['id']); ?>">Editar</a>
          <form method="post" action="index.php" onsubmit="return confirm('Apagar este produto?');">
            <input type="hidden" name="csrf" value="<?php echo admin_html($csrf); ?>" />
            <input type="hidden" name="acao" value="excluir" />
            <input type="hidden" name="id" value="<?php echo admin_html($p['id']); ?>" />
            <button class="excluir" type="submit">Excluir</button>
          </form>
        </div>
      </div>
    <?php } ?>
  <?php } ?>
  </div>
</body>
</html>