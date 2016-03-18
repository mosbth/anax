<?php
// Prepare classes
$classes[] = "blog-list";
if (isset($class)) {
    $classes[] = $class;
}

// Labels
$categoryLabel = isset($label["category"])
    ? $label["category"]
    : null; 

$readmoreLabel = isset($label["readmore"])
    ? $label["readmore"]
    : t("Read more »"); 



?><section <?= $this->classList($classes) ?>>
    <?php
    // Loop through all items and display
    foreach ($toc as $route => $content) :
        $item = $this->getContentForRoute($route);
        //var_dump($item);
        //var_dump(get_defined_vars());

        // TODO Format the date
        $datetime = $item["published"];
        $date = $item["published"];
        
        $category = isset($item["category"]) ? $item["category"] : null;

        // Format the content
        $urlToPost = $this->url($route);
        $excerpt = $item["excerpt"];
        
        // Wrap h1 with link to article
        $excerpt = $this->wrapElementContentWithStartEnd(
            $excerpt,
            "h1",
            "<a href=\"$urlToPost\">",
            "</a>",
            1
        );



        ?><section <?= $this->classList("blog-list-item") ?>>

            <span class="meta-header"><time datetime="<?= $datetime ?>"><?= $date ?></time></span>
            
            <?= $excerpt ?>
            
            <p class="readmore"><a href="<?= $urlToPost ?>"><?= $readmoreLabel ?></a></p>

            <?php 
            $this->renderView("default/blog-meta-footer", [
                "category" => $category,
                "categoryLabel" => $categoryLabel,
            ]); 
            ?>

        </section>
    <?php endforeach; ?>

    <footer>
        <hr>
        <p>Displaying <?= $meta["displayedItems"] ?> out of <?= $meta["totalItems"] ?>.</p>
    </footer>
</section>
