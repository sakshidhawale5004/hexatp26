const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const newCSS = `        .ticker-track {
            display: inline-block;
            white-space: nowrap;
            animation: ticker-marquee 15s linear infinite;
            font-size: 0;
        }

        .ticker-content {
            display: inline-block;
            white-space: nowrap;
            font-size: 16px;
        }`;

html = html.replace(/\.ticker-track\s*\{[\s\S]*?min-width:\s*max-content;\s*\}/, newCSS);
fs.writeFileSync('index.html', html, 'utf8');
console.log('CSS Regex fixed');
