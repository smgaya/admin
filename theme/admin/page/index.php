<!DOCTYPE html>
<html lang="en-US">
<head>
    <?= $this->theme->file('head') ?>
</head>
<body>
    <?= $this->theme->file('header') ?>

    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="page-header">
                    <?php if(ci()->can_i('create-page')): ?>
                    <a class="btn btn-primary pull-right" href="<?= base_url('/admin/page/0') ?>"><?= _l('Create New') ?></a>
                    <?php endif; ?>
                    <h1><?= $title ?></h1>
                </div>
                
                <div class="row">
                    <?php foreach($pages as $page): ?>
                    <div class="col-md-4">
                        <div class="list-group">
                            <a href="<?= base_url('/admin/page/' . $page->id) ?>" class="list-group-item">
                                <h4 class="list-group-item-heading"><?= $page->title ?></h4>
                                <p class="list-group-item-text"><?= base_url($page->page) ?></p>
                            </a>
                            <a href="<?= base_url($page->page) ?>" class="list-group-closer btn btn-default btn-xs"><i class="glyphicon glyphicon-new-window"></i></a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?= $this->theme->file('foot') ?>
</body>
</html>