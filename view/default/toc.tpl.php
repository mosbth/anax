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

        <?php
        foreach ($toc as $route => $item) {
            $text = $item["title"];
            if ($item["linkable"] !== false) {
                $text = "<a href=\"" . $this->url($route) . "\">$text</a>";
            }
            
            $class = "level-${item["level"]}";
            if ($item["sectionHeader"] === true) {
                $class = "section-header";
            }
            
            ?><li class="<?= $class ?>"><?= $text ?></li><?php
        } ?>

    </ul>

</div>
