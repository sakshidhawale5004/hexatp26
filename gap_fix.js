const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const oldMap = `                    var htmlItems = items.map(function(item) {
                        var dt = new Date(item.date);
                        return '<div class="ticker-item">' +
                            '<span class="country">' + item.flag + ' ' + item.label + '</span> ' +
                            '<span class="days" data-target="' + dt.getTime() + '"></span> ' +
                            '<span class="desc">' + item.desc + '</span>' +
                            '</div>';
                    }).join('');`;

const newMap = `                    var htmlItems = items.map(function(item, index) {
                        var dt = new Date(item.date);
                        var extraStyle = (index === items.length - 1) ? 'margin-right: 50vw;' : '';
                        return '<div class="ticker-item" style="' + extraStyle + '">' +
                            '<span class="country">' + item.flag + ' ' + item.label + '</span> ' +
                            '<span class="days" data-target="' + dt.getTime() + '"></span> ' +
                            '<span class="desc">' + item.desc + '</span>' +
                            '</div>';
                    }).join('');`;

if (html.includes(oldMap)) {
    html = html.replace(oldMap, newMap);
    html = html.replace('for (var i = 0; i < 4; i++) {', 'for (var i = 0; i < 2; i++) {');
    fs.writeFileSync('index.html', html, 'utf8');
    console.log('Map updated with gap and 2 copies');
} else {
    console.log('oldMap not found');
}
