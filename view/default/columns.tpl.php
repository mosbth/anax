<?php 
// Prepare the classes and allow $column to add own $class.
$outerClass = isset($class) ? $class : null;
$class = null;

$classes = isset($classes) ? $classes : null;



?><div <?= $this->classList("columns $outerClass-wrapper", $classes) ?>>

<?php if (isset($title)) : ?>
    <h2><?= $title ?></h2>
<?php endif; ?>

<?php $i = 1; foreach ($columns as $column) : ?>
    <div <?= $this->classList("column $outerClass") ?>>

        <?php 
        $column["classes"] = ["$outerClass-x", "$outerClass-$i"];
        $this->renderView("default/block", $column);
         ?>

    </div>
<?php $i++; endforeach; ?>

</div>
