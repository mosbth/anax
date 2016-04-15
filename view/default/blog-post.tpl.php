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


// Get time for publish/update/create
list($pubStr, $published) = $this->getPublishedDate(get_defined_vars());


// Get details on author.
$authorStr = "";
foreach ($author as $who) {
    $authorStr .= "<a rel=\"author\" href=\"/author/${who["acronym"]}\">${who["name"]}</a>, ";
}
$authorStr = substr($authorStr, 0, -2);
$byStr = t("By !AUTHORS.", ["!AUTHORS" => $authorStr]);



// Add meta header to h1
$header = <<<EOD
<header>
    <h1\$1>\$2</h1>
    <p class="meta-header">
    <adress class="author">$byStr</adress>
    $pubStr <time pubdate datetime="$published">$published</time>.
    </p>
</header>
EOD;

$content = preg_replace("#<h1(.*?)>(.*?)</h1>#", $header, $content, 1);



?><article <?= $this->classList($classes) ?> itemscope itemtype="http://schema.org/Article">

    <?= $content ?>

    <?php 
    $this->renderView("default/blog-meta-footer", [
        "category" => $category,
        "categoryLabel" => $categoryLabel,
    ]); 
    ?>

</article>
