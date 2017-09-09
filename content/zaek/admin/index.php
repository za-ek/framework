<?php
$this->template()->addProp('title', 'Пример страницы админ-панели');
?>

<div class="box">
    <div class="box-header">
        <h3 class="box-title">Статистика</h3>
    </div>
    <div class="box-body">
        Сегодня:
        <?php
        echo date('d/m/Y', time());
        ?> <br>
        Дата релиза: 08/09/2017<br>
        Прошло:
        <?php
        echo ceil((time() - strtotime('2017-09-08 10:34'))/60);
        ?>
        мин
    </div>
</div>