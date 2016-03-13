<?php 
// Prepare classes
$classes[] = "block";
if (isset($class)) {
    $classes[] = $class;
}

// Prepare title
$title = isset($title) && !empty($title)? $title : null;
$header = isset($header) ? $header : $title; 

// Prepare content
$content = isset($content) ? $content : null;
$text = isset($text) ? $text : $content;

?><div <?= $this->classList($classes) ?>>

    <?php if (isset($header)) : ?>
        <h4><?= $header ?></h4>
    <?php endif; ?>

    <?php if (isset($text)) : ?>
        <p><?= $text ?></p>
    <?php endif; ?>

    <?php if (isset($links)) : 
        $this->renderView("default/link-list", [
            "links" => $links
        ]); 
    endif; ?>

</div>
