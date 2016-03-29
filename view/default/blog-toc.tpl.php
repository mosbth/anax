<?php
// Prepare classes
$classes[] = "block blog-toc";
if (isset($class)) {
    $classes[] = $class;
}

// Prepare title
$title = isset($title) && !empty($title)
    ? $title
    : t("Current posts");


?><div <?= $this->classList($classes) ?>>

    <h4><?= $title ?></h4>
    
    <ul class="toc">
        <?php foreach ($toc as $route => $item) : ?>
        <li><a href="<?= $this->url($route) ?>"><?= $item["title"] ?></a></li>
        <?php endforeach; ?>
    </ul>

    <footer>
        <p>Displaying <?= $meta["displayedItems"] ?> out of <?= $meta["totalItems"] ?>.</p>
    </footer>

</div>
