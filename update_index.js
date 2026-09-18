const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const oldCode = `                if (!tickerInitialized) {
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

                    // Duplicate the content a few times to ensure it covers the screen width, but do it within each tickerContent div
                    html = html + html + html;
                    document.getElementById('tickerContent1').innerHTML = html;
                    document.getElementById('tickerContent2').innerHTML = html;
                    tickerInitialized = true;
                }`;

const newCode = `                if (!tickerInitialized) {
                    var items = deadlines; // Render ALL countries!
                    
                    var htmlItems = items.map(function(item) {
                        var dt = new Date(item.date);
                        return '<div class="ticker-item">' +
                            '<span class="country">' + item.flag + ' ' + item.label + '</span> ' +
                            '<span class="days" data-target="' + dt.getTime() + '"></span> ' +
                            '<span class="desc">' + item.desc + '</span>' +
                            '</div>';
                    }).join('');

                    var repeatedHtml = '';
                    for (var i = 0; i < 20; i++) {
                        repeatedHtml += htmlItems;
                    }
                    
                    document.getElementById('tickerContent1').innerHTML = repeatedHtml;
                    document.getElementById('tickerContent2').innerHTML = repeatedHtml;
                    tickerInitialized = true;
                }`;

if (html.includes(oldCode)) {
    html = html.replace(oldCode, newCode);
    fs.writeFileSync('index.html', html, 'utf8');
    console.log('Successfully updated index.html');
} else {
    console.log('oldCode not found');
}
