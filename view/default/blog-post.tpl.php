<?php 
// Prepare classes
$classes[] = "article blog-post";
if (isset($class)) {
    $classes[] = $class;
}

// Labels
$categoryLabel = isset($label["category"])
    ? $label["category"]
    : null; 

// Defaults
$category = isset($category) ? $category : null;


?><article <?= $this->classList($classes) ?>>

    <?= $content ?>

    <?php 
    $this->renderView("default/blog-meta-footer", [
        "category" => $category,
        "categoryLabel" => $categoryLabel,
    ]); 
    ?>

</article>
