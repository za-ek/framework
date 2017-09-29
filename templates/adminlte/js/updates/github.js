$(window).on('zAjax_finish-/zaek/admin/updates/types/github.php', function(e,t,a,b,c) {
    $(t).find('.github_link_form').submit(function() {
        $(this).closest('.box').find('.overlay').show();
        var link = $(this).find('.github_link').val();

        zAjax({
            url: '/zaek/admin/updates/types/github_get_tag_list.php',
            params: {
                repo: link
            }
        }, function (d) {
            try {
                var list = d.result;
                var table = $("<table class='table table-hover'><tbody></tbody></table>");
                for ( var a in list ) {
                    $(table).append('<tr><td>'+list[a].name+'</td><td>' +
                        '<a data-url="'+list[a].zipball_url+'">проверить</a> ' +
                        '</td></tr>');
                }
                $(t).find('.github_content').html(table);

                $(table).find('a').click(function() {
                    $(this).closest('.box').find('.overlay').show();
                    var $this = this;
                    zAjax({
                        url: '/zaek/admin/updates/list_from_zip.php',
                        params: {
                            url : $(this).attr('data-url')
                        }
                    }, function(d) {
                        var block = $('<ul></ul>');
                        block.appendTo($($this).closest('.box'));

                        var length = 1;
                        d.result.map(function(path) {
                            path = path.replace(/\/$/g, '');
                            if ( path.split('/').length > length ) {
                                var ul = $('<ul></ul>');
                                block.append(ul);
                                block = ul;
                            } else if ( path.split('/').length < length ) {
                                while ( length-- > path.split('/').length ) {
                                    block = block.parent();
                                }
                            }

                            var a = path.split('/');
                            block.append('<li>'+a[a.length-1]+'</li>');
                            length = path.split('/').length;
                        });

                        var btn = $('<button data-url="'+$($this).attr('data-url')+'">Установить</button>');
                        $($this).closest('.github_content').append(btn);
                        btn.on('click', function() {
                            $(this).closest('.box').find('.overlay').show();
                            zAjax({
                                url: '/zaek/admin/updates/from_zip.php',
                                params: {
                                    url : $(this).attr('data-url')
                                }
                            }, function(d) {
                                document.location.reload();
                            });
                        });

                        $($this).closest('.box').find('.overlay').hide();
                    });
                });
            } catch ( e ) {
                console.log(e);
                $(t).find('.github_content').html('Возникла ошибка');
            }

            $(t).closest('.box').find('.overlay').hide();
        });

        return false;
    });
});