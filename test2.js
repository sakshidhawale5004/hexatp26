const { JSDOM } = require('jsdom');
const dom = `
<div class="compliance-ticker-wrap">
    <div id="tickerContent1"></div>
    <div id="tickerContent2"></div>
</div>`;

const window = new JSDOM(dom).window;
const document = window.document;

        (function buildTicker() {
            var deadlines = [
                { flag: '🇵🇹', label: 'PORTUGAL',      date: new Date(2026,  8, 19), desc: 'Local File deadline' }
            ];

            function pad2(num) {
                return num.toString().padStart(2, '0');
            }

            var tickerInitialized = false;

            function updateTicker() {
                var now = new Date();
                
                if (!tickerInitialized) {
                    var items = deadlines
                        .map(function(d) {
                            var deadlineDate = new Date(d.date);
                            deadlineDate.setHours(0, 0, 0, 0); // Reset time to start of day
                            var timeDiff = deadlineDate - now;
                            return { 
                                flag: d.flag, 
                                label: d.label, 
                                targetMs: deadlineDate.getTime(),
                                diffMs: timeDiff,
                                desc: d.desc 
                            };
                        })
                        .filter(function(d) { return d.diffMs >= 0; })
                        .sort(function(a, b) { return a.diffMs - b.diffMs; });

                    if (items.length === 0) {
                        items = [{ flag: '📋', label: 'HEXATP', targetMs: -1, diffMs: -1, desc: 'No upcoming deadlines' }];
                    }

                    var html = items.map(function(item) {
                        return '<div class="ticker-item">' +
                            '<span class="country">' + item.flag + ' ' + item.label + '</span> ' +
                            '<span class="days" data-target="' + item.targetMs + '"></span> ' +
                            '<span class="desc">' + item.desc + '</span>' +
                            '</div>';
                    }).join('');

                    document.getElementById('tickerContent1').innerHTML = html;
                    document.getElementById('tickerContent2').innerHTML = html;
                    tickerInitialized = true;
                }

                var nowMs = Date.now();
                var daysEls = document.querySelectorAll('.compliance-ticker-wrap .days[data-target]');
                for(var i=0; i<daysEls.length; i++) {
                    var el = daysEls[i];
                    var target = parseInt(el.getAttribute('data-target'), 10);
                    if (target === -1) {
                        el.textContent = '—';
                        continue;
                    }
                    var diff = target - nowMs;
                    if (diff <= 0) {
                        el.textContent = 'TODAY';
                    } else {
                        var d = Math.floor(diff / 86400000);
                        var h = pad2(Math.floor((diff / 3600000) % 24));
                        var m = pad2(Math.floor((diff / 60000) % 60));
                        var s = pad2(Math.floor((diff / 1000) % 60));
                        el.textContent = d + 'D ' + h + ':' + m + ':' + s;
                    }
                }
            }

            updateTicker();
        })();

console.log(document.body.innerHTML);
