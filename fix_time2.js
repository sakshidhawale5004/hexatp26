const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const regex = /var days = Math\.ceil\(diff \/ 86400000\);\s*el\.textContent = days \+ ' days';/;

const newLogic = `var d = Math.floor(diff / 86400000);
                        var h = pad2(Math.floor((diff / 3600000) % 24));
                        var m = pad2(Math.floor((diff / 60000) % 60));
                        var s = pad2(Math.floor((diff / 1000) % 60));
                        el.textContent = d + 'd ' + h + 'h ' + m + 'm ' + s + 's';`;

html = html.replace(regex, newLogic);
fs.writeFileSync('index.html', html, 'utf8');
console.log('Fixed time format with regex');
