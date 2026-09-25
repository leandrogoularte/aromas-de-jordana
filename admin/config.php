<?php
/* Aromas de Jordana — configuração do painel de administração */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('ADMIN_PASS_FILE', __DIR__ . '/.passwd');
define('ADMIN_DATA_FILE', dirname(__DIR__) . '/assets/data/produtos.json');
define('ADMIN_UPLOAD_DIR', dirname(__DIR__) . '/assets/img/produtos/');
define('ADMIN_UPLOAD_URL', 'assets/img/produtos/');
define('ADMIN_SESSION_KEY', 'aromas_admin_ok');

function admin_is_authed() {
    return isset($_SESSION[ADMIN_SESSION_KEY]) && $_SESSION[ADMIN_SESSION_KEY] === true;
}

function admin_post($key, $padrao = '') {
    return isset($_POST[$key]) ? trim($_POST[$key]) : $padrao;
}

function admin_has_csrf() {
    return isset($_POST['csrf']) && isset($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $_POST['csrf']);
}

function admin_html($texto) {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

function admin_redirect($url) {
    header('Location: ' . $url);
    exit;
}

function admin_flash() {
    $msg = isset($_SESSION['flash']) ? $_SESSION['flash'] : '';
    unset($_SESSION['flash']);
    return $msg;
}

function admin_slug($texto) {
    $texto = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto);
    $texto = strtolower(trim((string)$texto));
    $texto = preg_replace('/[^a-z0-9]+/', '-', $texto);
    $texto = trim((string)$texto, '-');
    return $texto !== '' ? $texto : 'produto';
}

function admin_read_products() {
    if (!file_exists(ADMIN_DATA_FILE)) {
        return array();
    }
    $raw = @file_get_contents(ADMIN_DATA_FILE);
    if ($raw === false) {
        return array();
    }
    $json = json_decode($raw, true);
    $lista = is_array($json) && isset($json['produtos']) ? $json['produtos'] : array();
    return is_array($lista) ? $lista : array();
}

function admin_write_products($produtos) {
    $dir = dirname(ADMIN_DATA_FILE);
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    $json = json_encode(array('produtos' => $produtos), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return @file_put_contents(ADMIN_DATA_FILE, $json) !== false;
}

function admin_find_product($produtos, $id) {
    foreach ($produtos as $p) {
        if (isset($p['id']) && $p['id'] === $id) {
            return $p;
        }
    }
    return null;
}

function admin_ext_ok($ext) {
    return in_array(strtolower((string)$ext), array('jpg', 'jpeg', 'png', 'webp'));
}

function admin_salvar_imagem($tmp, $nome_original, $nome) {
    $info = @getimagesize($tmp);
    $mime = $info ? $info['mime'] : '';
    $ext = strtolower(pathinfo((string)$nome_original, PATHINFO_EXTENSION));
    if (!admin_ext_ok($ext)) {
        $ext = 'jpg';
    }

    if (function_exists('imagecreatetruecolor') && function_exists('imagewebp')) {
        $im = null;
        switch ($mime) {
            case 'image/jpeg': $im = @imagecreatefromjpeg($tmp); break;
            case 'image/png':  $im = @imagecreatefrompng($tmp); break;
            case 'image/webp': $im = @imagecreatefromwebp($tmp); break;
            default: $im = null;
        }
        if ($im) {
            $w = imagesx($im);
            $h = imagesy($im);
            $max = 1200;
            if ($w > $max || $h > $max) {
                $nova_w = $w;
                $nova_h = $h;
                if ($w >= $h) {
                    $nova_w = $max;
                    $nova_h = (int)round($h * $max / $w);
                } else {
                    $nova_h = $max;
                    $nova_w = (int)round($w * $max / $h);
                }
                $copia = imagecreatetruecolor($nova_w, $nova_h);
                imagecopyresampled($copia, $im, 0, 0, 0, 0, $nova_w, $nova_h, $w, $h);
                imagedestroy($im);
                $im = $copia;
            }
            if (!is_dir(ADMIN_UPLOAD_DIR)) {
                @mkdir(ADMIN_UPLOAD_DIR, 0755, true);
            }
            $arq = ADMIN_UPLOAD_DIR . $nome . '.webp';
            if (@imagewebp($im, $arq, 82)) {
                imagedestroy($im);
                return $nome . '.webp';
            }
            imagedestroy($im);
        }
    }

    if (!is_dir(ADMIN_UPLOAD_DIR)) {
        @mkdir(ADMIN_UPLOAD_DIR, 0755, true);
    }
    $destino = ADMIN_UPLOAD_DIR . $nome . '.' . $ext;
    if (@move_uploaded_file($tmp, $destino)) {
        return $nome . '.' . $ext;
    }
    return '';
}

function admin_process_uploads() {
    $salvas = array();
    if (empty($_FILES['fotos'])) {
        return $salvas;
    }
    $files = $_FILES['fotos'];
    if (!is_array($files['name'])) {
        $files = array(
            'name' => array($files['name']),
            'type' => array($files['type']),
            'tmp_name' => array($files['tmp_name']),
            'error' => array($files['error']),
            'size' => array($files['size'])
        );
    }
    $n = count($files['name']);
    for ($i = 0; $i < $n; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        if ((int)$files['size'][$i] > 12 * 1024 * 1024) {
            continue;
        }
        $nome = 'foto-' . date('Ymd_His') . '-' . rand(1000, 9999);
        $arquivo = admin_salvar_imagem($files['tmp_name'][$i], $files['name'][$i], $nome);
        if ($arquivo !== '') {
            $salvas[] = array('src' => ADMIN_UPLOAD_URL . $arquivo, 'alt' => '');
        }
    }
    return $salvas;
}

function admin_categoria_label($cat) {
    $mapa = array(
        'sagradas' => 'Sagradas',
        'decorativas' => 'Decorativas',
        'lembrancinhas' => 'Lembrancinhas'
    );
    return isset($mapa[$cat]) ? $mapa[$cat] : 'Sagradas';
}