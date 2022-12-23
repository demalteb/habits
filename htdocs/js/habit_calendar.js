$(document).ready(function() {
    $('.toggle-habit.open').hide();
    $('.toggle-allhabits.open').hide();
    $('.openable').hide();
    if ( habitIds !== undefined ) {
        openHabit(habitIds[0]);
    }

    $('.existing-resolution').click(nextResolutionHandler);
    $('.new-resolution').click(insertHandler);
    $('.existing-resolution .comment.show').click(showCommentInput);
    $('.existing-resolution .comment input').blur(updateCommentHandler);
    $('.month-heading').click(function(e) {
        const monthCombi = $(e.target).data('month-combi');
        $('table.month-table[data-month-combi="' + monthCombi + '"]').toggle();
    });

    function showCommentInput(e) {
        e.preventDefault();
        e.stopPropagation();
        const rdId = $(e.target).data('id');
        $('#comment-' + rdId + ' span').hide();
        $('#comment-' + rdId + ' input').show().focus();
    }
    function nextResolutionHandler(e) {
        const data = {
            'action': 'next_resolution',
            'id': $(e.target).data('id'),
        };
        postResolutionDate($(e.target), data);
    }

    function insertHandler(e) {
        const data = {
            'action': 'insert',
            'habitId': $(e.currentTarget).data('habit-id'),
            'year': $(e.currentTarget).data('year'),
            'month': $(e.currentTarget).data('month'),
            'dom': $(e.currentTarget).data('dom'),
        };
        postResolutionDate($(e.currentTarget), data);
    }
    function updateCommentHandler(e) {
        const data = {
            'action': 'update_comment',
            'id': $(e.target).data('id'),
            'comment': $(e.target).val(),
        };
        postResolutionDate($(e.target).parent().parent(), data);
    }
    function postResolutionDate(elToReplace, data) {
        $.ajax('resolution_date.php', {
            type: 'POST',
            dataType: 'json',
            data: data,
            success: function(data) {
                if (data.status === 'ok') {
                    $(elToReplace).replaceWith(data.html);
                    $('#resolution-date-' + data.resolutionDate.id).click(nextResolutionHandler);
                    $('#resolution-date-' + data.resolutionDate.id + ' .comment.show').click(showCommentInput);
                    $('#resolution-date-' + data.resolutionDate.id + ' .comment input').change(updateCommentHandler);
                } else {
                    console.log(data);
                    alert('ERROR ' + JSON.stringify(data));
                }
            }, 
            error: function(err) {
                console.log(err);
                alert(JSON.stringify(err));
            }
        });
    }
});
