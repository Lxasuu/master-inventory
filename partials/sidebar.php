<?php
// dist/partials/sidebar.php

$BASE_URL  = $BASE_URL ?? "/HTML/";

$fullName  = $fullName ?? ($_SESSION['user']['full_name'] ?? 'User');
$role      = $role ?? ($_SESSION['user']['role'] ?? 'User');
$photo     = $photo ?? get_user_photo("");

// menu aktif lebih stabil
$activeMenu = $activeMenu ?? '';
?>

<div class="vertical-menu">
  <div data-simplebar class="h-100">

    <div class="user-sidebar text-center">
      <div class="dropdown">
        <div class="user-img">
          <img src="<?= htmlspecialchars($photo) ?>" alt="" class="rounded-circle">
          <span class="avatar-online bg-success"></span>
        </div>

        <div class="user-info">
          <h5 class="mt-3 font-size-16 text-white"><?= htmlspecialchars($fullName) ?></h5>
          <span class="font-size-13 text-white-50"><?= htmlspecialchars($role) ?></span>
        </div>
      </div>
    </div>

    <div id="sidebar-menu">
      <ul class="metismenu list-unstyled" id="side-menu">
        <li class="menu-title">MENU</li>

        <li class="<?= $activeMenu === 'dashboard' ? 'mm-active' : '' ?>">
          <a href="<?= $BASE_URL ?>index.php"
             class="waves-effect <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
            <i class="mdi mdi-view-dashboard-outline"></i>
            <span>Dashboard</span>
          </a>
        </li>

        <?php if (function_exists('can') && can(['admin'])): ?>
          <li class="<?= $activeMenu === 'users' ? 'mm-active' : '' ?>">
            <a href="<?= $BASE_URL ?>users/index.php"
               class="waves-effect <?= $activeMenu === 'users' ? 'active' : '' ?>">
              <i class="dripicons-user"></i>
              <span>Pengguna</span>
            </a>
          </li>
        <?php endif; ?>

        <?php if (function_exists('can') && can(['pic','admin'])): ?>
          <li class="<?= $activeMenu === 'pcs' ? 'mm-active' : '' ?>">
            <a href="<?= $BASE_URL ?>pcs/index.php"
               class="waves-effect <?= $activeMenu === 'pcs' ? 'active' : '' ?>">
              <i class="mdi mdi-desktop-classic"></i>
              <span>Data PC</span>
            </a>
          </li>
        <?php endif; ?>

        <!-- <li class="<?= $activeMenu === 'profile' ? 'mm-active' : '' ?>">
          <a href="<?= $BASE_URL ?>profile/profile.php"
             class="waves-effect <?= $activeMenu === 'profile' ? 'active' : '' ?>">
            <i class="mdi mdi-account-circle-outline"></i>
            <span>Profile</span>
          </a>
        </li> -->

      </ul>
    </div>

  </div>
</div>
