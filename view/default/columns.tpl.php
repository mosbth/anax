<div class="<?= "$class-wrapper" ?>">

<?php $i = 1; foreach ($columns as $column) : ?>
    <div class="<?= $class ?>">
        <div class="<?= "$class-$i" ?>">

            <h4><?= $column["header"] ?></h4>

            <?php if (isset($column["text"])) : ?>
                <p><?= $column["text"] ?></p>
            <?php endif; ?>

            <?php if (isset($column["links"])) : 
                $this->renderView("default/link-list", [
                    "links" => $column["links"]
                ]); 
            endif; ?>

        </div>
    </div>
<?php $i++; endforeach; ?>

</div>
