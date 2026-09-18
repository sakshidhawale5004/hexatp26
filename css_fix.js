const fs = require('fs');
let html = fs.readFileSync('index.html', 'utf8');

const oldCSS = `        .ticker-track {
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

const newCSS = `        .ticker-track {
            display: inline-block;
            white-space: nowrap;
            animation: ticker-marquee 15s linear infinite;
            font-size: 0; /* Remove inline-block gap */
        }

        .ticker-content {
            display: inline-block;
            white-space: nowrap;
            font-size: 16px; /* Restore font size, assuming it inherits from body */
        }`;

if (html.includes(oldCSS)) {
    html = html.replace(oldCSS, newCSS);
    fs.writeFileSync('index.html', html, 'utf8');
    console.log('CSS fixed');
} else {
    console.log('CSS not found');
}
