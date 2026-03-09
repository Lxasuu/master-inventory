<?php
// Expect variables:
// $pageTitle (string)
// $activeMenu (string) optional
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($pageTitle ?? "App") ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="<?= $BASE_URL ?>assets/images/META/meta logo.png">

  <!-- Morvin CSS -->
  <link href="<?= $BASE_URL ?>assets/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
  <link href="<?= $BASE_URL ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
  <link href="<?= $BASE_URL ?>assets/css/app.min.css" rel="stylesheet" type="text/css" />
  <link href="<?= $BASE_URL ?>assets/css/app.custom.css" rel="stylesheet">
</head>

<body>
<div id="layout-wrapper">
  <?php require __DIR__ . "/topbar.php"; ?>
  <?php require __DIR__ . "/sidebar.php"; ?>

  <div class="main-content">
    <div class="page-content">
