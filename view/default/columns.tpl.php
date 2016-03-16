<?php 
// Prepare the classes and allow $column to add own $class.
$outerClass = isset($class) ? $class : null;
$class = null;

?><div <?= $this->classList("$outerClass-wrapper") ?>>

<?php $i = 1; foreach ($columns as $column) : ?>
    <div <?= $this->classList("$outerClass") ?>>

        <?php 
        $column["classes"] = ["$outerClass-x", "$outerClass-$i"];
        $this->renderView("default/block", $column);
         ?>

    </div>
<?php $i++; endforeach; ?>

</div>
