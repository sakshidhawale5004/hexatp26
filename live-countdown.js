// Live Countdown Timer - Updates Daily
(function buildTicker() {
    var deadlines = [
        { flag: '🇵🇹', label: 'PORTUGAL',      date: new Date(2026,  8, 20), desc: 'Local File deadline' },
        { flag: '🇮🇳', label: 'INDIA',          date: new Date(2026,  9, 31), desc: 'TP disclosure / annual filing' },
        { flag: '🇦🇪', label: 'UAE',            date: new Date(2026, 11,  6), desc: 'TP disclosure / annual filing' },
        { flag: '🇭🇰', label: 'HONG KONG SAR', date: new Date(2026, 11,  6), desc: 'Local File deadline' },
        { flag: '🇵🇱', label: 'POLAND',         date: new Date(2027,  0,  6), desc: 'Local File deadline' },
        { flag: '🇧🇪', label: 'BELGIUM',        date: new Date(2027,  3,  7), desc: 'TP disclosure / annual filing' },
        { flag: '🇭🇺', label: 'HUNGARY',        date: new Date(2027,  3,  7), desc: 'Master File deadline' },
        { flag: '🇰🇷', label: 'SOUTH KOREA',    date: new Date(2027,  3,  7), desc: 'Local File deadline' }
    ];

    function calculateCountdown() {
        var now = new Date();
        now.setHours(0, 0, 0, 0); // Reset time to start of day for accurate day count
        
        var items = deadlines
            .map(function(d) {
                var deadlineDate = new Date(d.date);
                deadlineDate.setHours(0, 0, 0, 0); // Reset time to start of day
                var timeDiff = deadlineDate - now;
                var days = Math.ceil(timeDiff / (1000 * 60 * 60 * 24));
                
                return { 
                    flag: d.flag, 
                    label: d.label, 
                    days: days,
                    totalMs: timeDiff,
                    desc: d.desc 
                };
            })
            .filter(function(d) { return d.days > 0; })
            .sort(function(a, b) { return a.totalMs - b.totalMs; });

        if (items.length === 0) {
            items = [{ 
                flag: '📋', 
                label: 'HEXATP', 
                days: '—',
                desc: 'No upcoming deadlines' 
            }];
        }

        return items;
    }

    function buildHTML(list) {
        return list.map(function(item) {
            var timeStr = item.days + ' days';
            
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

    // Update once per day at midnight
    setInterval(updateTicker, 86400000); // 24 hours in milliseconds
    
    // Also update every minute to catch day changes
    setInterval(updateTicker, 60000); // 1 minute
})();
