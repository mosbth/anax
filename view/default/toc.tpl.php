<?php 
// Prepare classes
$classes[] = "block toc";
if (isset($class)) {
    $classes[] = $class;
}

// Prepare title
$title = isset($title) && !empty($title)
    ? $title
    : t("Table Of Content");



?><div <?= $this->classList($classes) ?>>

    <h4><?= $title ?></h4>
    
    <ul class="toc">
        <?php foreach ($toc as $route => $item) : ?>
        <li class="level-<?= $item["level"] ?>"><a href="<?= $this->url($route) ?>"><?= $item["title"] ?></a></li>
        <?php endforeach; ?>
    </ul>

</div>
