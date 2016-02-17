<!doctype html>
<html class="<?= implode(", ", $htmlClass) ?>" lang="<?= $lang ?>">
<head>

    <meta charset="<?= $charset ?>">
    <title><?= $title . $title_append ?></title>

    <?php if (isset($favicon)) : ?>
    <link rel="icon" href="<?= $this->url->asset($favicon) ?>">
    <?php endif; ?>

    <?php foreach ($stylesheets as $stylesheet) : ?>
    <link rel="stylesheet" type="text/css" href="<?= $this->url->asset($stylesheet) ?>">
    <?php endforeach; ?>

    <?php if (isset($style)) : ?>
    <style><?= $style ?></style>
    <?php endif; ?>

</head>

<body class="<?= implode(", ", $bodyClass) ?>">

<!-- wrapper around all items on page -->
<div class="wrapper">

<!-- siteheader -->
<?php if ($this->views->hasContent("header")) : ?>
<header class="siteheader">
<?php $this->views->render("header")?>
</header>
<?php endif; ?>

<!-- navbar -->
<?php if ($this->views->hasContent("navbar")) : ?>
<nav class="navbar">
<?php $this->views->render("navbar")?>
</nav>
<?php endif; ?>

<!-- main -->
<?php if ($this->views->hasContent("main")) : ?>
<main class="main">
<?php $this->views->render("main")?>
</main>
<?php endif; ?>

<!-- sitefooter -->
<?php if ($this->views->hasContent("footer")) : ?>
<footer class="sitefooter">
<?php $this->views->render("footer")?>
</footer>
<?php endif; ?>

</div> <!-- wrapper -->

<!-- render javascripts -->
<?php if (isset($javascripts)) : foreach ($javascripts as $javascript) : ?>
<script src="<?=$this->url->asset($javascript)?>"></script>
<?php endforeach; endif; ?>

<!-- useful for inline javascripts such as google analytics-->
<?php if ($this->views->hasContent("body-end")) : ?>
<?php $this->views->render("body-end")?>
<?php endif; ?>

</body>
</html>
