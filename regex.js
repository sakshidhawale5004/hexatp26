const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const newCode = `if (!tickerInitialized) {
                    var items = deadlines;
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

html = html.replace(/if \(!tickerInitialized\) \{[\s\S]*?tickerInitialized = true;\s*\}/, newCode);
fs.writeFileSync('index.html', html, 'utf8');
console.log('Regex replace successful');
