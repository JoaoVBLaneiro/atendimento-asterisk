<?php
include 'config.php';

// Conecta via ODBC
$conn = odbc_connect($odbc_dsn, $db_user, $db_pass);

if (!$conn) {
    die("Falha na conexão ODBC: " . odbc_errormsg());
}

// Consulta CDR
$sql = "SELECT calldate, src, dst, disposition, duration FROM cdr ORDER BY calldate DESC LIMIT 20";
$rs = odbc_exec($conn, $sql);

if (!$rs) {
    die("Erro na consulta: " . odbc_errormsg());
}

echo "<h2>Últimas 20 ligações</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Data/Hora</th><th>De</th><th>Para</th><th>Status</th><th>Duração (s)</th></tr>";

while (odbc_fetch_row($rs)) {
    $calldate = odbc_result($rs, "calldate");
    $src = odbc_result($rs, "src");
    $dst = odbc_result($rs, "dst");
    $disposition = odbc_result($rs, "disposition");
    $duration = odbc_result($rs, "duration");

    echo "<tr>
            <td>$calldate</td>
            <td>$src</td>
            <td>$dst</td>
            <td>$disposition</td>
            <td>$duration</td>
          </tr>";
}

echo "</table>";

// Fecha conexão
odbc_close($conn);
?>
