<?php
// partials/head.php
$pageTitle = $pageTitle ?? 'App';
$BASE_URL = $BASE_URL ?? '/HTML/dist/';
?>
<head>
  <meta charset="utf-8" />
  <title><?= htmlspecialchars($pageTitle) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
  <meta content="Themesdesign" name="author" />

  <!-- App favicon -->
  <link rel="shortcut icon" href="<?= $BASE_URL ?>assets/images/META/meta logo.png">

  <!-- plugin css -->
  <link href="<?= $BASE_URL ?>assets/libs/admin-resources/jquery.vectormap/jquery-jvectormap-1.2.2.css" rel="stylesheet" type="text/css" />

  <!-- Bootstrap Css -->
  <link href="<?= $BASE_URL ?>assets/css/bootstrap.min.css" id="bootstrap-style" rel="stylesheet" type="text/css" />
  <!-- Icons Css -->
  <link href="<?= $BASE_URL ?>assets/css/icons.min.css" rel="stylesheet" type="text/css" />
  <!-- App Css-->
  <link href="<?= $BASE_URL ?>assets/css/app.min.css" id="app-style" rel="stylesheet" type="text/css" />
  <link href="<?= $BASE_URL ?>assets/css/app.custom.css" rel="stylesheet" type="text/css" />
</head>
