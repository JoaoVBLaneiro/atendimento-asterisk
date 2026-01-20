<?php
include 'config.php';

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function disp_label($v) {
    $s = trim((string)$v);

    // Se algum dia vier texto já (ANSWERED etc.), mantém
    if ($s !== "" && !ctype_digit($s)) {
        return strtoupper($s);
    }

    // Mapa baseado no que você viu:
    // 8 = atendida (tem billsec)
    // 4 = não atendeu (no answer)
    // 0 = falha/cancel/unknown (vamos tratar como FAILED/UNKNOWN)
    $map = [
        '8' => 'ANSWERED',
        '4' => 'NO ANSWER',
        '2' => 'BUSY',
        '1' => 'FAILED',
        '0' => 'FAILED',
    ];

    return $map[$s] ?? ("CODE_" . $s);
}

// Conecta via ODBC
$conn = odbc_connect($odbc_dsn, $db_user, $db_pass);
if (!$conn) {
    die("Falha na conexão ODBC: " . h(odbc_errormsg()));
}

// ---- filtros via GET ----
$day  = trim($_GET["day"]  ?? "");  // YYYY-MM-DD
$from = trim($_GET["from"] ?? "");  // src
$to   = trim($_GET["to"]   ?? "");  // dst

$where = [];
if ($day !== "") {
    // MariaDB: DATE(calldate) = '2026-01-19'
    $where[] = "DATE(calldate) = '" . addslashes($day) . "'";
}
if ($from !== "") {
    $where[] = "src = '" . addslashes($from) . "'";
}
if ($to !== "") {
    $where[] = "dst = '" . addslashes($to) . "'";
}

$whereSql = count($where) ? ("WHERE " . implode(" AND ", $where)) : "";

$sql = "SELECT calldate, src, dst, disposition, duration, billsec, userfield
        FROM cdr
        $whereSql
        ORDER BY calldate DESC
        LIMIT 200";


$rs = odbc_exec($conn, $sql);
if (!$rs) {
    die("Erro na consulta: " . h(odbc_errormsg()));
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>PABX Mototáxi - Chamadas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,Arial;margin:16px}
    .top{display:flex;justify-content:space-between;align-items:baseline;gap:12px;flex-wrap:wrap}
    table{border-collapse:collapse;width:100%}
    th,td{border:1px solid #ddd;padding:8px;font-size:14px}
    th{background:#f6f6f6;text-align:left}
    .ok{color:#0a7a0a;font-weight:700}
    .bad{color:#b00020;font-weight:700}
    .muted{color:#666}
    .pill{display:inline-block;padding:2px 8px;border-radius:999px;background:#f1f1f1}
  </style>
</head>
<body>

<div class="top">
  <h2>Últimas 50 ligações</h2>
  <div class="muted">Servidor: <?=h(gethostname())?></div>
</div>

<form method="get" style="margin:12px 0; display:flex; gap:8px; flex-wrap:wrap; align-items:end;">
  <div>
    <div class="muted">Data</div>
    <input name="day" placeholder="YYYY-MM-DD" value="<?=h($day)?>" style="padding:8px;">
  </div>
  <div>
    <div class="muted">Origem (src)</div>
    <input name="from" placeholder="1003" value="<?=h($from)?>" style="padding:8px;">
  </div>
  <div>
    <div class="muted">Destino (dst)</div>
    <input name="to" placeholder="600 ou número" value="<?=h($to)?>" style="padding:8px;">
  </div>
  <button type="submit" style="padding:8px 12px;">Filtrar</button>
  <a href="/asterisk/" class="muted" style="padding:8px 0;">Limpar</a>
</form>

<table>
  <tr>
    <th>Data/Hora</th>
    <th>De</th>
    <th>Para</th>
    <th>Status</th>
    <th>Falado (s)</th>
    <th>Duração (s)</th>
    <th>Gravação</th>

  </tr>

  <?php while (odbc_fetch_row($rs)): 
    $calldate = odbc_result($rs, "calldate");
    $src      = odbc_result($rs, "src");
    $dst      = odbc_result($rs, "dst");
    $disp     = odbc_result($rs, "disposition");
    $duration = odbc_result($rs, "duration");
    $billsec  = odbc_result($rs, "billsec");
    $userfield = trim((string)odbc_result($rs, "userfield"));

    $dispText = disp_label($disp);
    $cls = ($dispText === 'ANSWERED') ? 'ok' : 'bad';

    // dst = 's' normalmente é contexto/fila/URA
    $dstPretty = ($dst === 's') ? '(fila/URA)' : $dst;
  ?>
    <tr>
      <td><?=h($calldate)?></td>
      <td><span class="pill"><?=h($src)?></span></td>
      <td><?=h($dstPretty)?></td>
      <td class="<?=$cls?>"><?=h($dispText)?></td>
      <td><?=h($billsec)?></td>
      <td><?=h($duration)?></td>
      <td>
    <?php if ($userfield !== ""): ?>
      <audio controls preload="none" style="width:260px"
        src="<?=h("/asterisk/recordings/stream.php?f=" . urlencode($userfield))?>">
      </audio>
      <div><a href="<?=h("/asterisk/recordings/download.php?f=" . urlencode($userfield))?>">baixar</a></div>
    <?php else: ?>
      <span class="muted">—</span>
    <?php endif; ?>
     </td>

    </tr>
  <?php endwhile; ?>
</table>

<p class="muted">
  Próximo passo: adicionar filtros (data/ramal/número) e listar gravações da fila.
</p>

</body>
</html>
<?php
odbc_close($conn);
