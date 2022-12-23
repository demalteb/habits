$(document).ready(function() {
    $('#loading-overlay').show();
    const colors = [ '#480', '#c33', '#9e9', '#0c0', '#08c', '#fc0', '#0cc', '#e9e', '#068', '#80c', '#c24', '#c80', '#9cf', '#c9f', '#f9c', '#cf9', '#00c', '#0c8', '#0cb' ];
    function add(a, b) {
        return a + b;
    }
    function average(a) {
        return a.length === 0 ? 0 : a.reduce(add, 0) / a.length;
    }

    google.charts.load('current', {'packages':['corechart']});

    const fullHeight = 300;
    const fullWidth = $(window).width() * 0.9;
    const backgroundColor = 'transparent';

    setOnLoadCallbacks();

    function setOnLoadCallbacks() {
        google.charts.setOnLoadCallback(drawSuccessPerDayCharts);
        google.charts.setOnLoadCallback(drawHabitsPerDayChart);
        google.charts.setOnLoadCallback(drawResolutionsCharts);
        google.charts.setOnLoadCallback(drawResolutionsPerWeekCharts);
        google.charts.setOnLoadCallback(drawSuccessCharts);
        google.charts.setOnLoadCallback(drawSuccessPerWeekdayCharts);
        google.charts.setOnLoadCallback(drawStreakCharts);
        google.charts.setOnLoadCallback(drawStreakInTimeCharts);
        google.charts.setOnLoadCallback(drawTendencyCharts);
        google.charts.setOnLoadCallback(drawComparisonChart);

        google.charts.setOnLoadCallback(function() { 
            $('.stats').hide(); 
            if ( $('#date_start_span').val() === 'specific_date' ) {
                $('#specific_dates').show();
            } else {
                $('#specific_dates').hide();
            }
        });
    }

    function drawResolutionsCharts() {
        let allResolutionCounts = {};
        let allResolutionIdsToNames = {};
        var options = {
            'title':'All Resolutions',
            colors: colors,
            backgroundColor: backgroundColor,
        };
        habits.forEach(h => { 
            const resolutionIdsToNames = _.fromPairs(_.map(h.resolutions, r => [ r.id, r.name ]));
            const resolutionCounts = _.fromPairs(_.map(h.resolutions, r => [ r.id, 0 ]));
            
            h.resolutionDates.forEach(rd => { 
                resolutionCounts[rd.resolution.id]++;
                allResolutionCounts[rd.resolution.id]++;
            });

            let data = new google.visualization.DataTable();
            data.addColumn('string', 'Resolution');
            data.addColumn('number', 'Count');
            data.addRows(_.toPairs(resolutionCounts).map(([ id, count ]) => [ resolutionIdsToNames[id], count ]).sort((l1, l2) => l2[1] - l1[1]));

            var chart = new google.visualization.BarChart(document.getElementById('habit-' + h.id + '-resolutions'));
            chart.draw(data, options);
        });
    }
    function drawResolutionsPerWeekCharts() {
        let allResolutionCounts = {};
        let allResolutionIdsToNames = {};
        habits.forEach(h => { 
            let overallWeekIdx = 0;
            const names = [];
            const idsToIdx = {};
            let idx = 0;
            h.resolutions.forEach(r => {
                names.push(r.name);
                idsToIdx[r.id] = idx; // pfuiii
                idx++;
            });
            const firstYear = (new Date(h.resolutionDates[0].date)).getFullYear();   
            const lastYear = (new Date(h.resolutionDates[h.resolutionDates.length - 1].date)).getFullYear();   
            const firstWeek = getWeek(new Date(h.resolutionDates[0].date));
            const lastWeek = getWeek(new Date(h.resolutionDates[h.resolutionDates.length - 1].date));
            let numWeeks;
            if (lastYear === firstYear) {
                numWeeks = (lastWeek - firstWeek + 1);
            } else {
                numWeeks = (52 - firstWeek + 1) + (52 * (lastYear - firstYear - 1)) + lastWeek;
            }

            const resolutionCounts = [];
            for (let i = 0; i < numWeeks; ++i) {
                resolutionCounts[i] = _.fill(Array(names.length), 0);
            }
            
            const weeks = _.fill(Array(numWeeks), '0');
            for (let year = firstYear; year <= lastYear; ++year) {
                let startWeek, endWeek, weekIdx;
                if (year === firstYear) {
                    startWeek = firstWeek;
                } else {
                    startWeek = 1;
                } 
                if (year === lastYear) {
                    endWeek = lastWeek;
                } else {
                    endWeek = 52;
                }
                for (let week = startWeek; week <= endWeek; ++week) {
                    let weekIdx = getWeekIdx(week, year, firstWeek, firstYear);
                    if (week === 1 || overallWeekIdx % (Math.floor(numWeeks / 6)) === 0) {
                        weeks[weekIdx] = year + '/' + week;
                    } else {
                        weeks[weekIdx] = '' + week;
                    }
                    overallWeekIdx++;
                }
            }

            h.resolutionDates.forEach(rd => {                     
                const week = getWeek(new Date(rd.date));
                const year = (new Date(rd.date)).getFullYear();
                const weekIdx = getWeekIdx(week, year, firstWeek, firstYear);

                if (weeks[weekIdx] === '0') {
                    weeks[weekIdx] = year + '/' + week;
                }

                const idx = idsToIdx[rd.resolution.id];

                if (resolutionCounts[weekIdx] === undefined) {
                    resolutionCounts[weekIdx] = [];
                }
                resolutionCounts[weekIdx][idx]++;
            });

            let data = new google.visualization.DataTable();
            data.addColumn('string', 'Week');
            data.addColumn({role: 'style'});
            h.resolutions.forEach(r => data.addColumn('number', r.name));
            for (let i = 0; i < weeks.length; ++i) {
                data.addRow([weeks[i]].concat(['stroke-width: 4']).concat(resolutionCounts[i]));
            }

            var chart = new google.visualization.ColumnChart(document.getElementById('habit-' + h.id + '-resolutionsPerWeek'));
            chart.draw(data, {
                'title': h.name + ': Resolutions over time',
                colors: colors,
                backgroundColor: backgroundColor,
            });
        });

        function getWeekIdx(week, year, firstWeek, firstYear) {
            let weekIdx = 0;
            if (year === firstYear) {
                weekIdx = week - firstWeek;
            } else {
                weekIdx = (week - 1) + (year - firstYear - 1) * 52 + (52 - firstWeek);
            }
            return weekIdx;
        }

    }
    function drawSuccessCharts() {
        let allSuccessSum = 0;
        let allFailSum = 0;
        let allCount = 0;
        var options = {
            'title':'Success Rate',
            colors: colors,
            backgroundColor: backgroundColor,
        };
        habits.forEach(h => { 
            let successSum = 0;
            let failSum = 0;
            let count = 0;
            h.resolutionDates.forEach(rd => { 
                successSum += rd.resolution.fulfilmentPercent / 100; 
                allSuccessSum += rd.resolution.fulfilmentPercent / 100;
                failSum += (1 - (rd.resolution.fulfilmentPercent / 100)); 
                allFailSum += (1 - (rd.resolution.fulfilmentPercent / 100)); 
                ++count; 
                ++allCount;
            });

            let data = new google.visualization.DataTable();
            data.addColumn('string', 'Category');
            data.addColumn('number', 'Rate');
            data.addRows([
              ['Success', successSum / count],
              ['Fail', failSum / count ],
            ]);


            var chart = new google.visualization.PieChart(document.getElementById('habit-' + h.id + '-successPercent'));
            chart.draw(data, options);
        });
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Category');
        data.addColumn('number', 'Rate');
        data.addRows([
          ['Success', allSuccessSum / allCount],
          ['Fail', allFailSum / allCount ],
        ]);
        var chart = new google.visualization.PieChart(document.getElementById('allhabits-successPercent'));
        chart.draw(data, options);

    }

    function drawSuccessPerDayCharts() {
        let allRows = {};
        habits.forEach(h => { 
            let data = new google.visualization.DataTable();
            data.addColumn('date', 'Date');
            data.addColumn('number', 'Success');
            data.addColumn({ 'role': 'style' });
            data.addColumn({ 'role': 'emphasis' });
            data.addColumn('number', 'Fail');
            data.addColumn({ 'role': 'style' });
            data.addColumn({ 'role': 'emphasis' });
            data.addColumn({ 'role': 'annotation' });
            data.addColumn({ 'role': 'style' });

            h.resolutionDates.forEach(rd => {
                if (allRows[rd.date] === undefined) {
                    allRows[rd.date] = [];
                }
                allRows[rd.date].push([Number(rd.resolution.fulfilmentPercent), 100 - Number(rd.resolution.fulfilmentPercent)]);
            });

            data.addRows(h.resolutionDates.map(rd => [ 
                new Date(rd.date), 
                Number(rd.resolution.fulfilmentPercent), 
                ((rd.date === formatDate(new Date())) ? 'color: #260' : null),
                ((rd.date === formatDate(new Date())) ? 'true' : 'false'),
                100-Number(rd.resolution.fulfilmentPercent), 
                ((rd.date === formatDate(new Date())) ? 'color: #a22' : null),
                ((rd.date === formatDate(new Date())) ? 'true' : 'false'),
                [
                    ((new Date(rd.date).getDay()) === 1 ? 'Mo' : null),
                ].filter(e => e !== null).join(', '),
                [
                    (rd.isInPause ? 'opacity: 0.5' : null),
                ].filter(e => e !== null).join(';'),
            ]));

            var options = { 
                title: h.name + ": Daily Success", 
                isStacked: true, 
                colors: colors,
                width: fullWidth,
                height: fullHeight,
                backgroundColor: backgroundColor,
            };
            var chart = new google.visualization.ColumnChart(document.getElementById('habit-' + h.id + '-successPerDay'));
            chart.draw(data, options);
        });
        
        let allData = new google.visualization.DataTable();
        allData.addColumn('date', 'Date');
        allData.addColumn('number', 'Success');
        allData.addColumn('number', 'Fail');
        allData.addColumn({ 'role': 'annotation' });
        allData.addRows(_.toPairs(allRows).map(([date, lists]) => [ new Date(date), average(lists.map(l => l[0])), average(lists.map(l => l[1])), (new Date(date).getDay()) === 1 ? 'Mo' : ''  ]));
        allData.addRows([[new Date(), null, null, null]]);
        var options = { 
            title: "Daily Success (Average)", 
            isStacked: true,
            colors: colors,
            width: fullWidth,
            height: fullHeight,
            backgroundColor: backgroundColor,
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('allhabits-successPerDay'));
        chart.draw(allData, options);
    }

    function drawHabitsPerDayChart() {
        let allRows = {};
        let names = [];
        let idx = {}
        habits.forEach((h, i) => { 
            names.push(h.name);
            idx[h.id] = i;
        });
        habits.forEach(h => { 
            h.resolutionDates.forEach(rd => {
                if (allRows[rd.date] === undefined) {
                    allRows[rd.date] = _.fill(Array(names.length), 0);
                }
                allRows[rd.date][idx[h.id]] = Number(rd.resolution.fulfilmentPercent);
            });
        });
        
        let data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        names.forEach(n => data.addColumn('number', n));
        data.addRows(_.toPairs(allRows).map(([date, list]) => [ new Date(date) ].concat(list)));
        data.addRows([[new Date()].concat(_.fill(Array(names.length), 0))]);
        var options = { 
            title: "Daily Success Per Habit", 
            isStacked: true, 
            colors: colors,
            width: fullWidth,
            height: fullHeight * 2,
            backgroundColor: backgroundColor,
        };
        var chart = new google.visualization.ColumnChart(document.getElementById('allhabits-successPerHabitsPerDay'));
        chart.draw(data, options);
    }


    function drawSuccessPerWeekdayCharts() {
        const daynames = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'];
        const allDows = [[], [], [], [], [], [], []];
        habits.forEach(h => { 
            const dows = [[], [], [], [], [], [], []];
            h.resolutionDates.forEach(rd => { 
                dows[(7 + (new Date(rd.date)).getDay() - 1) % 7].push(Number(rd.resolution.fulfilmentPercent)); 
                allDows[(7 + (new Date(rd.date)).getDay() - 1) % 7].push(Number(rd.resolution.fulfilmentPercent)); 
            });

            const data = new google.visualization.DataTable();
            data.addColumn('string', 'Wochentag');
            data.addColumn('number', 'Avg. Success');
            data.addRows(dows.map((d, i) => [daynames[i], (d.length ? (d.reduce(add, 0) / d.length) : 0)]));

            const options = {
                title: "Avg per weekday",
                colors: colors,
                width: fullWidth / 2,
                height: fullHeight,
                backgroundColor: backgroundColor,
            };
            const chart = new google.visualization.ColumnChart(document.getElementById('habit-' + h.id + '-successPerWeekDay'));
            chart.draw(data, options);
        });

        const options = {
            title: "Avg per weekday",
            colors: colors,
            width: fullWidth / 2,
            height: fullHeight,
            backgroundColor: backgroundColor,
        };
        const data = new google.visualization.DataTable();
        data.addColumn('string', 'Wochentag');
        data.addColumn('number', 'Avg. Success');
        data.addRows(allDows.map((d, i) => [daynames[i], (d.length ? (d.reduce(add, 0) / d.length) : 0)]));
        var chart = new google.visualization.ColumnChart(document.getElementById('allhabits-successPerWeekDay'));
        chart.draw(data, options);
    }

    function drawStreakInTimeCharts() {
        _drawStreakInTimeCharts('strong');
        _drawStreakInTimeCharts('weak');
    }
    function _drawStreakInTimeCharts(idFrag) {
        habits.forEach(h => { 
            const hStreaks = h.streaks[idFrag];
            if (!hStreaks.length) {
                $('#habit-' + h.id + '-streaks-in-time-' + idFrag).text('NO STREAKS');
                return;
            }

            const data = new google.visualization.DataTable();
            data.addColumn('date', 'Date');
            data.addColumn('number', 'Length');
            data.addColumn({ 'role': 'annotation' });
            data.addRows(
                hStreaks.map(s => [ new Date(s.startDate), s.days, ''+s.days+' days' ]));
            const options = {
                title: 'Streaks in time (' + idFrag + ')',
                colors: colors,
                width: fullWidth / 2,
                height: fullHeight,
                backgroundColor: backgroundColor,
            };
            var chart = new google.visualization.ColumnChart(document.getElementById('habit-' + h.id + '-streaks-in-time-' + idFrag));
            chart.draw(data, options);
        });
    }

    function drawStreakCharts() {
        _drawStreakCharts('strong');
        _drawStreakCharts('weak');
    }
    function _drawStreakCharts(idFrag) {
        const colors = [ '#0bf', '#0ae', '#09d', '#08c', '#07b', '#06a', '#059', '#048', '#037', '#026', '#015', '#004' ];
        const allStreaks = {};
        habits.forEach(h => { 
            const hStreaks = h.streaks[idFrag];
            if (!hStreaks.length) {
                $('#habit-' + h.id + '-streaks-' + idFrag).text('NO STREAKS');
                return;
            }

            const streaks = [];
            hStreaks.forEach(s => {
                streaks.push(s.days);
                if ( allStreaks[s.days] === undefined ) {
                    allStreaks[s.days] = [];
                }
                allStreaks[s.days].push(h.name);
            });

            const data = new google.visualization.DataTable();
            data.addColumn('string', 'Streak');
            data.addColumn('number', 'Length');
            data.addColumn({ 'role': 'style' });
            data.addColumn({ 'role': 'annotation' });
            // NOTE: I have no clue why this is already inversed after the sort?
            data.addRows(
                _.toPairs(_.groupBy(streaks))
                .map(l => [Number(l[0]), l[1]])
                .filter(l => l[0] > 1)
                .sort((l1, l2) => l2[0] - l1[0])
                .map(l => [ 
                    l[0] + ' days', 
                    l[0], 
                    'color: ' + colors[l[1].length] + ';', 
                    l[1].length + ' streaks' 
                ]));
            const options = {
                title: 'Streaks (' + idFrag + ')',
                colors: colors,
                width: fullWidth / 2,
                height: fullHeight,
                backgroundColor: backgroundColor,
            };
            var chart = new google.visualization.BarChart(document.getElementById('habit-' + h.id + '-streaks-' + idFrag));
            chart.draw(data, options);
        });
        const data = new google.visualization.DataTable();
        data.addColumn('string', 'Streak');
        data.addColumn('number', 'Length');
        data.addColumn({ 'role': 'style' });
        data.addColumn({ 'role': 'annotation' });
        // NOTE: I have no clue why this is already inversed after the sort?
        const allRows = _.toPairs(allStreaks)
            .map(l => [Number(l[0]), l[1]])
            .filter(l => l[0] > 1)
            .sort(l => l[0])
            .map(l => { 
                const names = _.keys(_.groupBy(l[1])); 
                return [
                    l[0] + ' days (' + l[1].length + ')', 
                    l[0], 
                    'color: ' + colors[names.length] + ';', 
                    l[0] + ' days (' + names.join(', ') + ')'
                ]; 
            });
        data.addRows(allRows);

        const options = {
            title: 'Streaks (' + idFrag + ')',
            colors: colors,
            width: fullWidth / 2,
            height: fullHeight,
            backgroundColor: backgroundColor,
        };
        var chart = new google.visualization.BarChart(document.getElementById('allhabits-streaks-' + idFrag));
        chart.draw(data, options);
    }

    function drawTendencyCharts() {
        habits.forEach(h => { 
            let data = new google.visualization.DataTable();
            data.addColumn('date', 'Date');
            data.addColumn('number', 'Tendency');

            let accu = 50;
            data.addRows(h.resolutionDates.map(rd => {
                accu += Number(rd.resolution.fulfilmentPercent) - 50;
                return [ new Date(rd.date),  accu ];
            }));

            var options = { 
                title: h.name + ": Daily Tendency", 
                colors: colors,
                curveType: 'function',
                width: fullWidth,
                height: fullHeight,
                backgroundColor: backgroundColor,
            };
            var chart = new google.visualization.LineChart(document.getElementById('habit-' + h.id + '-tendencyPerDay'));
            chart.draw(data, options);

        });

        let allRows = {};
        let hidmap = {};
        let hidmapcount = 1;
        habits.map(h => { hidmap[h.id] = hidmapcount++; });
        habits.forEach(h => { 
            let accu = 50;
            h.resolutionDates.forEach(rd => {
                if ( allRows[rd.date] === undefined ) {
                    allRows[rd.date] = _.fill(Array(hidmapcount), null);
                    allRows[rd.date][0] = new Date(rd.date);
                }
                accu += Number(rd.resolution.fulfilmentPercent) - 50;
                allRows[rd.date][hidmap[h.id]] = accu;
            });
        });

        var options = { 
            title: "Daily Tendencies", 
            colors: colors,
            curveType: 'function',
            width: fullWidth,
            height: fullHeight * 2,
            backgroundColor: backgroundColor,
        };
        let data = new google.visualization.DataTable();
        data.addColumn('date', 'Date');
        habits.map(h => {
            data.addColumn('number', h.name)
        });
        const allRowsForGoogle = _.values(allRows).sort((r1, r2) => r2[0] - r1[0]);
        for ( let rowIndex = 1; rowIndex < allRowsForGoogle.length; ++rowIndex ) {
            for ( let colIndex = 1; colIndex < hidmapcount; ++colIndex ) {
                if ( allRowsForGoogle[rowIndex][colIndex] === null ) {
                    allRowsForGoogle[rowIndex][colIndex] = allRowsForGoogle[rowIndex - 1][colIndex];
                }
            }
        }
        data.addRows(allRowsForGoogle);
        var chart = new google.visualization.LineChart(document.getElementById('allhabits-tendencyPerDay'));
        chart.draw(data, options);
    }

    function drawComparisonChart() {
        let allRows = {};
        let data = new google.visualization.DataTable();
        data.addColumn('string', 'Name');
        data.addColumn('number', 'Avg. Success');
        data.addRows(habits.map(h => [h.name, average(h.resolutionDates.map(rd => Number(rd.resolution.fulfilmentPercent)))]).sort((l1, l2) => Number(l2[1])-Number(l1[1])));
        var options = { 
            title: "Comparison", 
            colors: colors,
            width: fullWidth,
            height: fullHeight * 2,
            backgroundColor: backgroundColor,
        };
        var chart = new google.visualization.BarChart(document.getElementById('allhabits-comparison'));
        chart.draw(data, options);
    }

    function formatDate(date) {
        return date.getFullYear() + '-' + (''+(date.getMonth() + 1)).padStart(2, '0') + '-' + (''+date.getDate()).padStart(2, '0');
    }
});


function getWeek(date) {
    //find the year of the current date  
    var oneJan =  new Date(date.getFullYear() + '-01-01');   
    // calculating number of days in given year before a given date   
    var numberOfDays =  Math.floor((date - oneJan) / (24 * 60 * 60 * 1000));   
    // adding 1 since to current date and returns value starting from 0   
    return Math.ceil(( (date.getDay() + 1) % 7 + numberOfDays) / 7);     
}
