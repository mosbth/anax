<?php
// Prepare classes
$classes[] = "next-previous";
if (isset($class)) {
    $classes[] = $class;
}



?><div <?= $this->classList($classes) ?>>
    <?php if (isset($next)) : ?>
    <div class="next"><a href="<?= $this->url($next["route"]) ?>"><?= $next["title"] ?></a> »</div>
    <?php endif; ?>

    <?php if (isset($previous)) : ?>
    <div class="previous">« <a href="<?= $this->url($previous["route"]) ?>"><?= $previous["title"] ?></a></div>
    <?php endif; ?>
</div>
