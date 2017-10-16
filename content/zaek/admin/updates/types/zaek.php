<?php
/**
 * @var $this \zaek\engine\CMain
 */
$this->template()->setTitle('Модули zaek');
?>
<table class="table">
    <tbody>
    <tr>
        <td>Модуль для разработки сервисов</td>
        <td>
            <?php
            if ( file_exists($this->fs()->convertPath('%ADMIN_ROOT%/updates/current/zaek_dev.php')) ) {
                echo '<a href="#" class="update_module" data-module="za-ek/zaek_dev" data-source="github">Обновить</a> <small>Текущая версия: '.(include $this->fs()->convertPath('%ADMIN_ROOT%/updates/current/zaek_dev.php')).'</small>';
            } else {
                echo '<a href="#" class="update_module" data-module="za-ek/zaek_dev" data-source="github">Установить</a>';
            }
            ?>
        </td>
    </tr>
    <tr><td>Главный модуль (нестабильная версия)</td><td><a href="#" class="update_module" data-module="za-ek/zaek" data-source="github">Установить</a></td></tr>
    </tbody>
</table>
<script>
$('.update_module').click(function() {
    $(this).zAjax({
        url : '/zaek/admin/updates/run.php',
        params : {
            module : $(this).attr('data-module'),
            source : $(this).attr('data-source')
        }
    }, function(e) {
        if ( !e.error ) {
            alert('Обновлено');
        } else {
            alert('Ошибка');
        }
        console.log(e);
    })
});
</script>