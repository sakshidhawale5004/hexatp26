const fs = require('fs');
let current = fs.readFileSync('index.html', 'utf8');

const badCSS = `        .ticker-track {
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

const goodCSS = `        .ticker-track {
            display: flex;
            width: max-content;
            animation: ticker-marquee 15s linear infinite;
        }

        .ticker-content {
            display: flex;
            align-items: center;
            flex-shrink: 0;
            min-width: max-content;
        }`;

current = current.replace(badCSS, goodCSS);
fs.writeFileSync('index.html', current, 'utf8');
console.log('CSS Reverted');
