const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const regex = /var htmlItems = items\.map\(function\(item\) \{[\s\S]*?return '<div class="ticker-item">' \+[\s\S]*?\}\)\.join\(''\);/;

const newMap = `var htmlItems = items.map(function(item, index) {
                        var dt = new Date(item.date);
                        dt.setHours(0, 0, 0, 0);
                        var extraStyle = (index === items.length - 1) ? 'margin-right: 50vw;' : '';
                        return '<div class="ticker-item" style="' + extraStyle + '">' +
                            '<span class="country">' + item.flag + ' ' + item.label + '</span> ' +
                            '<span class="days" data-target="' + dt.getTime() + '"></span> ' +
                            '<span class="desc">' + item.desc + '</span>' +
                            '</div>';
                    }).join('');`;

html = html.replace(regex, newMap);
html = html.replace('for (var i = 0; i < 4; i++) {', 'for (var i = 0; i < 2; i++) {');
fs.writeFileSync('index.html', html, 'utf8');
console.log('Fixed');
