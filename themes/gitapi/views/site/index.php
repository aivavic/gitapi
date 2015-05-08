<?php
/* @var $this SiteController */
//$git = new GitAuth2();
//$git->start();
$this->pageTitle = Yii::app()->name;

$git = GitAuth2::getInstance();

$main = $git->apiRequest('https://api.github.com/repos/yiisoft/yii2');

?>
<pre>
    <?php
    var_dump($main);
    ?>
</pre>