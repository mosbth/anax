<?php
// Prepare classes
$classes[] = "meta-footer";
if (isset($class)) {
    $classes[] = $class;
}

// Labels
$categoryLabel = $categoryLabel
    ? $categoryLabel
    : t("Category: "); 



?>
<footer <?= $this->classList($classes) ?>>
    <?php if (isset($category)) : ?>
        <span><?= $categoryLabel ?></span>
        <ul>
            <?php foreach ($category as $name) : ?>
                <li><a href="#"><?= $name ?></a></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</footer>
