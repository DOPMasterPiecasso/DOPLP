<?php
function affiliateLayoutHead($title, $description) {
    $title = (string)$title;
    $description = (string)$description;
    ?>
<!doctype html>
<html lang="id">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="theme-color" content="#00c0ef">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
  <meta name="description" content="<?= htmlspecialchars($description, ENT_QUOTES, 'UTF-8') ?>">
  <link href="/ico/favicon.png" rel="shortcut icon">
  <link rel="stylesheet" href="/css/affiliate.css?v=<?= filemtime(__DIR__ . '/../css/affiliate.css') ?>">
</head>

<body class="af-body">
    <?php
}

function affiliateAuthVisual() {
    ?>
    <div class="af-auth-visual">
      <div class="af-auth-visual__content">
        <div class="af-auth-visual__icon">
          <i class="mi">handshake</i>
        </div>
        <h1 class="af-auth-visual__title">Program Mitra Affiliate dopagency</h1>
        <p class="af-auth-visual__desc">Ubah kenalan pemilik fotokopi & percetakan jadi komisi Rp100.000 – Rp250.000 per klien.</p>
      </div>
    </div>
    <?php
}

function affiliateLayoutFoot() {
    ?>
  <footer class="af-footer">
    &copy; <?= date('Y') ?> dopagency &middot; Program Mitra Affiliate
  </footer>
</body>

</html>
    <?php
}

function affiliateNotice($message, $type = 'success') {
    if ($message === '' || $message === null) {
        return;
    }
    $icons = [
        'success' => 'check_circle',
        'error' => 'error',
        'info' => 'info',
    ];
    $icon = $icons[$type] ?? $icons['info'];
    $class = in_array($type, ['success', 'error', 'info'], true) ? $type : 'info';
    echo '<div class="af-notice' . ($class === 'success' ? '' : ' is-' . $class) . '" role="alert">'
        . '<i class="mi">' . $icon . '</i>'
        . '<p>' . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>'
        . '</div>';
}

function affiliateField($name, $label, $icon, $value, $options = []) {
    $type = $options['type'] ?? 'text';
    $required = !empty($options['required']);
    $placeholder = $options['placeholder'] ?? '';
    $full = !empty($options['full']);
    $hint = $options['hint'] ?? '';
    $autocomplete = $options['autocomplete'] ?? '';
    $id = 'af-' . $name;
    ?>
  <div class="af-field<?= $full ? ' af-field--full' : '' ?>">
    <label for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
      <i class="mi"><?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?></i>
      <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
      <?php if ($required): ?><span class="req">*</span><?php endif; ?>
    </label>
    <div class="af-control">
      <i class="mi"><?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?></i>
      <?php if ($type === 'textarea'): ?>
        <textarea id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
          name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
          placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>"
          <?= $required ? 'required' : '' ?>><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></textarea>
      <?php elseif ($type === 'select'): ?>
        <select id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
          name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
          <?= $required ? 'required' : '' ?>>
          <?php foreach (($options['choices'] ?? []) as $key => $text): ?>
            <option value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?>"
              <?= (string)$value === (string)$key ? 'selected' : '' ?>>
              <?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>
            </option>
          <?php endforeach; ?>
        </select>
      <?php else: ?>
        <input type="<?= htmlspecialchars($type, ENT_QUOTES, 'UTF-8') ?>"
          id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>"
          name="<?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>"
          value="<?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?>"
          placeholder="<?= htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') ?>"
          <?= $autocomplete !== '' ? 'autocomplete="' . htmlspecialchars($autocomplete, ENT_QUOTES, 'UTF-8') . '"' : '' ?>
          <?= $required ? 'required' : '' ?>>
      <?php endif; ?>
    </div>
    <?php if ($hint !== ''): ?>
      <span class="af-field__hint"><?= htmlspecialchars($hint, ENT_QUOTES, 'UTF-8') ?></span>
    <?php endif; ?>
  </div>
    <?php
}
