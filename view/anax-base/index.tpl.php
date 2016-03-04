<!doctype html>
<html <?= $this->classList($htmlClass) ?> lang="<?= $lang ?>">
<head>

    <meta charset="<?= $charset ?>">
    <title><?= $title . $title_append ?></title>

    <?php if (isset($favicon)) : ?>
    <link rel="icon" href="<?= $this->asset($favicon) ?>">
    <?php endif; ?>

    <?php foreach ($stylesheets as $stylesheet) : ?>
    <link rel="stylesheet" type="text/css" href="<?= $this->asset($stylesheet) ?>">
    <?php endforeach; ?>

    <?php if (isset($style)) : ?>
    <style><?= $style ?></style>
    <?php endif; ?>

</head>

<body <?= $this->classList($bodyClass, $currentRoute) ?>>

<!-- wrapper around all items on page -->
<div class="wrapper">

<!-- siteheader -->
<?php if ($this->regionHasContent("header")) : ?>
<header class="siteheader">
<?php $this->renderRegion("header")?>
</header>
<?php endif; ?>

<!-- navbar -->
<?php if ($this->regionHasContent("navbar")) : ?>
<nav class="navbar">
<?php $this->renderRegion("navbar")?>
</nav>
<?php endif; ?>

<!-- main -->
<?php if ($this->regionHasContent("main")) : ?>
<main class="main">
<?php $this->renderRegion("main")?>
</main>
<?php endif; ?>

<!-- sitefooter -->
<?php if ($this->regionHasContent("footer")) : ?>
<footer class="sitefooter">
<?php $this->renderRegion("footer")?>
</footer>
<?php endif; ?>

</div> <!-- wrapper -->

<!-- render javascripts -->
<?php if (isset($javascripts)) : foreach ($javascripts as $javascript) : ?>
<script src="<?=$this->asset($javascript)?>"></script>
<?php endforeach; endif; ?>

<!-- useful for inline javascripts such as google analytics-->
<?php if ($this->regionHasContent("body-end")) : ?>
<?php $this->renderRegion("body-end")?>
<?php endif; ?>

</body>
</html>
