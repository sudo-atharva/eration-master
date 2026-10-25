<?php
// Demo mode: minimal mysqli_* stand-ins backed by SQLite (demo.sqlite at project root).
// Requires the mysqli extension to be DISABLED and pdo_sqlite ENABLED in php.ini.
// ponytail: covers only the mysqli calls this app uses; add more if new code calls others.
if (!function_exists('mysqli_connect')) {
  define('MYSQLI_ASSOC', 1);
  define('MYSQLI_NUM', 2);
  define('MYSQLI_BOTH', 3);

  class DemoResult {
    public $rows; public $i = 0;
    function __construct($rows) { $this->rows = $rows; }
  }

  function mysqli_connect($host = null, $user = null, $pass = null, $db = null) {
    $pdo = new PDO('sqlite:' . __DIR__ . '/../demo.sqlite');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $pdo->sqliteCreateFunction('NOW', fn() => date('Y-m-d H:i:s'), 0);
    $pdo->sqliteCreateFunction('CURDATE', fn() => date('Y-m-d'), 0);
    return $pdo;
  }
  function mysqli_connect_error() { return ''; }
  function mysqli_error($conn) { return $conn->errorInfo()[2] ?? ''; }
  function mysqli_real_escape_string($conn, $s) { return str_replace("'", "''", (string)$s); }
  function mysqli_insert_id($conn) { return (int)$conn->lastInsertId(); }

  function mysqli_query($conn, $sql) {
    $stmt = $conn->query($sql);
    if (!$stmt) return false;
    return $stmt->columnCount() > 0 ? new DemoResult($stmt->fetchAll(PDO::FETCH_ASSOC)) : true;
  }
  function mysqli_num_rows($r) { return $r instanceof DemoResult ? count($r->rows) : 0; }
  function mysqli_fetch_assoc($r) {
    return ($r instanceof DemoResult && $r->i < count($r->rows)) ? $r->rows[$r->i++] : null;
  }
  function mysqli_fetch_array($r, $mode = MYSQLI_BOTH) {
    $row = mysqli_fetch_assoc($r);
    if ($row === null || $mode == MYSQLI_ASSOC) return $row;
    return $mode == MYSQLI_NUM ? array_values($row) : array_values($row) + $row;
  }
  function mysqli_fetch_all($r, $mode = MYSQLI_NUM) {
    $out = [];
    while (($row = mysqli_fetch_array($r, $mode)) !== null) $out[] = $row;
    return $out;
  }
}
