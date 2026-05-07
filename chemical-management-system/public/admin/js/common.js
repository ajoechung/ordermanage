$(function() {
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    $('form').on('submit', function(e) {
        if ($(this).data('ajax') !== false) {
            e.preventDefault();
            var form = $(this);
            var url = form.attr('action') || window.location.href;
            var method = form.attr('method') || 'POST';
            
            $.ajax({
                url: url,
                type: method,
                data: form.serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.code === 1) {
                        alert(res.msg);
                        if (res.url) {
                            window.location.href = res.url;
                        } else {
                            location.reload();
                        }
                    } else {
                        alert(res.msg);
                    }
                },
                error: function() {
                    alert('请求失败，请重试');
                }
            });
        }
    });
    
    $('.btn-delete').on('click', function() {
        var url = $(this).data('url') || $(this).attr('href');
        var message = $(this).data('message') || '确定要删除吗？';
        
        if (confirm(message)) {
            $.post(url, {id: $(this).data('id') || ''}, function(res) {
                if (res.code === 1) {
                    alert(res.msg);
                    location.reload();
                } else {
                    alert(res.msg);
                }
            }, 'json');
        }
    });
    
    $('.btn-confirm').on('click', function() {
        var url = $(this).data('url') || $(this).attr('href');
        var message = $(this).data('message') || '确定要执行此操作吗？';
        
        if (confirm(message)) {
            $.get(url, function(res) {
                if (res.code === 1) {
                    alert(res.msg);
                    location.reload();
                } else {
                    alert(res.msg);
                }
            }, 'json');
        }
    });
    
    $('.btn-ajax').on('click', function() {
        var url = $(this).data('url') || $(this).attr('href');
        var message = $(this).data('message');
        
        if (message && !confirm(message)) {
            return false;
        }
        
        $.get(url, function(res) {
            alert(res.msg);
            if (res.code === 1) {
                location.reload();
            }
        }, 'json');
        
        return false;
    });
    
    $('[data-toggle="tooltip"]').tooltip();
    
    $('.table').on('click', 'tr[data-href]', function() {
        if (!$(event.target).closest('a, button').length) {
            window.location.href = $(this).data('href');
        }
    });
    
    $('.table').on('click', 'tr[data-href]', function() {
        if (!$(event.target).closest('a, button, input').length) {
            window.location.href = $(this).data('href');
        }
    });
    
    $('select.form-control').each(function() {
        if ($(this).find('option').length > 10) {
            $(this).select2();
        }
    });
    
    $('.datepicker').datepicker({
        format: 'yyyy-mm-dd',
        autoclose: true,
        todayHighlight: true
    });
    
    $('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        sideBySide: true
    });
    
    $('[data-dismiss="modal"]').on('click', function() {
        $(this).closest('.modal').modal('hide');
    });
    
    $('.check-all').on('change', function() {
        var checked = $(this).prop('checked');
        $(this).closest('table').find('tbody input[type="checkbox"]').prop('checked', checked);
    });
    
    $('.batch-delete').on('click', function() {
        var ids = [];
        $('table tbody input[type="checkbox"]:checked').each(function() {
            ids.push($(this).val());
        });
        
        if (ids.length === 0) {
            alert('请选择要删除的项');
            return;
        }
        
        if (!confirm('确定要删除选中的 ' + ids.length + ' 项吗？')) {
            return;
        }
        
        $.post($(this).data('url') || $(this).attr('href'), {ids: ids}, function(res) {
            alert(res.msg);
            if (res.code === 1) {
                location.reload();
            }
        }, 'json');
    });
    
    function initSidebar() {
        var url = window.location.pathname;
        $('.nav-sidebar .nav-link').each(function() {
            var href = $(this).attr('href');
            if (href && url.indexOf(href) !== -1) {
                $(this).addClass('active');
                var parent = $(this).closest('.nav-item');
                if (parent.hasClass('has-treeview')) {
                    parent.addClass('menu-open');
                }
            }
        });
    }
    
    initSidebar();
    
    $(window).on('resize', function() {
        if ($(window).width() < 768) {
            $('body').addClass('sidebar-collapse');
        }
    });
});

function showMessage(message, type) {
    var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    var html = '<div class="alert ' + alertClass + ' alert-dismissible fade show" style="position: fixed; top: 70px; right: 20px; z-index: 9999;">' +
        message +
        '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
        '</div>';
    
    $('body').append(html);
    setTimeout(function() {
        $('.alert').alert('close');
    }, 3000);
}

function showLoading() {
    var html = '<div class="modal fade" id="loadingModal" data-backdrop="static"><div class="modal-dialog modal-sm"><div class="modal-content"><div class="modal-body text-center"><i class="fa fa-spinner fa-spin fa-3x"></i><p style="margin-top: 10px;">加载中...</p></div></div></div></div>';
    if (!$('#loadingModal').length) {
        $('body').append(html);
    }
    $('#loadingModal').modal('show');
}

function hideLoading() {
    $('#loadingModal').modal('hide');
}

function confirm(message, callback) {
    if (confirm(message)) {
        callback && callback();
    }
}
