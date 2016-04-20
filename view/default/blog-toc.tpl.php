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

// Next and previous page
$nextStr     = t("Next »");
$previousStr = t("« Previous");
$nextPageUrl     = $meta["nextPageUrl"];
$previousPageUrl = $meta["previousPageUrl"];
$currentPage = $meta["currentPage"];
$totalPages  = $meta["totalPages"];
$pageStr = t("!CURRENT_PAGE (!TOTAL_PAGES)", [
    "!CURRENT_PAGE" => $currentPage,
    "!TOTAL_PAGES" => $totalPages,
]);



?><div <?= $this->classList($classes) ?>>

    <h4><?= $title ?></h4>
    
    <ul class="toc">
        <?php foreach ($toc as $route => $item) : ?>
        <li><a href="<?= $this->url($route) ?>"><?= $item["title"] ?></a></li>
        <?php endforeach; ?>
    </ul>

    <footer>
        <?php 
        $this->renderView("default/blog-toc-next-prev-page", [
            "meta" => $meta,
        ]); 
        ?>
    </footer>

</div>
