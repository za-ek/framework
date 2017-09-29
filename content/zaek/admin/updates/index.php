<section class="row">
    <div class="col-lg-6">
        <div class="box fixed-box">
            <div class="box-header">
                <h3 class="box-title">Загрузка обновлений</h3>
            </div>
            <div class="box-body">
                <table class="table table-hover table-row-click">
                    <tbody>
                        <tr data-source="zaek"><td>Расширения zaek</td></tr>
                        <tr data-source="github"><td>GitHub</td></tr>
                    <?php
                    if ( file_exists(__DIR__ . '/list.php') ) {
                        $this->includeFile(__DIR__ . '/list.php');
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <div class="overlay"><i class="fa fa-spin fa-refresh"></i></div>
        </div>
    </div>
    <div class="col-lg-6" id="update_detail">
        <div class="box fixed-box">
            <div class="box-header">
                <h3 class="box-title">Выберите способ установки</h3>
            </div>
            <div class="box-body">

            </div>
            <div class="overlay"><i class="fa fa-spin fa-refresh"></i></div>
        </div>
    </div>
</section>

<script>
    $(function() {
        $('.table-row-click tr').click(function() {
            var t = $(this).attr('data-source');
            $('#update_detail > .box > .box-body').zAjax({
                url: '/zaek/admin/updates/types/' + t + '.php'
            }, function(r) {
                $('#update_detail .box-body').html(r.content);
                $('#update_detail .box-title').html(r.page_param.title);
            });
        });
    });
</script>