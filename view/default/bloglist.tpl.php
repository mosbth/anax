<?php
// Prepare classes
$classes[] = "blog-list";
if (isset($class)) {
    $classes[] = $class;
}

?><section <?= $this->classList($classes) ?>>
    <?php
    // Loop through all items and display
    foreach ($toc as $route => $content) :
        extract($content);
        extract($this->getContentForRoute($route));
        //var_dump(get_defined_vars());

        // TODO Format the date
        $datetime = $published;
        $date = $published;

        ?><section <?= $this->classList("blog-list-item") ?>>

            <span class="meta-header"><time datetime="<?= $datetime ?>"><?= $date ?></time></span>
            <?= $content ?>

        </section>
    <?php endforeach; ?>

    <footer>
        <hr>
        <p>Displaying <?= $meta["displayedItems"] ?> out of <?= $meta["totalItems"] ?>.</p>
    </footer>
</section>
