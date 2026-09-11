// Live Countdown Timer - Updates Every Second
(function buildTicker() {
    var deadlines = [
        { flag: '🇵🇹', label: 'PORTUGAL',      date: new Date(2026,  8, 20), desc: 'Local File deadline' },
        { flag: '🇦🇪', label: 'UAE',            date: new Date(2026, 11,  6), desc: 'CT/income tax return (incl. TP)' },
        { flag: '🇭🇰', label: 'HONG KONG SAR', date: new Date(2026, 11,  6), desc: 'Local File deadline' },
        { flag: '🇵🇱', label: 'POLAND',         date: new Date(2027,  0,  6), desc: 'Local File deadline' },
        { flag: '🇮🇳', label: 'INDIA',          date: new Date(2027,  0,  6), desc: 'TP disclosure / annual filing' },
        { flag: '🇵🇱', label: 'POLAND',         date: new Date(2027,  2,  6), desc: 'TP disclosure / annual filing' },
        { flag: '🇧🇪', label: 'BELGIUM',        date: new Date(2027,  3,  7), desc: 'TP disclosure / annual filing' },
        { flag: '🇵🇱', label: 'POLAND',         date: new Date(2027,  3,  7), desc: 'Master File deadline' },
        { flag: '🇭🇺', label: 'HUNGARY',        date: new Date(2027,  3,  7), desc: 'Master File deadline' },
        { flag: '🇰🇷', label: 'SOUTH KOREA',    date: new Date(2027,  3,  7), desc: 'Local File deadline' }
    ];

    function calculateCountdown() {
        var now = new Date();
        
        var items = deadlines
            .map(function(d) {
                var timeDiff = d.date - now;
                var days = Math.floor(timeDiff / (1000 * 60 * 60 * 24));
                var hours = Math.floor((timeDiff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                var minutes = Math.floor((timeDiff % (1000 * 60 * 60)) / (1000 * 60));
                var seconds = Math.floor((timeDiff % (1000 * 60)) / 1000);
                
                return { 
                    flag: d.flag, 
                    label: d.label, 
                    days: days,
                    hours: hours,
                    minutes: minutes,
                    seconds: seconds,
                    totalMs: timeDiff,
                    desc: d.desc 
                };
            })
            .filter(function(d) { return d.totalMs > 0; })
            .sort(function(a, b) { return a.totalMs - b.totalMs; });

        if (items.length === 0) {
            items = [{ 
                flag: '📋', 
                label: 'HEXATP', 
                days: 0,
                hours: 0,
                minutes: 0,
                seconds: 0,
                desc: 'No upcoming deadlines' 
            }];
        }

        return items;
    }

    function buildHTML(list) {
        return list.map(function(item) {
            var timeStr = item.days + 'd ' + 
                         String(item.hours).padStart(2, '0') + 'h ' + 
                         String(item.minutes).padStart(2, '0') + 'm ' + 
                         String(item.seconds).padStart(2, '0') + 's';
            
            return '<div class="ticker-item">' +
                '<span class="country">' + item.flag + ' ' + item.label + '</span> ' +
                '<span class="days">' + timeStr + '</span> ' +
                '<span class="desc">' + item.desc + '</span>' +
                '</div>';
        }).join('');
    }

    function updateTicker() {
        var items = calculateCountdown();
        var html = buildHTML(items);
        var ticker1 = document.getElementById('tickerContent1');
        var ticker2 = document.getElementById('tickerContent2');
        if (ticker1) ticker1.innerHTML = html;
        if (ticker2) ticker2.innerHTML = html;
    }

    // Initial update
    updateTicker();

    // Update every second
    setInterval(updateTicker, 1000);
})();
