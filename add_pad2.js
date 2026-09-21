const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const search = 'var tickerInitialized = false;';
const replace = `function pad2(num) {
                return num.toString().padStart(2, '0');
            }

            var tickerInitialized = false;`;

if (html.includes(search)) {
    html = html.replace(search, replace);
    fs.writeFileSync('index.html', html, 'utf8');
    console.log('Added pad2');
} else {
    console.log('Not found');
}
