const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const regexMap = /var htmlItems = items\.map\(function\(item, index\) \{[\s\S]*?\}\)\.join\(''\);/;

const newMap = `var htmlItems = items.map(function(item) {
                        var dt = new Date(item.date);
                        dt.setHours(0, 0, 0, 0);
                        return '<div class="ticker-item">' +
                            '<span class="country">' + item.flag + ' ' + item.label + '</span> ' +
                            '<span class="days" data-target="' + dt.getTime() + '"></span> ' +
                            '<span class="desc">' + item.desc + '</span>' +
                            '</div>';
                    }).join('');`;

html = html.replace(regexMap, newMap);
html = html.replace('for (var i = 0; i < 2; i++) {', 'for (var i = 0; i < 4; i++) {');

fs.writeFileSync('index.html', html, 'utf8');
console.log('Removed gap and restored 4 copies');
