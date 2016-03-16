<?php 
// Prepare classes
$classes[] = "block toc";
if (isset($class)) {
    $classes[] = $class;
}

// Prepare title
$title = isset($title) && !empty($title)? t("Table Of Content") : null;

?><div <?= $this->classList($classes) ?>>

    <h4><?= $title ?></h4>
    
    <ul class="toc">
        <?php foreach ($toc as $url => $page) : ?>
        <li class="level-<?= $page["level"] ?>"><a href="<?= $this->url($url) ?>"><?= $page["title"] ?></a></li>
        <?php endforeach; ?>
    </ul>

</div>
