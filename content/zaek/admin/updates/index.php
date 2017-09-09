<section class="row">
    <div class="col-lg-6">
        <div class="box fixed-box">
            <div class="box-header">
                <h3 class="box-title">Загрузка обновлений</h3>
            </div>
            <div class="box-body">
                <table class="table table-hover table-row-click">
                    <tbody>
                    <tr data-type="github">
                        <td>Из репозитория github</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="overlay"><i class="fa fa-spin fa-refresh"></i></div>
        </div>
    </div>
    <div class="col-lg-6" id="update_detail">
        Выберите способ установки
    </div>
</section>

<script>
    $(function() {
        $('.table-row-click tr').click(function() {
            var t = $(this).attr('data-type');
            $.ajax({
                url: '/zaek/admin/updates/types/' + t + '.php',
                data : {
                    'zAjax' : 1
                }
            }).done(function(e) {
                $('#update_detail').html(e);
            });
        });
    });
</script>