$(document).ready(function() {
    window.setTimeout(function() {
        if ( openedHabits !== undefined ) {
            openOpenedHabits();
        }
    }, 3000);

    $('input.date').datepicker({
        dateFormat: 'yy-mm-dd',
        firstDay: 1,
    });

    $('.toggle-habit').click(function(el) {
        const id = $(this).data('habitid');
        if ( $('#content-habit-' + id).is(':visible') ) {
            closeHabit(id, openedHabits !== undefined);
        } else {
            openHabit(id, openedHabits !== undefined);
        }
    });

    $('.next-habit').click(function(el) {
        const id = $(this).data('habitid');
        openNextHabit(id);
    });

    $('.previous-habit').click(function(el) {
        const id = $(this).data('habitid');
        openPreviousHabit(id);
    });

    $('.toggle-allhabits').click(function(el) {
        if ( $('#toggle-habit-all-opener').is(':visible') ) {
            $('.openable').show();
            $('#toggle-habit-all-opener').hide();
            $('#toggle-habit-all-closer').show();
            if ( openedHabits !== undefined ) {
                openedHabits = habitIds.join(',');
                saveOpenedHabits();
            }
        } else {
            $('.openable').hide();
            $('#toggle-habit-all-opener').show();
            $('#toggle-habit-all-closer').hide();
            if ( openedHabits !== undefined ) {
                openedHabits = '';
                saveOpenedHabits();
            }
        }
    });

    $('#add-touch').click(function(el) {
        addTouch();
    });


});

function openHabit(id, save) {
    if ( save === undefined ) save = false;

    if ( save ) {
        let ids = ( openedHabits && openedHabits.length > 0 ) ? openedHabits.split(',') : [];
        if ( !ids.find(i => i == id) ) {
            ids.push(id);
        }
        openedHabits = ids.join(',');
        saveOpenedHabits();
    }


    $('#content-habit-' + id).show();
    $('#toggle-habit-' + id + '-opener').hide();
    $('#toggle-habit-' + id + '-closer').show();
    const el = document.getElementById('toggle-habit-' + id + '-closer');
    if ( el ) el.scrollIntoView(true);
}

function closeHabit(id, save) {
    if ( save === undefined ) save = false;

    if ( save ) {
        let ids = openedHabits.split(',').filter(e => e != id);
        openedHabits = ids.join(',');
        saveOpenedHabits();
    }
    
    $('#content-habit-' + id).hide();
    $('#toggle-habit-' + id + '-opener').show();
    $('#toggle-habit-' + id + '-closer').hide();
}

function openNextHabit(id) {
    let idx = findIdxInHabitIds(id);
    if ( idx === habitIds.length - 1 ) {
        idx = 0;
    } else {
        ++idx;
    }

    $('.openable').hide(); 
    $('.toggle-habit.open').hide();
    $('.toggle-habit.closed').show();
    if ( openedHabits !== undefined ) {
        openedHabits = '';
    }
    openHabit(habitIds[idx], openedHabits !== undefined);
}

function openPreviousHabit(id) {
    let idx = findIdxInHabitIds(id);
    if ( idx === 0 ) {
        idx = habitIds.length - 1;
    } else {
        --idx;
    }

    $('.openable').hide(); 
    $('.toggle-habit.open').hide();
    $('.toggle-habit.closed').show();
    if ( openedHabits !== undefined ) {
        openedHabits = '';
    }
    openHabit(habitIds[idx], openedHabits !== undefined);
}
function saveOpenedHabits() {
    const data = {
        'action': 'save_opened_habits',
        'opened_habits': openedHabits,
    };
    $.ajax('users.php', {
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(data) {
            if (data.status === 'ok') {
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

function findIdxInHabitIds(id) {
    for (let i = 0; i < habitIds.length; ++i) {
        if ( id == habitIds[i] ) {
            return i;
        }
    }
    throw Error('id not found in habitIds');
}

function openOpenedHabits() {
    $('.toggle-habit.open').hide();
    $('.toggle-allhabits.open').hide();
    $('.openable').hide(); 
    openedHabits.split(',').forEach(id => openHabit(id, false));
    if ( openedHabits.split(',').length === habitIds.length ) {
        $('.toggle-allhabits.open').hide();
        $('.toggle-allhabits.closed').show();
    }
    $('#loading-overlay').hide();
}

function confirmDelete(url) {
    if (!confirm('Are you sure?')) return;
    window.location.href = url;
}

function filterDates(startDate, endDate) {
    $('#start-date').val(startDate);
    $('#end-date').val(endDate);
    $('#date-form').submit();
}
function filterDatesBySpan(span) {
    $('#date_start_span').val(span);
    $('#date-form').submit();
}
function useSpecificDates() {
    $('#date_start_span').val('specific_date');
    $('#specific_dates').show();
}
function addTouch() {
    const data = {
        'action': 'add_json',
    };
    $.ajax('touchcounter.php', {
        type: 'POST',
        dataType: 'json',
        data: data,
        success: function(data) {
            if (data.status === 'ok') {
                $('#touches-sum').html(data.touches);
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
